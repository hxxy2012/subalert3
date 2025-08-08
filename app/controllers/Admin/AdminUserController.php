<?php
namespace App\Controllers\Admin;

use App\Models\DB;

class AdminUserController
{
    /**
     * Display list of users with pagination, search, and filtering.
     */
    public function index(): void
    {
        $pdo = DB::getConnection();
        
        // Get search and filter parameters
        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $perPage = (int)($_GET['per_page'] ?? 20);
        $page = max(1, (int)($_GET['page'] ?? 1));
        
        // Validate per_page
        if (!in_array($perPage, [10, 20, 50, 100])) {
            $perPage = 20;
        }
        
        // Build WHERE clause
        $where = [];
        $params = [];
        
        if ($search) {
            $where[] = "(email LIKE ? OR nickname LIKE ? OR id = ?)";
            $searchParam = '%' . $search . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = is_numeric($search) ? (int)$search : 0;
        }
        
        if ($status && in_array($status, ['normal', 'frozen', 'cancelled'])) {
            $where[] = "status = ?";
            $params[] = $status;
        }
        
        if ($dateFrom) {
            $where[] = "created_at >= ?";
            $params[] = $dateFrom . ' 00:00:00';
        }
        
        if ($dateTo) {
            $where[] = "created_at <= ?";
            $params[] = $dateTo . ' 23:59:59';
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM users $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalUsers = $countStmt->fetchColumn();
        
        // Calculate pagination
        $totalPages = ceil($totalUsers / $perPage);
        $offset = ($page - 1) * $perPage;
        
        // Get users with pagination
        $sql = "SELECT id, email, nickname, status, created_at, last_login_at 
                FROM users $whereClause 
                ORDER BY created_at DESC 
                LIMIT $perPage OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        // Get status statistics
        $statsSql = "SELECT status, COUNT(*) as count FROM users GROUP BY status";
        $statsStmt = $pdo->query($statsSql);
        $statusStats = [];
        while ($row = $statsStmt->fetch()) {
            $statusStats[$row['status']] = $row['count'];
        }
        
        // Calculate pagination info
        $startRecord = $totalUsers > 0 ? $offset + 1 : 0;
        $endRecord = min($offset + $perPage, $totalUsers);
        
        view('admin/users', [
            'users' => $users,
            'totalUsers' => $totalUsers,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'startRecord' => $startRecord,
            'endRecord' => $endRecord,
            'search' => $search,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'statusStats' => $statusStats
        ]);
    }

    /**
     * Edit a user.
     */
    public function edit(): void
    {
        $id = intval($_GET['id'] ?? 0);
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if (!$user) {
            flash('error', '用户不存在');
            redirect('/admin.php?r=users');
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nickname = trim($_POST['nickname'] ?? '');
            $status   = trim($_POST['status'] ?? 'normal');
            $pdo = DB::getConnection();
            $stmt = $pdo->prepare('UPDATE users SET nickname = ?, status = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$nickname, $status, $id]);
            log_admin_action('edit_user', '编辑用户 ID: ' . $id);
            flash('success', '用户更新成功');
            redirect('/admin.php?r=users');
        } else {
            view('admin/user_edit', ['user' => $user]);
        }
    }

    /**
     * Delete a user (soft delete -> set status cancelled).
     */
    public function delete(): void
    {
        $id = intval($_GET['id'] ?? 0);
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
        $stmt->execute(['cancelled', $id]);
        log_admin_action('delete_user', '注销用户 ID: ' . $id);
        flash('success', '用户已注销');
        redirect('/admin.php?r=users');
    }

    /**
     * Batch operations on users.
     */
    public function batchAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            flash('error', '无效请求');
            redirect('/admin.php?r=users');
            return;
        }
        
        $userIds = $_POST['user_ids'] ?? [];
        $action = $_POST['action'] ?? '';
        
        if (empty($userIds) || !is_array($userIds)) {
            flash('error', '请选择要操作的用户');
            redirect('/admin.php?r=users');
            return;
        }
        
        $userIds = array_map('intval', $userIds);
        $userIds = array_filter($userIds);
        
        if (empty($userIds)) {
            flash('error', '无效的用户ID');
            redirect('/admin.php?r=users');
            return;
        }
        
        $pdo = DB::getConnection();
        $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
        
        switch ($action) {
            case 'activate':
                $stmt = $pdo->prepare("UPDATE users SET status = 'normal', updated_at = NOW() WHERE id IN ($placeholders)");
                $stmt->execute($userIds);
                log_admin_action('batch_activate_users', '批量激活用户 IDs: ' . implode(',', $userIds));
                flash('success', '成功激活 ' . count($userIds) . ' 个用户');
                break;
                
            case 'freeze':
                $stmt = $pdo->prepare("UPDATE users SET status = 'frozen', updated_at = NOW() WHERE id IN ($placeholders)");
                $stmt->execute($userIds);
                log_admin_action('batch_freeze_users', '批量冻结用户 IDs: ' . implode(',', $userIds));
                flash('success', '成功冻结 ' . count($userIds) . ' 个用户');
                break;
                
            case 'cancel':
                $stmt = $pdo->prepare("UPDATE users SET status = 'cancelled', updated_at = NOW() WHERE id IN ($placeholders)");
                $stmt->execute($userIds);
                log_admin_action('batch_cancel_users', '批量注销用户 IDs: ' . implode(',', $userIds));
                flash('success', '成功注销 ' . count($userIds) . ' 个用户');
                break;
                
            default:
                flash('error', '无效的操作');
                break;
        }
        
        // Preserve search parameters
        $params = [];
        foreach (['search', 'status', 'date_from', 'date_to', 'per_page', 'page'] as $key) {
            if (!empty($_POST[$key])) {
                $params[$key] = $_POST[$key];
            }
        }
        
        $redirectUrl = '/admin.php?r=users';
        if ($params) {
            $redirectUrl .= '&' . http_build_query($params);
        }
        
        redirect($redirectUrl);
    }

    /**
     * Export user list as CSV with current filters.
     */
    public function export(): void
    {
        $pdo = DB::getConnection();
        
        // Get search and filter parameters
        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        
        // Build WHERE clause (same logic as index method)
        $where = [];
        $params = [];
        
        if ($search) {
            $where[] = "(email LIKE ? OR nickname LIKE ? OR id = ?)";
            $searchParam = '%' . $search . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = is_numeric($search) ? (int)$search : 0;
        }
        
        if ($status && in_array($status, ['normal', 'frozen', 'cancelled'])) {
            $where[] = "status = ?";
            $params[] = $status;
        }
        
        if ($dateFrom) {
            $where[] = "created_at >= ?";
            $params[] = $dateFrom . ' 00:00:00';
        }
        
        if ($dateTo) {
            $where[] = "created_at <= ?";
            $params[] = $dateTo . ' 23:59:59';
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="users_' . date('YmdHis') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel compatibility with UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, ['ID','邮箱','昵称','状态','注册时间','最后登录']);
        
        $sql = "SELECT id, email, nickname, status, created_at, last_login_at FROM users $whereClause ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        while ($row = $stmt->fetch()) {
            fputcsv($output, [$row['id'], $row['email'], $row['nickname'], $row['status'], $row['created_at'], $row['last_login_at']]);
        }
        
        fclose($output);
        log_admin_action('export_users', '导出用户数据（带筛选条件）');
        exit;
    }
}