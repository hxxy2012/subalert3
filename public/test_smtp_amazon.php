<?php
// Amazon SES SMTP客户端测试脚本
// 保存为 public/smtp_test.php

echo "🔧 Amazon SES SMTP客户端测试\n";
echo "============================\n\n";

$testEmail = 'hxxy2012@gmail.com'; // 修改为您要测试的邮箱地址
$fromEmail = 'noreply@subalert.nextone.im'; // 修改为您在SES中验证的发件人邮箱
echo "📧 测试邮箱: $testEmail\n";
echo "📤 发件人邮箱: $fromEmail\n\n";

// 1. 加载配置
$configPath = __DIR__ . '/../app/config.php';
$config = require $configPath;
$pdo = new PDO(
    sprintf('mysql:host=%s;dbname=%s;charset=%s', $config['db_host'], $config['db_name'], $config['db_charset']),
    $config['db_user'], $config['db_password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$stmt = $pdo->query('SELECT `key`, `value` FROM settings WHERE `key` IN ("smtp_host", "smtp_port", "smtp_user", "smtp_pass", "site_name")');
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['key']] = $row['value'];
}

$host = $settings['smtp_host'];
$port = intval($settings['smtp_port'] ?? '587');
$user = $settings['smtp_user'];
$pass = $settings['smtp_pass'];
$siteName = $settings['site_name'] ?? 'SubAlert';

echo "📋 配置信息:\n";
echo "   Host: $host:$port\n";
echo "   User: $user\n";
echo "   Pass: " . substr($pass, 0, 4) . "****(共" . strlen($pass) . "位)\n\n";

// 检测是否为Amazon SES
$isAmazonSES = strpos($host, 'amazonaws.com') !== false;
echo "🔍 检测到SMTP服务器类型: " . ($isAmazonSES ? 'Amazon SES' : '通用SMTP') . "\n\n";

// 2. 增强的SMTP客户端实现 - 兼容Amazon SES
class EnhancedSMTPClient {
    private $socket;
    private $host;
    private $port;
    private $timeout = 60; // Amazon SES推荐更长的超时时间
    private $isAmazonSES;
    
    public function __construct($host, $port) {
        $this->host = $host;
        $this->port = $port;
        $this->isAmazonSES = strpos($host, 'amazonaws.com') !== false;
        
        // Amazon SES通常使用587端口(STARTTLS)，而不是465(SSL)
        if ($this->isAmazonSES && $port == 587) {
            echo "✅ Amazon SES 使用 STARTTLS 连接方式\n";
        } elseif ($this->isAmazonSES && $port == 465) {
            echo "⚠️  Amazon SES 推荐使用587端口和STARTTLS，而不是465端口的SSL\n";
        }
    }
    
    public function connect() {
        echo "🔌 连接到 {$this->host}:{$this->port}...\n";
        
        if ($this->isAmazonSES && $this->port == 587) {
            // Amazon SES 使用 STARTTLS (先建立普通连接，然后升级到TLS)
            $this->socket = @stream_socket_client(
                "{$this->host}:{$this->port}",
                $errno, $errstr, $this->timeout
            );
        } else {
            // 传统SSL连接 (465端口)
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
        }
        
        if (!$this->socket) {
            throw new Exception("连接失败: [$errno] $errstr");
        }
        
        stream_set_timeout($this->socket, $this->timeout);
        
        // 读取欢迎消息
        $response = $this->readResponse();
        echo "   服务器欢迎: $response\n";
        
        if (!$this->isResponseOK($response, '220')) {
            throw new Exception("服务器欢迎失败: $response");
        }
        
        if ($this->isAmazonSES && $this->port == 587) {
            echo "   ✅ 普通连接建立成功，准备升级到TLS\n\n";
        } else {
            echo "   ✅ SSL连接建立成功\n\n";
        }
        
        return true;
    }
    
