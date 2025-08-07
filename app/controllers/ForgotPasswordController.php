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
            $stmt = $pdo->prepare('SELECT id, nickname FROM users WHERE email = ? AND status = "normal"');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                // 为了安全考虑，即使邮箱不存在也显示成功消息，防止邮箱枚举攻击
                flash('success', '如果该邮箱已注册，您将收到密码重置邮件。请检查您的邮箱（包括垃圾邮件文件夹）。');
                view('auth/forgot_password');
                return;
            }
            
            // 检查是否在30秒内已经申请过
            $stmt = $pdo->prepare('SELECT created_at FROM password_resets WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 30 SECOND)');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                flash('warning', '请求过于频繁，请等待30秒后再试');
                view('auth/forgot_password');
                return;
            }
            
            // Load SMTP configuration
            $stmt = $pdo->query('SELECT `key`, `value` FROM settings WHERE `key` IN ("smtp_host", "smtp_port", "smtp_user", "smtp_pass", "site_name", "default_from_email", "ses_from_email")');
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['key']] = $row['value'];
            }
            
            // Check if SMTP is configured
            $smtpConfigured = !empty($settings['smtp_host']) && 
                             !empty($settings['smtp_user']) && 
                             !empty($settings['smtp_pass']);
            
            if (!$smtpConfigured) {
                flash('error', '系统邮件服务未配置，无法发送重置邮件。请联系管理员。');
                view('auth/forgot_password');
                return;
            }
            
            $smtpConfig = [
                'host' => $settings['smtp_host'],
                'port' => intval($settings['smtp_port'] ?? '465'),
                'user' => $settings['smtp_user'],
                'pass' => $settings['smtp_pass'],
            ];
            
            $siteName = $settings['site_name'] ?? 'SubAlert';
            
            // 获取发件人邮箱
            $fromEmail = $this->getFromEmail($smtpConfig, $settings);
            
            // Generate token
            $token = bin2hex(random_bytes(32));
            
            // Delete existing tokens for this email
            $pdo->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);
            
            // Insert new token
            $stmt = $pdo->prepare('INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, NOW())');
            $stmt->execute([$email, $token]);
            
            // 构建重置链接
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $resetLink = $protocol . '://' . $host . '/index.php?r=reset-password&token=' . $token;
            
            // Send email
            $subject = "[{$siteName}] 密码重置验证";
            $body = $this->createPasswordResetEmailTemplate($resetLink, $siteName);
            
            $emailSent = $this->sendPasswordResetEmail($email, $subject, $body, $smtpConfig, $fromEmail, $siteName);
            
            if ($emailSent) {
                // 成功发送邮件后的消息 - 不包含任何敏感信息
                $message = '密码重置邮件已发送！<br><br>';
                $message .= '<div style="background:#f0f9ff;border:1px solid #0ea5e9;padding:15px;margin:15px 0;border-radius:6px;">';
                $message .= '<h4 style="margin:0 0 10px 0;color:#0369a1;"><i class="fas fa-envelope"></i> 邮件已发送</h4>';
                $message .= '<p style="margin:0;color:#0c4a6e;">重置链接已发送到您的邮箱：<strong>' . htmlspecialchars($email) . '</strong></p>';
                $message .= '</div>';
                $message .= '<div style="background:#fef3c7;border:1px solid #f59e0b;padding:15px;margin:15px 0;border-radius:6px;">';
                $message .= '<h4 style="margin:0 0 10px 0;color:#92400e;"><i class="fas fa-info-circle"></i> 重要提示</h4>';
                $message .= '<ul style="margin:5px 0;padding-left:20px;color:#78350f;">';
                $message .= '<li>请在1小时内使用重置链接</li>';
                $message .= '<li>如未收到邮件，请检查垃圾邮件文件夹</li>';
                $message .= '<li>链接只能使用一次</li>';
                $message .= '<li>如果仍未收到，请重新申请</li>';
                $message .= '</ul>';
                $message .= '</div>';
                
                flash('success', $message, true);
            } else {
                flash('error', '邮件发送失败，请稍后重试或联系管理员。');
            }
            
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
            flash('error', '重置令牌已过期，请重新申请');
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
            
            // Remove token after successful reset
            $pdo->prepare('DELETE FROM password_resets WHERE token=?')->execute([$token]);
            
            // 成功消息
            $successMessage = '密码重置成功！<br><br>';
            $successMessage .= '<div style="background:#f0fdf4;border:1px solid #22c55e;padding:15px;margin:15px 0;border-radius:6px;">';
            $successMessage .= '<h4 style="margin:0 0 10px 0;color:#15803d;"><i class="fas fa-check-circle"></i> 重置完成</h4>';
            $successMessage .= '<p style="margin:0 0 15px 0;color:#166534;">您的密码已成功重置，现在可以使用新密码登录了。</p>';
            $successMessage .= '<a href="/?r=login" class="login-link" style="display:inline-block;padding:10px 20px;background:#22c55e;color:white;text-decoration:none;border-radius:4px;font-weight:600;">';
            $successMessage .= '<i class="fas fa-sign-in-alt"></i> 立即登录';
            $successMessage .= '</a>';
            $successMessage .= '</div>';
            
            flash('success', $successMessage, true);
            redirect('/?r=login');
            return;
        } else {
            view('auth/reset_password', ['token' => $token]);
        }
    }
    
    /**
     * 获取合适的发件人邮箱地址
     */
    private function getFromEmail($smtpConfig, $settings) {
        $isAmazonSES = strpos($smtpConfig['host'], 'amazonaws.com') !== false;
        
        if ($isAmazonSES) {
            // Amazon SES：优先使用专用发件人邮箱
            if (!empty($settings['ses_from_email'])) {
                return $settings['ses_from_email'];
            }
        }
        
        // 使用默认发件人邮箱
        if (!empty($settings['default_from_email'])) {
            return $settings['default_from_email'];
        }
        
        // 最后回退：根据服务类型决定
        if ($isAmazonSES) {
            // Amazon SES需要真实邮箱，构造一个基于域名的地址
            $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
            return "noreply@$domain";
        } else {
            // 传统SMTP可以使用用户名
            return $smtpConfig['user'];
        }
    }

    /**
     * Send password reset email via enhanced SMTP
     */
    private function sendPasswordResetEmail($email, $subject, $body, $smtpConfig, $fromEmail, $siteName) {
        try {
            // 确保包含新的邮件发送函数
            if (!function_exists('sendEmailSMTP')) {
                require_once __DIR__ . '/../helpers/functions.php';
            }
            
            return sendEmailSMTP($email, $subject, $body, $smtpConfig, $fromEmail, $siteName);
        } catch (\Exception $e) {
            error_log("Password reset email send failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create HTML email template for password reset
     */
    private function createPasswordResetEmailTemplate($resetLink, $siteName = 'SubAlert') {
        return '<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head><body>
<div style="font-family:Arial,sans-serif;max-width:600px;margin:20px auto;padding:20px;border:1px solid #ddd;border-radius:8px;">
    <div style="text-align:center;margin-bottom:30px;">
        <h1 style="color:#3b82f6;margin-bottom:10px;">🔐 密码重置</h1>
        <p style="color:#666;font-size:16px;">您申请了重置 ' . htmlspecialchars($siteName) . ' 账户密码</p>
    </div>
    
    <div style="background:#f8f9fa;border:1px solid #e9ecef;padding:20px;margin:20px 0;border-radius:6px;">
        <h2 style="color:#333;margin-bottom:15px;">📋 重置说明</h2>
        <p style="color:#666;line-height:1.6;margin-bottom:20px;">
            我们收到了您的密码重置请求。请点击下方按钮重置您的密码：
        </p>
        
        <div style="text-align:center;margin:30px 0;">
            <a href="' . htmlspecialchars($resetLink) . '" 
               style="display:inline-block;padding:15px 30px;background:#3b82f6;color:white;text-decoration:none;border-radius:6px;font-weight:600;font-size:16px;">
                🔑 重置密码
            </a>
        </div>
        
        <p style="color:#666;font-size:14px;line-height:1.6;">
            如果上方按钮无法点击，请复制以下链接到浏览器地址栏：<br>
            <span style="background:#f1f5f9;padding:8px;border-radius:4px;word-break:break-all;font-family:monospace;font-size:12px;">' . htmlspecialchars($resetLink) . '</span>
        </p>
    </div>
    
    <div style="background:#fff3cd;border:1px solid #ffeaa7;padding:15px;margin:20px 0;border-radius:6px;">
        <h3 style="color:#856404;margin-bottom:10px;">⚠️ 安全提示</h3>
        <ul style="color:#856404;margin:0;padding-left:20px;font-size:14px;">
            <li>此链接有效期为 <strong>1小时</strong></li>
            <li>链接只能使用一次</li>
            <li>如果您没有申请密码重置，请忽略此邮件</li>
            <li>请不要将此链接分享给任何人</li>
        </ul>
    </div>
    
    <div style="text-align:center;margin-top:30px;padding-top:20px;border-top:1px solid #eee;">
        <p style="color:#999;font-size:12px;">
            此邮件由 ' . htmlspecialchars($siteName) . ' 自动发送<br>
            如有疑问，请联系系统管理员
        </p>
    </div>
</div>
</body></html>';
    }
}
?>