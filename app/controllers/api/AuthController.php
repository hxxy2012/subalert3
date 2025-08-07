<?php
namespace App\Controllers\Api;

use App\Models\DB;

/**
 * 用户认证API控制器
 * 处理用户注册、登录、登出、密码重置等功能
 */
class AuthController extends BaseApiController
{
    /**
     * 用户注册
     * POST /api/auth/register
     * 
     * @param string email 邮箱地址
     * @param string password 密码
     * @param string confirm_password 确认密码
     * @param string nickname 昵称（可选）
     */
    public function register()
    {
        try {
            // 获取请求数据
            $data = $this->getJsonInput();
            
            // 验证必填字段
            $this->validateRequired($data, ['email', 'password', 'confirm_password']);
            
            $email = trim($data['email']);
            $password = $data['password'];
            $confirmPassword = $data['confirm_password'];
            $nickname = trim($data['nickname'] ?? '');
            
            // 如果昵称为空，使用邮箱前缀作为昵称
            if (empty($nickname)) {
                $nickname = explode('@', $email)[0];
            }
            
            // 邮箱格式验证
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->error('邮箱格式不正确', 400);
            }
            
            // 密码确认验证
            if ($password !== $confirmPassword) {
                return $this->error('两次密码输入不一致', 400);
            }
            
            // 密码强度验证
            if (strlen($password) < 8) {
                return $this->error('密码长度至少8位', 400);
            }
            
            // 密码复杂度验证（可选）
            if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)/', $password)) {
                return $this->error('密码必须包含字母和数字', 400);
            }
            
            // 检查邮箱唯一性
            $pdo = DB::getConnection();
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return $this->error('邮箱已被注册', 409);
            }
            
            // 创建用户
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('
                INSERT INTO users (email, password, nickname, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([$email, $hashedPassword, $nickname, 'normal']);
            
            $userId = $pdo->lastInsertId();
            
            // 记录注册日志
            error_log("用户注册成功: ID=$userId, Email=$email");
            
            return $this->success([
                'user_id' => (int)$userId,
                'email' => $email,
                'nickname' => $nickname,
                'status' => 'normal',
                'created_at' => date('Y-m-d H:i:s')
            ], '注册成功', 201);
            
        } catch (\Exception $e) {
            error_log("用户注册失败: " . $e->getMessage());
            return $this->error('注册失败：' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 用户登录
     * POST /api/auth/login
     * 
     * @param string email 邮箱地址
     * @param string password 密码
     * @param bool remember_me 记住我（可选）
     */
    public function login()
    {
        try {
            $data = $this->getJsonInput();
            
            // 验证必填字段
            $this->validateRequired($data, ['email', 'password']);
            
            $email = trim($data['email']);
            $password = $data['password'];
            $rememberMe = isset($data['remember_me']) ? (bool)$data['remember_me'] : false;
            
            // 邮箱格式验证
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->error('邮箱格式不正确', 400);
            }
            
            // 查询用户
            $pdo = DB::getConnection();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            // 验证用户存在性
            if (!$user) {
                return $this->error('用户不存在', 404);
            }
            
            // 验证用户状态
            if ($user['status'] !== 'normal') {
                $statusMap = [
                    'frozen' => '账户已被冻结',
                    'cancelled' => '账户已被注销'
                ];
                $message = $statusMap[$user['status']] ?? '账户状态异常';
                return $this->error($message, 403);
            }
            
            // 验证密码
            if (!password_verify($password, $user['password'])) {
                return $this->error('密码错误', 401);
            }
            
            // 更新最后登录时间
            $stmt = $pdo->prepare('UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = ?');
            $stmt->execute([$user['id']]);
            
            // 生成JWT Token
            $expiresIn = $rememberMe ? 30 * 24 * 3600 : 24 * 3600; // 记住我30天，否则1天
            $tokenPayload = [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'exp' => time() + $expiresIn
            ];
            
            $token = $this->generateJWT($tokenPayload);
            
            // 返回用户信息（不包含密码）
            unset($user['password']);
            $user['last_login_at'] = date('Y-m-d H:i:s');
            
            // 记录登录日志
            error_log("用户登录成功: ID={$user['id']}, Email=$email");
            
            return $this->success([
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $expiresIn,
                'expires_at' => date('Y-m-d H:i:s', time() + $expiresIn)
            ], '登录成功');
            
        } catch (\Exception $e) {
            error_log("用户登录失败: " . $e->getMessage());
            return $this->error('登录失败：' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 忘记密码 - 发送重置邮件
     * POST /api/auth/forgot-password
     * 
     * @param string email 邮箱地址
     */
    public function forgotPassword()
    {
        try {
            $data = $this->getJsonInput();
            
            $this->validateRequired($data, ['email']);
            $email = trim($data['email']);
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->error('邮箱格式不正确', 400);
            }
            
            // 检查用户是否存在
            $pdo = DB::getConnection();
            $stmt = $pdo->prepare('SELECT id, nickname FROM users WHERE email = ? AND status = ?');
            $stmt->execute([$email, 'normal']);
            $user = $stmt->fetch();
            
            if (!$user) {
                return $this->error('该邮箱未注册', 404);
            }
            
            // 生成重置token
            $resetToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1小时后过期
            
            // 创建密码重置表（如果不存在）
            $pdo->exec('
                CREATE TABLE IF NOT EXISTS `password_resets` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `email` VARCHAR(255) NOT NULL,
                    `token` VARCHAR(64) NOT NULL,
                    `expires_at` DATETIME NOT NULL,
                    `created_at` DATETIME NOT NULL,
                    UNIQUE KEY `email` (`email`),
                    KEY `token` (`token`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ');
            
            // 保存重置token到数据库
            $stmt = $pdo->prepare('
                INSERT INTO password_resets (email, token, expires_at, created_at) 
                VALUES (?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                token = VALUES(token), expires_at = VALUES(expires_at), created_at = NOW()
            ');
            $stmt->execute([$email, $resetToken, $expiresAt]);
            
            // 发送重置邮件
            $resetLink = "https://yourdomain.com/reset-password?token=" . $resetToken;
            $subject = 'SubAlert - 密码重置';
            $body = $this->getPasswordResetEmailTemplate($user['nickname'], $resetLink);
            
            $emailSent = $this->sendEmail($email, $subject, $body);
            
            if (!$emailSent) {
                return $this->error('邮件发送失败，请稍后重试', 500);
            }
            
            return $this->success([
                'email' => $email,
                'expires_in' => 3600,
                'expires_at' => $expiresAt
            ], '密码重置邮件已发送，请查收邮箱');
            
        } catch (\Exception $e) {
            error_log("发送密码重置邮件失败: " . $e->getMessage());
            return $this->error('发送失败：' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 重置密码
     * POST /api/auth/reset-password
     * 
     * @param string token 重置令牌
     * @param string password 新密码
     * @param string confirm_password 确认新密码
     */
    public function resetPassword()
    {
        try {
            $data = $this->getJsonInput();
            
            $this->validateRequired($data, ['token', 'password', 'confirm_password']);
            
            $token = $data['token'];
            $password = $data['password'];
            $confirmPassword = $data['confirm_password'];
            
            // 密码确认验证
            if ($password !== $confirmPassword) {
                return $this->error('两次密码输入不一致', 400);
            }
            
            // 密码强度验证
            if (strlen($password) < 8) {
                return $this->error('密码长度至少8位', 400);
            }
            
            if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)/', $password)) {
                return $this->error('密码必须包含字母和数字', 400);
            }
            
            // 验证重置token
            $pdo = DB::getConnection();
            $stmt = $pdo->prepare('
                SELECT email FROM password_resets 
                WHERE token = ? AND expires_at > NOW()
            ');
            $stmt->execute([$token]);
            $reset = $stmt->fetch();
            
            if (!$reset) {
                return $this->error('重置链接无效或已过期', 400);
            }
            
            // 更新用户密码
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?');
            $stmt->execute([$hashedPassword, $reset['email']]);
            
            // 删除已使用的重置token
            $stmt = $pdo->prepare('DELETE FROM password_resets WHERE token = ?');
            $stmt->execute([$token]);
            
            // 记录密码重置日志
            error_log("用户密码重置成功: Email={$reset['email']}");
            
            return $this->success(null, '密码重置成功，请使用新密码登录');
            
        } catch (\Exception $e) {
            error_log("密码重置失败: " . $e->getMessage());
            return $this->error('重置失败：' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 用户登出
     * POST /api/auth/logout
     */
    public function logout()
    {
        try {
            $user = $this->getCurrentUser();
            
            if ($user) {
                // 记录登出日志
                error_log("用户登出: ID={$user['id']}, Email={$user['email']}");
            }
            
            // TODO: 可以实现token黑名单机制
            // $token = $this->getBearerToken();
            // if ($token) {
            //     $this->blacklistToken($token);
            // }
            
            return $this->success(null, '登出成功');
            
        } catch (\Exception $e) {
            return $this->error('登出失败：' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 验证Token有效性
     * GET /api/auth/verify
     */
    public function verify()
    {
        try {
            $user = $this->getCurrentUser();
            
            if (!$user) {
                return $this->error('Token无效或已过期', 401);
            }
            
            return $this->success([
                'user' => $user,
                'valid' => true
            ], 'Token有效');
            
        } catch (\Exception $e) {
            return $this->error('验证失败', 401);
        }
    }
    
    /**
     * 刷新Token
     * POST /api/auth/refresh
     */
    public function refresh()
    {
        try {
            $currentUser = $this->getCurrentUser();
            
            if (!$currentUser) {
                return $this->error('Token无效或已过期', 401);
            }
            
            // 生成新的token
            $expiresIn = 24 * 3600; // 1天
            $tokenPayload = [
                'user_id' => $currentUser['id'],
                'email' => $currentUser['email'],
                'exp' => time() + $expiresIn
            ];
            
            $newToken = $this->generateJWT($tokenPayload);
            
            return $this->success([
                'token' => $newToken,
                'token_type' => 'Bearer',
                'expires_in' => $expiresIn,
                'expires_at' => date('Y-m-d H:i:s', time() + $expiresIn)
            ], 'Token刷新成功');
            
        } catch (\Exception $e) {
            return $this->error('刷新失败：' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 获取当前用户信息
     * GET /api/auth/user
     */
    public function user()
    {
        try {
            $user = $this->requireAuth();
            
            // 获取用户统计信息
            $pdo = DB::getConnection();
            
            // 订阅总数
            $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM subscriptions WHERE user_id = ? AND status != ?');
            $stmt->execute([$user['id'], 'deleted']);
            $subscriptionCount = $stmt->fetchColumn();
            
            // 活跃提醒数
            $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM reminders WHERE user_id = ? AND status = ?');
            $stmt->execute([$user['id'], 'pending']);
            $reminderCount = $stmt->fetchColumn();
            
            $user['stats'] = [
                'subscription_count' => (int)$subscriptionCount,
                'reminder_count' => (int)$reminderCount
            ];
            
            return $this->success($user, '获取用户信息成功');
            
        } catch (\Exception $e) {
            return $this->error('获取用户信息失败：' . $e->getMessage(), 500);
        }
    }
    
    /**
     * 获取密码重置邮件模板
     */
    private function getPasswordResetEmailTemplate($nickname, $resetLink)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>SubAlert - 密码重置</title>
        </head>
        <body style="margin: 0; padding: 20px; font-family: Arial, sans-serif; background-color: #f5f5f5;">
            <div style="max-width: 600px; margin: 0 auto; background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <div style="background: linear-gradient(135deg, #3b82f6, #2563eb); padding: 30px; text-align: center;">
                    <h1 style="color: white; margin: 0; font-size: 24px;">
                        <i style="margin-right: 10px;">🔔</i>
                        SubAlert
                    </h1>
                    <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0 0;">智能订阅管理系统</p>
                </div>
                
                <div style="padding: 40px 30px;">
                    <h2 style="color: #333; margin-bottom: 20px;">密码重置请求</h2>
                    
                    <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">
                        尊敬的 <strong>' . htmlspecialchars($nickname) . '</strong>，您好！
                    </p>
                    
                    <p style="color: #666; line-height: 1.6; margin-bottom: 30px;">
                        您请求重置SubAlert账户密码。请点击下面的按钮来设置新密码：
                    </p>
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="' . $resetLink . '" 
                           style="display: inline-block; background: #3b82f6; color: white; text-decoration: none; 
                                  padding: 12px 30px; border-radius: 6px; font-weight: bold;">
                            重置密码
                        </a>
                    </div>
                    
                    <p style="color: #999; font-size: 14px; line-height: 1.6; margin-top: 30px;">
                        <strong>安全提示：</strong><br>
                        • 此链接将在1小时后失效<br>
                        • 如果您没有请求重置密码，请忽略此邮件<br>
                        • 请不要将此链接分享给他人
                    </p>
                    
                    <div style="border-top: 1px solid #eee; margin-top: 30px; padding-top: 20px; text-align: center;">
                        <p style="color: #999; font-size: 12px; margin: 0;">
                            此邮件由SubAlert系统自动发送，请勿直接回复<br>
                            © 2025 SubAlert. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </body>
        </html>';
    }
}