<?php
namespace App\Controllers;

use App\Models\DB;

class ForgotPasswordController
{
    /**
     * Handle forgot password form submission and token creation.
     */
    public function forgot(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', '邮箱格式不正确');
                view('auth/forgot_password');
                return;
            }
            $pdo = DB::getConnection();
            // Check if user exists
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if (!$stmt->fetch()) {
                flash('error', '该邮箱未注册');
                view('auth/forgot_password');
                return;
            }
            // Generate token
            $token = bin2hex(random_bytes(32));
            // Delete existing tokens for this email
            $pdo->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);
            // Insert new token
            $stmt = $pdo->prepare('INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, NOW())');
            $stmt->execute([$email, $token]);
            // For demonstration, we display the reset link instead of sending email
            $resetLink = '/index.php?r=reset-password&token=' . $token;
            flash('success', '重置链接已生成。请点击链接重设密码：<a href="' . $resetLink . '">' . $resetLink . '</a>');
            view('auth/forgot_password');
        } else {
            view('auth/forgot_password');
        }
    }

    /**
     * Handle password reset using token.
     */
    public function reset(): void
    {
        $token = $_GET['token'] ?? '';
        $pdo = DB::getConnection();
        // Validate token
        $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = ?');
        $stmt->execute([$token]);
        $record = $stmt->fetch();
        if (!$record) {
            flash('error', '无效或已使用的重置令牌');
            view('auth/reset_password');
            return;
        }
        // Check expiry (1 hour)
        $createdAt = strtotime($record['created_at']);
        if (time() - $createdAt > 3600) {
            // Remove expired token
            $pdo->prepare('DELETE FROM password_resets WHERE token = ?')->execute([$token]);
            flash('error', '重置令牌已过期');
            view('auth/reset_password');
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            if (strlen($newPassword) < 8) {
                flash('error', '新密码长度至少8位');
                view('auth/reset_password', ['token' => $token]);
                return;
            }
            if ($newPassword !== $confirm) {
                flash('error', '两次密码不一致');
                view('auth/reset_password', ['token' => $token]);
                return;
            }
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            // Update user password by email
            $stmt = $pdo->prepare('UPDATE users SET password=?, updated_at=NOW() WHERE email=?');
            $stmt->execute([$hash, $record['email']]);
            // Remove token
            $pdo->prepare('DELETE FROM password_resets WHERE token=?')->execute([$token]);
            flash('success', '密码重置成功，请登录');
            redirect('/?r=login');
            return;
        } else {
            view('auth/reset_password', ['token' => $token]);
        }
    }
}