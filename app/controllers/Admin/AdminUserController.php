<?php
namespace App\Controllers\Admin;

use App\Models\DB;

class AdminUserController
{
    /**
     * Display list of users with filtering support.
     */
    public function index(): void
    {
        $pdo = DB::getConnection();
        
        // Build query with filters
        $where = [];
        $params = [];
        
        // Search filter
        $search = trim($_GET['search'] ?? '');
        if (!empty($search)) {
            $where[] = '(email LIKE ? OR nickname LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        
        // Status filter
        $status = trim($_GET['status'] ?? '');
        if (!empty($status) && in_array($status, ['normal', 'frozen', 'cancelled'])) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        
        // Date range filters
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');
        if (!empty($dateFrom)) {
            $where[] = 'created_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }
        if (!empty($dateTo)) {
            $where[] = 'created_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }
        
        // Build final query
        $sql = 'SELECT id, email, nickname, status, created_at, last_login_at FROM users';
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        // Get user counts for status badges
        $statusCounts = $this->getUserStatusCounts($pdo);
        
        view('admin/users', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ],
            'statusCounts' => $statusCounts
        ]);
    }
    
    /**
     * Get user counts by status for filter badges.
     */
    private function getUserStatusCounts($pdo): array
    {
        $stmt = $pdo->query('SELECT status, COUNT(*) as count FROM users GROUP BY status');
        $counts = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        
        // Ensure all statuses are represented
        $defaultCounts = ['normal' => 0, 'frozen' => 0, 'cancelled' => 0];
        return array_merge($defaultCounts, $counts);
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
     * Export user list as CSV.
     */
    public function export(): void
    {
        $pdo = DB::getConnection();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="users_' . date('YmdHis') . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID','邮箱','昵称','状态','注册时间','最后登录']);
        $stmt = $pdo->query('SELECT id, email, nickname, status, created_at, last_login_at FROM users');
        while ($row = $stmt->fetch()) {
            fputcsv($output, [$row['id'], $row['email'], $row['nickname'], $row['status'], $row['created_at'], $row['last_login_at']]);
        }
        fclose($output);
        log_admin_action('export_users', '导出用户数据');
        exit;
    }
}