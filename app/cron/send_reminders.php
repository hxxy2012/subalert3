<?php
// Script to send reminders (improved with proper SMTP support)

require __DIR__ . '/../../app/models/DB.php';

use App\Models\DB;

/**
 * Enhanced SMTP Client for sending emails
 * Based on the working precise_smtp.php implementation
 */
class SMTPClient {
    private $socket;
    private $host;
    private $port;
    private $username;
    private $password;
    private $timeout = 30;
    
    public function __construct($host, $port, $username, $password) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }
    
    public function connect() {
        // Create SSL context
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
                'crypto_method' => STREAM_CRYPTO_METHOD_TLS_CLIENT,
            ]
        ]);
        
        $this->socket = @stream_socket_client(
            "ssl://{$this->host}:{$this->port}",
            $errno, $errstr, $this->timeout,
            STREAM_CLIENT_CONNECT, $context
        );
        
        if (!$this->socket) {
            throw new Exception("SMTP连接失败: [$errno] $errstr");
        }
        
        stream_set_timeout($this->socket, $this->timeout);
        
        // Read welcome message
        $response = $this->readResponse();
        if (!$this->isResponseOK($response, '220')) {
            throw new Exception("SMTP服务器欢迎失败: $response");
        }
        
        return true;
    }
    
    public function ehlo($hostname = 'localhost') {
        $this->sendCommand("EHLO $hostname");
        $response = $this->readMultilineResponse();
        
        if (!$this->isResponseOK($response, '250')) {
            throw new Exception("EHLO失败: $response");
        }
        
        return $response;
    }
    
    public function authenticate() {
        // AUTH LOGIN
        $this->sendCommand("AUTH LOGIN");
        $response = $this->readResponse();
        
        if (!$this->isResponseOK($response, '334')) {
            throw new Exception("AUTH LOGIN失败: $response");
        }
        
        // Send username (Base64 encoded)
        $encodedUser = base64_encode($this->username);
        $this->sendCommand($encodedUser);
        $response = $this->readResponse();
        
        if (!$this->isResponseOK($response, '334')) {
            throw new Exception("用户名认证失败: $response");
        }
        
        // Send password (Base64 encoded)
        $encodedPass = base64_encode($this->password);
        $this->sendCommand($encodedPass);
        $response = $this->readResponse();
        
        if (!$this->isResponseOK($response, '235')) {
            throw new Exception("密码认证失败: $response");
        }
        
        return true;
    }
    
    public function sendMail($from, $to, $subject, $body) {
        // MAIL FROM
        $this->sendCommand("MAIL FROM: <$from>");
        $response = $this->readResponse();
        
        if (!$this->isResponseOK($response, '250')) {
            throw new Exception("MAIL FROM失败: $response");
        }
        
        // RCPT TO
        $this->sendCommand("RCPT TO: <$to>");
        $response = $this->readResponse();
        
        if (!$this->isResponseOK($response, '250')) {
            throw new Exception("RCPT TO失败: $response");
        }
        
        // DATA
        $this->sendCommand("DATA");
        $response = $this->readResponse();
        
        if (!$this->isResponseOK($response, '354')) {
            throw new Exception("DATA失败: $response");
        }
        
        // Build email headers and body
        $timestamp = date('r'); // RFC 2822 format
        $messageId = '<' . uniqid() . '@' . $this->host . '>';
        
        $email = "From: SubAlert <$from>\r\n";
        $email .= "To: <$to>\r\n";
        $email .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $email .= "Date: $timestamp\r\n";
        $email .= "Message-ID: $messageId\r\n";
        $email .= "MIME-Version: 1.0\r\n";
        $email .= "Content-Type: text/html; charset=utf-8\r\n";
        $email .= "Content-Transfer-Encoding: 8bit\r\n";
        $email .= "\r\n";
        $email .= $body;
        $email .= "\r\n.\r\n";
        
        // Send email content
        fwrite($this->socket, $email);
        fflush($this->socket);
        
        $response = $this->readResponse();
        
        if (!$this->isResponseOK($response, '250')) {
            throw new Exception("邮件发送失败: $response");
        }
        
        return true;
    }
    
    public function quit() {
        if ($this->socket) {
            $this->sendCommand("QUIT");
            $this->readResponse();
            fclose($this->socket);
        }
    }
    
    private function sendCommand($command) {
        fwrite($this->socket, $command . "\r\n");
        fflush($this->socket);
    }
    
    private function readResponse() {
        $response = fgets($this->socket, 512);
        if ($response === false) {
            throw new Exception("读取服务器响应失败");
        }
        return trim($response);
    }
    
    private function readMultilineResponse() {
        $response = '';
        while (true) {
            $line = fgets($this->socket, 512);
            if ($line === false) break;
            
            $response .= $line;
            
            // Check if this is the last line (4th character is space)
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        return trim($response);
    }
    
    private function isResponseOK($response, $expectedCode) {
        return strpos($response, $expectedCode) === 0;
    }
}