    public function ehlo($hostname = 'localhost') {
        echo "📝 发送EHLO命令...\n";
        
        $this->sendCommand("EHLO $hostname");
        $response = $this->readMultilineResponse();
        
        echo "   服务器响应:\n";
        foreach (explode("\n", trim($response)) as $line) {
            if ($line) echo "      $line\n";
        }
        
        if (!$this->isResponseOK($response, '250')) {
            throw new Exception("EHLO失败: $response");
        }
        
        echo "   ✅ EHLO成功\n";
        
        // 如果是Amazon SES的587端口，需要升级到TLS
        if ($this->isAmazonSES && $this->port == 587 && strpos($response, 'STARTTLS') !== false) {
            echo "\n🔒 开始STARTTLS升级...\n";
            $this->startTLS();
            
            // 升级后需要重新发送EHLO
            echo "📝 TLS升级后重新发送EHLO...\n";
            $this->sendCommand("EHLO $hostname");
            $response = $this->readMultilineResponse();
            
            echo "   TLS升级后服务器响应:\n";
            foreach (explode("\n", trim($response)) as $line) {
                if ($line) echo "      $line\n";
            }
            
            if (!$this->isResponseOK($response, '250')) {
                throw new Exception("TLS升级后EHLO失败: $response");
            }
            
            echo "   ✅ TLS升级后EHLO成功\n";
        }
        
        echo "\n";
        return $response;
    }
    
