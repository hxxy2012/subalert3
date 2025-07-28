<?php
namespace App\Controllers\Admin;

use App\Models\DB;

class AdminUserController
{
    /**
     * Display list of users.
     */
    public function index(): void
    {
        $pdo = DB::getConnection();
        $stmt = $pdo->query('SELECT id, email, nickname, status, created_at, last_login_at FROM users');
        $users = $stmt->fetchAll();
        view('admin/users', ['users' => $users]);
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