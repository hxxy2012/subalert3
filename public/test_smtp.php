<?php
// 精确模拟邮件客户端的SMTP脚本
// 保存为 public/precise_smtp.php

echo "🔧 精确SMTP客户端模拟测试\n";
echo "============================\n\n";

$testEmail = 'hxxy2012@gmail.com';
echo "📧 测试邮箱: $testEmail\n\n";

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
$port = intval($settings['smtp_port'] ?? '465');
$user = $settings['smtp_user'];
$pass = $settings['smtp_pass'];
$siteName = $settings['site_name'] ?? 'SubAlert';

echo "📋 配置信息:\n";
echo "   Host: $host:$port\n";
echo "   User: $user\n";
echo "   Pass: " . substr($pass, 0, 4) . "****(共" . strlen($pass) . "位)\n\n";

// 2. 精确的SMTP客户端实现
class PreciseSMTPClient {
    private $socket;
    private $host;
    private $port;
    private $timeout = 30;
    
    public function __construct($host, $port) {
        $this->host = $host;
        $this->port = $port;
    }
    
    public function connect() {
        echo "🔌 连接到 {$this->host}:{$this->port}...\n";
        
        // 创建SSL上下文 - 模拟真实邮件客户端
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
            throw new Exception("连接失败: [$errno] $errstr");
        }
        
        // 设置超时
        stream_set_timeout($this->socket, $this->timeout);
        
        // 读取欢迎消息
        $response = $this->readResponse();
        echo "   服务器欢迎: $response\n";
        
        if (!$this->isResponseOK($response, '220')) {
            throw new Exception("服务器欢迎失败: $response");
        }
        
        echo "   ✅ SSL连接建立成功\n\n";
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
        
