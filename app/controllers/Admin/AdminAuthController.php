<?php
namespace App\Controllers\Admin;

use App\Models\DB;

class AdminAuthController
{
    /**
     * Admin login page or handle login.
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $pdo = DB::getConnection();
            $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ?');
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            if (!$admin || !password_verify($password, $admin['password'])) {
                flash('error', '用户名或密码错误');
                view('admin/login');
                return;
            }
            unset($admin['password']);
            $_SESSION['admin'] = $admin;
            flash('success', '后台登录成功');
            redirect('/admin.php?r=dashboard');
        } else {
            view('admin/login');
        }
    }

    /**
     * Logout admin.
     */
    public function logout(): void
    {
        unset($_SESSION['admin']);
        flash('success', '已退出后台登录');
        redirect('/admin.php?r=login');
    }
}