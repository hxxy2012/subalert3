<?php
namespace App\Controllers;

use App\Models\DB;

class PasswordController
{
    public function index(): void
    {
        $user = current_user();
        $pdo = DB::getConnection();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            if (strlen($new) < 8) {
                flash('error', '新密码长度至少8位');
                view('auth/change_password');
                return;
            }
            if ($new !== $confirm) {
                flash('error', '新密码两次输入不一致');
                view('auth/change_password');
                return;
            }
            // Fetch current hashed password
            $stmt = $pdo->prepare('SELECT password FROM users WHERE id=?');
            $stmt->execute([$user['id']]);
            $row = $stmt->fetch();
            if (!$row || !password_verify($current, $row['password'])) {
                flash('error', '当前密码错误');
                view('auth/change_password');
                return;
            }
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('UPDATE users SET password=?, updated_at=NOW() WHERE id=?');
            $stmt->execute([$hash, $user['id']]);
            flash('success', '密码修改成功，请重新登录');
            unset($_SESSION['user']);
            redirect('/?r=login');
        } else {
            view('auth/change_password');
        }
    }
}