        echo "   ✅ EHLO成功\n\n";
        return $response;
    }
    
    public function authenticate($username, $password) {
        echo "🔐 开始身份认证...\n";
        echo "   用户名: $username\n";
        echo "   密码长度: " . strlen($password) . " 位\n";
        
        // AUTH LOGIN
        $this->sendCommand("AUTH LOGIN");
        $response = $this->readResponse();
        echo "   AUTH LOGIN响应: $response\n";
        
        if (!$this->isResponseOK($response, '334')) {
            throw new Exception("AUTH LOGIN失败: $response");
        }
        
        // 发送用户名（Base64编码）
        $encodedUser = base64_encode($username);
        echo "   发送用户名 (Base64): $encodedUser\n";
        $this->sendCommand($encodedUser);
        $response = $this->readResponse();
        echo "   用户名响应: $response\n";
        
        if (!$this->isResponseOK($response, '334')) {
            throw new Exception("用户名认证失败: $response");
        }
        
        // 发送密码（Base64编码）
        $encodedPass = base64_encode($password);
        echo "   发送密码 (Base64): " . substr($encodedPass, 0, 8) . "...\n";
        $this->sendCommand($encodedPass);
        $response = $this->readResponse();
        echo "   密码响应: $response\n";
        
        if (!$this->isResponseOK($response, '235')) {
            throw new Exception("密码认证失败，请检查授权码: $response");
        }
        
        echo "   ✅ 身份认证成功！\n\n";
        return true;
    }
    
    public function sendMail($from, $to, $subject, $body) {
        echo "📧 开始发送邮件...\n";
        echo "   从: $from\n";
        echo "   到: $to\n";
        echo "   主题: $subject\n\n";
        
        // MAIL FROM
        $this->sendCommand("MAIL FROM: <$from>");
        $response = $this->readResponse();
        echo "   MAIL FROM响应: $response\n";
        
        if (!$this->isResponseOK($response, '250')) {
            throw new Exception("MAIL FROM失败: $response");
        }
        
        // RCPT TO
        $this->sendCommand("RCPT TO: <$to>");
        $response = $this->readResponse();
        echo "   RCPT TO响应: $response\n";
        
        if (!$this->isResponseOK($response, '250')) {
            throw new Exception("RCPT TO失败: $response");
        }
        
        // DATA
        $this->sendCommand("DATA");
        $response = $this->readResponse();
        echo "   DATA响应: $response\n";
        
        if (!$this->isResponseOK($response, '354')) {
            throw new Exception("DATA失败: $response");
        }
        
        // 构建邮件头和正文
        $timestamp = date('r'); // RFC 2822 格式
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
        
        // 发送邮件内容
        fwrite($this->socket, $email);
        fflush($this->socket);
        
        $response = $this->readResponse();
        echo "   发送结果: $response\n";
        
        if (!$this->isResponseOK($response, '250')) {
            throw new Exception("邮件发送失败: $response");
        }
        
        // 提取Queue ID（如果有）
        if (preg_match('/Ok: queued as (.+)/', $response, $matches)) {
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

// 3. 执行测试
try {
    echo str_repeat("=", 50) . "\n";
    echo "🚀 开始精确SMTP测试\n";
    echo str_repeat("=", 50) . "\n";
    
    $smtp = new PreciseSMTPClient($host, $port);
    
    // 连接
    $smtp->connect();
    
    // EHLO
    $smtp->ehlo($_SERVER['HTTP_HOST'] ?? 'localhost');
    
    // 认证
    $smtp->authenticate($user, $pass);
    
    // 发送邮件
    $subject = "[SubAlert] 精确SMTP测试 - " . date('H:i:s');
    $body = '<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head><body>
<div style="font-family:Arial,sans-serif;max-width:600px;margin:20px auto;padding:20px;border:1px solid #ddd;border-radius:8px;">
    <h2 style="color:#10b981;">🎉 精确SMTP测试成功！</h2>
    <p>这封邮件是通过精确模拟邮件客户端的方式发送的。</p>
    
    <div style="background:#f0f9ff;border:1px solid #0ea5e9;padding:15px;margin:20px 0;border-radius:6px;">
        <h3>📊 技术详情</h3>
        <ul>
            <li><strong>发送时间:</strong> ' . date('Y-m-d H:i:s') . '</li>
            <li><strong>SMTP服务器:</strong> ' . $host . ':' . $port . '</li>
            <li><strong>发送账户:</strong> ' . $user . '</li>
            <li><strong>认证方式:</strong> AUTH LOGIN (客户端授权码)</li>
            <li><strong>连接方式:</strong> SSL直连</li>
            <li><strong>编码方式:</strong> UTF-8</li>
        </ul>
    </div>
    
    <div style="background:#fef3c7;border:1px solid #f59e0b;padding:15px;margin:20px 0;border-radius:6px;">
        <h3>✅ 测试结果</h3>
        <p><strong>如果您收到这封邮件，说明：</strong></p>
        <ul>
            <li>✅ SMTP服务器连接正常</li>
            <li>✅ SSL/TLS加密工作正常</li>
            <li>✅ 客户端授权码认证成功</li>
            <li>✅ 邮件发送功能完全正常</li>
            <li>✅ 可以正常使用密码重置功能</li>
        </ul>
    </div>
    
    <p><strong style="color:#10b981;">恭喜！您的SubAlert邮件系统配置完美！</strong></p>
    
    <hr>
    <p style="color:#666;font-size:12px;">
        此邮件由SubAlert精确SMTP客户端发送<br>
        如果您没有申请此测试，请忽略此邮件
    </p>
</div>
</body></html>';
    
    $smtp->sendMail($user, $testEmail, $subject, $body);
    
    // 关闭连接
    $smtp->quit();
    
    echo str_repeat("=", 50) . "\n";
    echo "🎉 测试完成 - 邮件发送成功！\n";
    echo str_repeat("=", 50) . "\n";
    echo "📬 请检查 $testEmail 的收件箱\n";
    echo "📁 也请检查垃圾邮件文件夹\n";
    echo "⏰ 邮件通常在1-3分钟内到达\n";
    echo "🎯 如果收到邮件，说明配置完全正确！\n\n";
    
} catch (Exception $e) {
    echo "\n❌ 测试失败: " . $e->getMessage() . "\n";
    echo "\n🔧 故障排除:\n";
    echo "   1. 检查客户端授权码是否正确\n";
    echo "   2. 确认企业邮箱SMTP服务已开启\n";
    echo "   3. 检查服务器防火墙设置\n";
    echo "   4. 验证邮箱账户状态\n";
}

echo "\n测试时间: " . date('Y-m-d H:i:s') . "\n";