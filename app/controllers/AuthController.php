<?php
namespace App\Controllers;

use App\Models\DB;
use App\Models\User;

/**
 * Handles user registration, login and logout.
 */
class AuthController
{
    /**
     * Display registration form or handle registration submission.
     */
    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm'] ?? '';
            $nickname = trim($_POST['nickname'] ?? '');
            // Basic validation
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', '邮箱格式不正确');
                view('auth/register');
                return;
            }
            if ($password !== $confirm) {
                flash('error', '两次密码输入不一致');
                view('auth/register');
                return;
            }
            if (strlen($password) < 8) {
                flash('error', '密码长度至少8位');
                view('auth/register');
                return;
            }
            // Check email uniqueness
            $pdo = DB::getConnection();
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                flash('error', '邮箱已被注册');
                view('auth/register');
                return;
            }
            // Create user
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO users (email, password, nickname, status, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())');
            $status = 'normal';
            $stmt->execute([$email, $hash, $nickname, $status]);
            flash('success', '注册成功，请登录');
            redirect('/?r=login');
        } else {
            view('auth/register');
        }
    }

    /**
     * Display login form or handle login.
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', '邮箱格式不正确');
                view('auth/login');
                return;
            }
            $pdo = DB::getConnection();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND status = ?');
            $stmt->execute([$email, 'normal']);
            $user = $stmt->fetch();
            if (!$user || !password_verify($password, $user['password'])) {
                flash('error', '邮箱或密码错误');
                view('auth/login');
                return;
            }
            // Save user to session (exclude password)
            unset($user['password']);
            $_SESSION['user'] = $user;
            // Update last_login_at
            $stmt = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
            $stmt->execute([$user['id']]);
            flash('success', '登录成功');
            redirect('/?r=dashboard');
        } else {
            view('auth/login');
        }
    }

    /**
     * Logout user and redirect to login page.
     */
    public function logout(): void
    {
        unset($_SESSION['user']);
        flash('success', '已退出登录');
        redirect('/?r=login');
    }
}