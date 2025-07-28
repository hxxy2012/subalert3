<?php
namespace App\Controllers;

use App\Models\DB;

class ProfileController
{
    /**
     * Show and update user profile.
     */
    public function index(): void
    {
        $user = current_user();
        $pdo = DB::getConnection();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nickname = trim($_POST['nickname'] ?? '');
            $phone    = trim($_POST['phone'] ?? '');
            // Basic validation
            if ($nickname === '') {
                flash('error', '昵称不能为空');
                view('profile/index', ['user' => $user]);
                return;
            }
            // Handle avatar upload
            $avatarPath = $user['avatar'] ?? null;
            if (!empty($_FILES['avatar']['name'])) {
                $file = $_FILES['avatar'];
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $allowed = ['jpg','jpeg','png','gif'];
                    if (!in_array(strtolower($ext), $allowed)) {
                        flash('error', '头像文件格式不支持');
                        view('profile/index', ['user' => $user]);
                        return;
                    }
                    $uploadDir = __DIR__ . '/../../public/uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $filename = uniqid('avatar_') . '.' . $ext;
                    $destination = $uploadDir . $filename;
                    move_uploaded_file($file['tmp_name'], $destination);
                    $avatarPath = '/uploads/' . $filename;
                }
            }
            // Update user table
            $stmt = $pdo->prepare('UPDATE users SET nickname=?, phone=?, avatar=?, updated_at=NOW() WHERE id=?');
            $stmt->execute([$nickname, $phone, $avatarPath, $user['id']]);
            // Update session
            $_SESSION['user']['nickname'] = $nickname;
            $_SESSION['user']['phone']    = $phone;
            $_SESSION['user']['avatar']   = $avatarPath;
            flash('success', '个人资料更新成功');
            redirect('/?r=profile');
        } else {
            view('profile/index', ['user' => $user]);
        }
    }

    /**
     * Delete user account (soft delete).
     */
    public function deleteAccount(): void
    {
        $user = current_user();
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('UPDATE users SET status=?, updated_at=NOW() WHERE id=?');
        $stmt->execute(['cancelled', $user['id']]);
        unset($_SESSION['user']);
        flash('success', '账户已注销');
        redirect('/?r=login');
    }
}