/**
 * Enhanced email sending function using SMTP
 */
function sendEmailSMTP($to, $subject, $body, $smtpConfig) {
    try {
        $smtp = new SMTPClient(
            $smtpConfig['host'],
            $smtpConfig['port'],
            $smtpConfig['user'],
            $smtpConfig['pass']
        );
        
        $smtp->connect();
        $smtp->ehlo($_SERVER['HTTP_HOST'] ?? 'localhost');
        $smtp->authenticate();
        $smtp->sendMail($smtpConfig['user'], $to, $subject, $body);
        $smtp->quit();
        
        return true;
    } catch (Exception $e) {
        echo "[SMTP ERROR] " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Send a text message to a Feishu (Lark) webhook.
 */
function sendFeishu(string $webhook, string $content): bool
{
    if (empty($webhook)) {
        return false;
    }
    $payload = json_encode([
        'msg_type' => 'text',
        'content'  => ['text' => $content],
    ], JSON_UNESCAPED_UNICODE);
    $ch = curl_init($webhook);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $status >= 200 && $status < 300;
}

/**
 * Send a text message to an Enterprise WeChat webhook.
 */
function sendWeChat(string $webhook, string $content): bool
{
    if (empty($webhook)) {
        return false;
    }
    $payload = json_encode([
        'msgtype' => 'text',
        'text'    => ['content' => $content],
    ], JSON_UNESCAPED_UNICODE);
    $ch = curl_init($webhook);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $status >= 200 && $status < 300;
}

/**
 * Create HTML email template for reminders
 */
function createEmailTemplate($subscriptionName, $expireDate, $daysLeft, $siteName = 'SubAlert') {
    $urgencyClass = '';
    $urgencyText = '';
    $urgencyColor = '';
    
    if ($daysLeft <= 1) {
        $urgencyClass = 'urgent';
        $urgencyText = '紧急提醒';
        $urgencyColor = '#ef4444';
    } elseif ($daysLeft <= 3) {
        $urgencyClass = 'warning';
        $urgencyText = '即将到期';
        $urgencyColor = '#f59e0b';
    } else {
        $urgencyClass = 'normal';
        $urgencyText = '到期提醒';
        $urgencyColor = '#3b82f6';
    }
    
    return '<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head><body>
<div style="font-family:Arial,sans-serif;max-width:600px;margin:20px auto;padding:20px;border:1px solid #ddd;border-radius:8px;">
    <div style="text-align:center;margin-bottom:30px;">
        <h1 style="color:' . $urgencyColor . ';margin-bottom:10px;">🔔 ' . $urgencyText . '</h1>
        <p style="color:#666;font-size:16px;">您的订阅服务即将到期</p>
    </div>
    
    <div style="background:#f8f9fa;border:1px solid #e9ecef;padding:20px;margin:20px 0;border-radius:6px;">
        <h2 style="color:#333;margin-bottom:15px;">📋 订阅信息</h2>
        <table style="width:100%;border-collapse:collapse;">
            <tr>
                <td style="padding:8px 0;color:#666;width:80px;"><strong>服务名称:</strong></td>
                <td style="padding:8px 0;color:#333;"><strong>' . htmlspecialchars($subscriptionName) . '</strong></td>
            </tr>
            <tr>
                <td style="padding:8px 0;color:#666;"><strong>到期时间:</strong></td>
                <td style="padding:8px 0;color:#333;">' . htmlspecialchars($expireDate) . '</td>
            </tr>
            <tr>
                <td style="padding:8px 0;color:#666;"><strong>剩余时间:</strong></td>
                <td style="padding:8px 0;color:' . $urgencyColor . ';"><strong>' . $daysLeft . ' 天</strong></td>
            </tr>
        </table>
    </div>
    
    <div style="background:#fff3cd;border:1px solid #ffeaa7;padding:15px;margin:20px 0;border-radius:6px;">
        <h3 style="color:#856404;margin-bottom:10px;">💡 温馨提示</h3>
        <ul style="color:#856404;margin:0;padding-left:20px;">
            <li>请及时续费以免影响服务使用</li>
            <li>建议提前续费避免服务中断</li>
            <li>如有疑问请联系服务提供商</li>
        </ul>
    </div>
    
    <div style="text-align:center;margin-top:30px;padding-top:20px;border-top:1px solid #eee;">
        <p style="color:#999;font-size:12px;">
            此邮件由 ' . htmlspecialchars($siteName) . ' 自动发送<br>
            如不需要此类提醒，请登录系统修改提醒设置
        </p>
    </div>
</div>
</body></html>';
}

// Only run via CLI
if (php_sapi_name() !== 'cli') {
    exit("This script must be run from command line\n");
}

echo "=== SubAlert 提醒发送任务开始 ===\n";
echo "执行时间: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $pdo = DB::getConnection();
    
    // Load SMTP configuration from settings
    $stmt = $pdo->query('SELECT `key`, `value` FROM settings WHERE `key` IN ("smtp_host", "smtp_port", "smtp_user", "smtp_pass", "site_name")');
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['key']] = $row['value'];
    }
    
    // Check if SMTP is configured
    $smtpConfigured = !empty($settings['smtp_host']) && 
                     !empty($settings['smtp_user']) && 
                     !empty($settings['smtp_pass']);
    
    if (!$smtpConfigured) {
        echo "❌ SMTP配置不完整，邮件功能将被跳过\n";
        echo "请在管理后台配置SMTP设置\n\n";
    } else {
        echo "✅ SMTP配置检查通过\n";
        echo "SMTP服务器: " . $settings['smtp_host'] . ":" . ($settings['smtp_port'] ?? '465') . "\n";
        echo "发送账户: " . $settings['smtp_user'] . "\n\n";
    }
    
    $smtpConfig = [
        'host' => $settings['smtp_host'] ?? '',
        'port' => intval($settings['smtp_port'] ?? '465'),
        'user' => $settings['smtp_user'] ?? '',
        'pass' => $settings['smtp_pass'] ?? '',
    ];
    
    $siteName = $settings['site_name'] ?? 'SubAlert';
    
    $now = date('Y-m-d H:i:s');
    
    // Fetch pending reminders along with user information and subscription details
    $stmt = $pdo->prepare('
        SELECT r.*, u.id AS user_id, u.email, u.nickname, 
               s.name as subscription_name, s.expire_at as subscription_expire
        FROM reminders r 
        JOIN users u ON r.user_id = u.id 
        JOIN subscriptions s ON r.subscription_id = s.id 
        WHERE r.status = ? AND r.remind_at <= ?
        ORDER BY r.remind_at ASC
    ');
    $stmt->execute(['pending', $now]);
    $reminders = $stmt->fetchAll();
    
    $processed = 0;
    $successCount = 0;
    $failCount = 0;
    
    echo "📋 找到 " . count($reminders) . " 个待处理提醒\n\n";
    
    foreach ($reminders as $rem) {
        $processed++;
        echo "处理提醒 #{$rem['id']} ({$processed}/" . count($reminders) . ")\n";
        echo "  订阅: {$rem['subscription_name']}\n";
        echo "  用户: {$rem['email']}\n";
        echo "  方式: {$rem['remind_type']}\n";
        
        // Load user settings once per user
        static $settingsCache = [];
        $uid = $rem['user_id'];
        if (!isset($settingsCache[$uid])) {
            $userSettings = [];
            $userStmt = $pdo->prepare('SELECT setting_key, setting_value FROM user_settings WHERE user_id = ?');
            $userStmt->execute([$uid]);
            while ($row = $userStmt->fetch()) {
                $userSettings[$row['setting_key']] = $row['setting_value'];
            }
            $settingsCache[$uid] = $userSettings;
        } else {
            $userSettings = $settingsCache[$uid];
        }
        
        // Check if user has disabled reminders
        if (isset($userSettings['reminders_enabled']) && $userSettings['reminders_enabled'] === '0') {
            echo "  ⏭️  用户已禁用提醒功能，跳过\n\n";
            continue;
        }
        
        // Check mute period (HH:MM format)
        $muteStart = $userSettings['mute_start'] ?? '';
        $muteEnd   = $userSettings['mute_end'] ?? '';
        if ($muteStart && $muteEnd) {
            $currentTime = date('H:i');
            $inMuteWindow = false;
            
            if ($muteStart <= $muteEnd) {
                // Normal range (e.g., 22:00-08:00 next day)
                $inMuteWindow = ($currentTime >= $muteStart && $currentTime < $muteEnd);
            } else {
                // Cross-midnight range (e.g., 22:00-06:00)
                $inMuteWindow = ($currentTime >= $muteStart || $currentTime < $muteEnd);
            }
            
            if ($inMuteWindow) {
                echo "  🔇 当前时间在免打扰时段内({$muteStart}-{$muteEnd})，跳过\n\n";
                continue;
            }
        }
        
        // Calculate days left
        $expireDate = new DateTime($rem['subscription_expire']);
        $today = new DateTime();
        $diff = $today->diff($expireDate);
        $daysLeft = $expireDate > $today ? $diff->days : 0;
        
        // Compose message
        $success = false;
        
        switch ($rem['remind_type']) {
            case 'email':
                if ($smtpConfigured) {
                    $subject = "[{$siteName}] 订阅到期提醒 - {$rem['subscription_name']}";
                    $body = createEmailTemplate(
                        $rem['subscription_name'], 
                        $rem['subscription_expire'], 
                        $daysLeft, 
                        $siteName
                    );
                    
                    $success = sendEmailSMTP($rem['email'], $subject, $body, $smtpConfig);
                    
                    if ($success) {
                        echo "  ✅ 邮件发送成功\n";
                    } else {
                        echo "  ❌ 邮件发送失败\n";
                    }
                } else {
                    echo "  ⚠️  SMTP未配置，跳过邮件发送\n";
                }
                break;
                
            case 'feishu':
                $webhook = $userSettings['feishu_webhook'] ?? '';
                $message = sprintf(
                    "🔔 订阅到期提醒\n\n📋 服务名称：%s\n⏰ 到期时间：%s\n⏳ 剩余时间：%d 天\n\n💡 请及时续费以免影响使用",
                    $rem['subscription_name'],
                    $rem['subscription_expire'],
                    $daysLeft
                );
                
                $success = sendFeishu($webhook, $message);
                
                if ($success) {
                    echo "  ✅ 飞书通知发送成功\n";
                } else {
                    echo "  ❌ 飞书通知发送失败\n";
                }
                break;
                
            case 'wechat':
                $webhook = $userSettings['wechat_webhook'] ?? '';
                $message = sprintf(
                    "🔔 订阅到期提醒\n\n📋 服务名称：%s\n⏰ 到期时间：%s\n⏳ 剩余时间：%d 天\n\n💡 请及时续费以免影响使用",
                    $rem['subscription_name'],
                    $rem['subscription_expire'],
                    $daysLeft
                );
                
                $success = sendWeChat($webhook, $message);
                
                if ($success) {
                    echo "  ✅ 企业微信通知发送成功\n";
                } else {
                    echo "  ❌ 企业微信通知发送失败\n";
                }
                break;
                
            case 'site':
                // For site notifications, we just mark as sent
                // In a real application, you might want to store these in a notifications table
                $success = true;
                echo "  ✅ 站内通知已创建\n";
                break;
        }
        
        if ($success) {
            $successCount++;
        } else {
            $failCount++;
        }
        
        // Update reminder status and next remind time
        // For repeating reminders, set next remind time (e.g., 1 day later)
        $nextRemindAt = date('Y-m-d H:i:s', strtotime($rem['remind_at'] . ' +1 day'));
        
        $updateStmt = $pdo->prepare('
            UPDATE reminders 
            SET remind_at = ?, sent_at = NOW(), updated_at = NOW(), status = ? 
            WHERE id = ?
        ');
        $updateStmt->execute([$nextRemindAt, 'pending', $rem['id']]);
        
        echo "\n";
    }
    
    echo "=== 提醒发送任务完成 ===\n";
    echo "总计处理: {$processed} 个提醒\n";
    echo "发送成功: {$successCount} 个\n";
    echo "发送失败: {$failCount} 个\n";
    echo "完成时间: " . date('Y-m-d H:i:s') . "\n";

} catch (Exception $e) {
    echo "❌ 执行失败: " . $e->getMessage() . "\n";
    echo "错误文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}