    private function startTLS() {
        $this->sendCommand("STARTTLS");
        $response = $this->readResponse();
        echo "   STARTTLS响应: $response\n";
        
        if (!$this->isResponseOK($response, '220')) {
            throw new Exception("STARTTLS失败: $response");
        }
        
        // 升级连接到TLS
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
                'crypto_method' => STREAM_CRYPTO_METHOD_TLS_CLIENT,
            ]
        ]);
        
        $result = stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        
        if (!$result) {
            throw new Exception("TLS升级失败");
        }
        
        echo "   ✅ 成功升级到TLS连接\n";
    }
    
    public function authenticate($username, $password) {
        echo "🔐 开始身份认证...\n";
        echo "   用户名: $username\n";
        echo "   密码长度: " . strlen($password) . " 位\n";
        
        if ($this->isAmazonSES) {
            echo "   认证类型: Amazon SES SMTP 凭证\n";
        }
        
        // AUTH LOGIN
        $this->sendCommand("AUTH LOGIN");
        $response = $this->readResponse();
        echo "   AUTH LOGIN响应: $response\n";
        
        if (!$this->isResponseOK($response, '334')) {
            throw new Exception("AUTH LOGIN失败: $response");
        }
        
        // 发送用户名（Base64编码）
        $encodedUser = base64_encode($username);
        echo "   发送用户名 (Base64): " . substr($encodedUser, 0, 16) . "...\n";
        $this->sendCommand($encodedUser);
        $response = $this->readResponse();
        echo "   用户名响应: $response\n";
        
        if (!$this->isResponseOK($response, '334')) {
            throw new Exception("用户名认证失败，请检查SMTP用户名: $response");
        }
        
        // 发送密码（Base64编码）
        $encodedPass = base64_encode($password);
        echo "   发送密码 (Base64): " . substr($encodedPass, 0, 12) . "...\n";
        $this->sendCommand($encodedPass);
        $response = $this->readResponse();
        echo "   密码响应: $response\n";
        
        if (!$this->isResponseOK($response, '235')) {
            if ($this->isAmazonSES) {
                throw new Exception("Amazon SES认证失败，请检查SMTP凭证。确保：\n1. 使用正确的SMTP用户名和密码\n2. 该邮箱地址已在SES中验证\n3. SES不在沙盒模式，或目标邮箱已验证\n响应: $response");
            } else {
                throw new Exception("密码认证失败: $response");
            }
        }
        
        echo "   ✅ 身份认证成功！\n\n";
        return true;
    }
    
    public function sendMail($from, $to, $subject, $body, $fromName = 'SubAlert') {
        echo "📧 开始发送邮件...\n";
        echo "   从: $fromName <$from>\n";
        echo "   到: $to\n";
        echo "   主题: $subject\n";
        
        if ($this->isAmazonSES) {
            echo "   🔍 Amazon SES 检查:\n";
            echo "      - 发件人邮箱 $from 是否已在SES中验证？\n";
            echo "      - SES账户是否仍在沙盒模式？\n";
            echo "      - 如在沙盒模式，收件人邮箱是否已验证？\n\n";
        }
        
        // MAIL FROM - 使用真实邮箱地址，不是SMTP用户名
        $this->sendCommand("MAIL FROM: <$from>");
        $response = $this->readResponse();
        echo "   MAIL FROM响应: $response\n";
        
        if (!$this->isResponseOK($response, '250')) {
            if ($this->isAmazonSES) {
                throw new Exception("MAIL FROM失败 - 发件人邮箱 '$from' 可能未在Amazon SES中验证，或邮箱格式不正确: $response");
            } else {
                throw new Exception("MAIL FROM失败: $response");
            }
        }
        
        // RCPT TO
        $this->sendCommand("RCPT TO: <$to>");
        $response = $this->readResponse();
        echo "   RCPT TO响应: $response\n";
        
        if (!$this->isResponseOK($response, '250')) {
            if ($this->isAmazonSES) {
                if (strpos($response, 'MessageRejected') !== false) {
                    throw new Exception("RCPT TO失败 - Amazon SES沙盒模式限制。请验证收件人邮箱 '$to' 或申请移出沙盒模式: $response");
                } else {
                    throw new Exception("RCPT TO失败 - Amazon SES拒绝收件人 '$to': $response");
                }
            } else {
                throw new Exception("RCPT TO失败: $response");
            }
        }
        
        // DATA
        $this->sendCommand("DATA");
        $response = $this->readResponse();
        echo "   DATA响应: $response\n";
        
        if (!$this->isResponseOK($response, '354')) {
            throw new Exception("DATA失败: $response");
        }
        
        // 构建邮件头和正文 - Amazon SES兼容格式
        $timestamp = date('r'); // RFC 2822 格式
        $messageId = '<' . uniqid('ses-', true) . '@' . ($this->isAmazonSES ? 'amazonses.com' : $this->host) . '>';
        
        // 使用正确的发件人格式
        $email = "From: $fromName <$from>\r\n";
        $email .= "To: <$to>\r\n";
        $email .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $email .= "Date: $timestamp\r\n";
        $email .= "Message-ID: $messageId\r\n";
        $email .= "MIME-Version: 1.0\r\n";
        $email .= "Content-Type: text/html; charset=utf-8\r\n";
        $email .= "Content-Transfer-Encoding: 8bit\r\n";
        
        // Amazon SES 推荐的额外头信息
        if ($this->isAmazonSES) {
            $email .= "X-Mailer: SubAlert Amazon SES Client\r\n";
            $email .= "Return-Path: $from\r\n";
        }
        
        $email .= "\r\n";
        $email .= $body;
        $email .= "\r\n.\r\n";
        
        // 发送邮件内容
        fwrite($this->socket, $email);
        fflush($this->socket);
        
        $response = $this->readResponse();
        echo "   发送结果: $response\n";
        
        if (!$this->isResponseOK($response, '250')) {
            throw new Exception("邮件发送失败: $response");
        }
        
        // 提取Message ID（Amazon SES特有）
        if ($this->isAmazonSES && preg_match('/Ok ([0-9a-f\-]+)/', $response, $matches)) {
            echo "   📬 Amazon SES Message ID: {$matches[1]}\n";
        } elseif (preg_match('/Ok: queued as (.+)/', $response, $matches)) {
            echo "   📬 队列ID: {$matches[1]}\n";
        }
        
        echo "   ✅ 邮件发送成功！\n\n";
        return true;
    }
    
    public function quit() {
        echo "👋 关闭连接...\n";
        $this->sendCommand("QUIT");
        $response = $this->readResponse();
        echo "   QUIT响应: $response\n";
        
        if ($this->socket) {
            fclose($this->socket);
        }
        
        echo "   ✅ 连接已关闭\n";
    }
    
    private function sendCommand($command) {
        fwrite($this->socket, $command . "\r\n");
        fflush($this->socket);
    }
    
    private function readResponse() {
        $response = fgets($this->socket, 1024); // Amazon SES可能返回更长的响应
        if ($response === false) {
            throw new Exception("读取服务器响应失败");
        }
        return trim($response);
    }
    
    private function readMultilineResponse() {
        $response = '';
        while (true) {
            $line = fgets($this->socket, 1024);
            if ($line === false) break;
            
            $response .= $line;
            
            // 检查是否是最后一行（第4个字符是空格）
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

// 3. 创建优化的邮件模板
function createTestEmailTemplate($isAmazonSES, $host, $user, $port) {
    $serviceType = $isAmazonSES ? 'Amazon SES' : '通用SMTP';
    $testTime = date('Y-m-d H:i:s');
    
    return '<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head><body>
<div style="font-family:Arial,sans-serif;max-width:600px;margin:20px auto;padding:20px;border:1px solid #ddd;border-radius:8px;">
    <h2 style="color:#10b981;">🎉 ' . $serviceType . ' SMTP测试成功！</h2>
    <p>这封邮件是通过精确模拟邮件客户端的方式发送的，专门为' . $serviceType . '优化。</p>
    
    <div style="background:#f0f9ff;border:1px solid #0ea5e9;padding:15px;margin:20px 0;border-radius:6px;">
        <h3>📊 技术详情</h3>
        <ul>
            <li><strong>发送时间:</strong> ' . $testTime . '</li>
            <li><strong>SMTP服务器:</strong> ' . $host . ':' . $port . '</li>
            <li><strong>服务类型:</strong> ' . $serviceType . '</li>
            <li><strong>发送账户:</strong> ' . $user . '</li>
            <li><strong>认证方式:</strong> AUTH LOGIN</li>
            <li><strong>连接方式:</strong> ' . ($port == 587 ? 'STARTTLS' : 'SSL') . '</li>
            <li><strong>编码方式:</strong> UTF-8</li>
        </ul>
    </div>' . 
    
    ($isAmazonSES ? '
    <div style="background:#fff7ed;border:1px solid #fb923c;padding:15px;margin:20px 0;border-radius:6px;">
        <h3>🔶 Amazon SES 特别说明</h3>
        <ul>
            <li>✅ 发件人邮箱地址已通过SES验证</li>
            <li>✅ SMTP凭证配置正确</li>
            <li>✅ 网络连接到AWS服务正常</li>
            <li>' . ($port == 587 ? '✅ 使用推荐的587端口和STARTTLS' : '⚠️ 推荐使用587端口') . '</li>
            <li>💡 如需发送到未验证邮箱，请申请移出沙盒模式</li>
        </ul>
    </div>' : '') . '
    
    <div style="background:#fef3c7;border:1px solid #f59e0b;padding:15px;margin:20px 0;border-radius:6px;">
        <h3>✅ 测试结果</h3>
        <p><strong>如果您收到这封邮件，说明：</strong></p>
        <ul>
            <li>✅ SMTP服务器连接正常</li>
            <li>✅ TLS/SSL加密工作正常</li>
            <li>✅ 身份认证成功</li>
            <li>✅ 邮件发送功能完全正常</li>
            <li>✅ 可以正常使用密码重置功能</li>' .
            ($isAmazonSES ? '<li>✅ Amazon SES配置完美</li>' : '') . '
        </ul>
    </div>
    
    <p><strong style="color:#10b981;">恭喜！您的SubAlert邮件系统配置完美！</strong></p>
    
    <hr>
    <p style="color:#666;font-size:12px;">
        此邮件由SubAlert ' . $serviceType . ' 客户端发送<br>
        测试时间: ' . $testTime . '<br>
        如果您没有申请此测试，请忽略此邮件
    </p>
</div>
</body></html>';
}

// 4. 执行测试
try {
    echo str_repeat("=", 60) . "\n";
    echo "🚀 开始 " . ($isAmazonSES ? 'Amazon SES' : '通用') . " SMTP测试\n";
    echo str_repeat("=", 60) . "\n";
    
    if ($isAmazonSES) {
        echo "🔍 Amazon SES 预检查:\n";
        echo "   ✓ 确保发件人邮箱 $fromEmail 已在SES控制台中验证\n";
        echo "   ✓ 确保SMTP凭证正确（不是AWS访问密钥）\n";
        echo "   ✓ 检查SES账户状态（沙盒模式限制）\n";
        echo "   ✓ 确认区域设置正确\n\n";
        
        // 推荐的Amazon SES设置
        if ($port != 587) {
            echo "⚠️  建议: Amazon SES推荐使用587端口和STARTTLS\n";
            echo "   当前端口: $port\n";
            echo "   推荐端口: 587\n\n";
        }
    }
    
    $smtp = new EnhancedSMTPClient($host, $port);
    
    // 连接
    $smtp->connect();
    
    // EHLO
    $smtp->ehlo($_SERVER['HTTP_HOST'] ?? 'localhost');
    
    // 认证
    $smtp->authenticate($user, $pass);
    
    // 发送邮件
    $subject = "[SubAlert] " . ($isAmazonSES ? 'Amazon SES' : 'SMTP') . " 测试 - " . date('H:i:s');
    $body = createTestEmailTemplate($isAmazonSES, $host, $user, $port);
    
    $smtp->sendMail($fromEmail, $testEmail, $subject, $body);
    
    // 关闭连接
    $smtp->quit();
    
    echo str_repeat("=", 60) . "\n";
    echo "🎉 测试完成 - 邮件发送成功！\n";
    echo str_repeat("=", 60) . "\n";
    echo "📬 请检查 $testEmail 的收件箱\n";
    echo "📁 也请检查垃圾邮件文件夹\n";
    
    if ($isAmazonSES) {
        echo "⏰ Amazon SES邮件通常在几秒钟内到达\n";
        echo "📊 您可以在SES控制台查看发送统计\n";
    } else {
        echo "⏰ 邮件通常在1-3分钟内到达\n";
    }
    
    echo "🎯 如果收到邮件，说明配置完全正确！\n\n";
    
} catch (Exception $e) {
    echo "\n❌ 测试失败: " . $e->getMessage() . "\n\n";
    
    if ($isAmazonSES) {
        echo "🔧 Amazon SES 故障排除:\n";
        echo "   1. 检查SES控制台中发件人邮箱 '$fromEmail' 的验证状态\n";
        echo "   2. 确认使用的是SMTP凭证，不是AWS访问密钥\n";
        echo "   3. 检查SES账户是否仍在沙盒模式\n";
        echo "   4. 如在沙盒模式，确保收件人邮箱 '$testEmail' 已验证\n";
        echo "   5. 检查SES服务区域是否正确 (当前: ap-southeast-1)\n";
        echo "   6. 确认AWS账户状态正常\n";
        echo "   7. 检查发送限制和费率限制\n";
        echo "   8. 尝试使用其他已验证的发件人邮箱地址\n";
    } else {
        echo "🔧 通用SMTP 故障排除:\n";
        echo "   1. 检查SMTP用户名和密码\n";
        echo "   2. 确认SMTP服务已启用\n";
        echo "   3. 检查服务器防火墙设置\n";
        echo "   4. 验证邮箱账户状态\n";
    }
    
    echo "\n📞 如需帮助，请联系系统管理员\n";
}

echo "\n测试时间: " . date('Y-m-d H:i:s') . "\n";
echo "脚本版本: Amazon SES兼容版 v1.0\n";
?>