<?php
namespace App\Controllers\Api;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\DB;

/**
 * API基础控制器
 * 提供统一的API响应格式和JWT认证功能
 */
class BaseApiController
{
    protected $jwtSecret = 'subalert_jwt_secret_key_2025'; // 应该从配置文件读取
    
    /**
     * 获取JSON输入数据
     */
    protected function getJsonInput()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON format');
        }
        
        return $data ?? [];
    }
    
    /**
     * 验证必填字段
     */
    protected function validateRequired($data, $fields)
    {
        $missing = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            throw new \Exception('缺少必填字段: ' . implode(', ', $missing));
        }
    }
    
    /**
     * 成功响应
     */
    protected function success($data = null, $message = 'success', $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        echo json_encode([
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c'),
            'success' => true
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * 错误响应
     */
    protected function error($message, $code = 400, $errors = null)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        echo json_encode([
            'code' => $code,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
            'timestamp' => date('c'),
            'success' => false
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * 生成JWT Token
     */
    protected function generateJWT($payload)
    {
        $payload['iat'] = time(); // 签发时间
        $payload['iss'] = 'SubAlert'; // 签发者
        
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
    
    /**
     * 验证JWT Token
     */
    protected function verifyJWT($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * 获取Bearer Token
     */
    protected function getBearerToken()
    {
        $headers = getallheaders();
        if (!$headers) {
            $headers = [];
        }
        
        // 尝试多种方式获取Authorization头
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (empty($authHeader)) {
            // 尝试从 HTTP_AUTHORIZATION 获取
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        }
        
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }
    
    /**
     * 获取当前用户
     */
    protected function getCurrentUser()
    {
        $token = $this->getBearerToken();
        
        if (!$token) {
            return null;
        }
        
        $payload = $this->verifyJWT($token);
        
        if (!$payload || !isset($payload['user_id'])) {
            return null;
        }
        
        // 检查token是否过期
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }
        
        // 从数据库获取用户信息
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND status = ?');
        $stmt->execute([$payload['user_id'], 'normal']);
        $user = $stmt->fetch();
        
        if ($user) {
            unset($user['password']);
        }
        
        return $user;
    }
    
    /**
     * 需要登录验证的方法装饰器
     */
    protected function requireAuth()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            $this->error('请先登录', 401);
        }
        return $user;
    }
    
    /**
     * 发送邮件的辅助方法
     */
    protected function sendEmail($to, $subject, $body, $isHtml = true)
    {
        try {
            // 获取SMTP配置
            $pdo = DB::getConnection();
            $stmt = $pdo->query('SELECT `key`, `value` FROM settings WHERE `key` IN ("smtp_host", "smtp_port", "smtp_user", "smtp_pass", "site_name")');
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['key']] = $row['value'];
            }
            
            // 检查SMTP配置
            if (empty($settings['smtp_host']) || empty($settings['smtp_user']) || empty($settings['smtp_pass'])) {
                throw new \Exception('SMTP配置不完整');
            }
            
            $smtpConfig = [
                'host' => $settings['smtp_host'],
                'port' => intval($settings['smtp_port'] ?? '465'),
                'user' => $settings['smtp_user'],
                'pass' => $settings['smtp_pass'],
            ];
            
            // 创建SMTP客户端并发送邮件
            $smtp = $this->createSMTPClient($smtpConfig);
            $smtp->connect();
            $smtp->ehlo();
            $smtp->authenticate();
            $smtp->sendMail($settings['smtp_user'], $to, $subject, $body);
            $smtp->quit();
            
            return true;
            
        } catch (\Exception $e) {
            error_log('发送邮件失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 创建SMTP客户端
     */
    private function createSMTPClient($smtpConfig)
    {
        return new class($smtpConfig) {
            private $socket;
            private $host;
            private $port;
            private $username;
            private $password;
            private $timeout = 30;
            
            public function __construct($config) {
                $this->host = $config['host'];
                $this->port = $config['port'];
                $this->username = $config['user'];
                $this->password = $config['pass'];
            }
            
            public function connect() {
                $context = stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ]
                ]);
                
                $this->socket = @stream_socket_client(
                    "ssl://{$this->host}:{$this->port}",
                    $errno, $errstr, $this->timeout,
                    STREAM_CLIENT_CONNECT, $context
                );
                
                if (!$this->socket) {
                    throw new \Exception("SMTP连接失败: [$errno] $errstr");
                }
                
                stream_set_timeout($this->socket, $this->timeout);
                
                $response = $this->readResponse();
                if (!$this->isResponseOK($response, '220')) {
                    throw new \Exception("SMTP服务器连接失败: $response");
                }
            }
            
            public function ehlo($hostname = 'localhost') {
                $this->sendCommand("EHLO $hostname");
                $response = $this->readResponse();
                
                if (!$this->isResponseOK($response, '250')) {
                    throw new \Exception("EHLO失败: $response");
                }
            }
            
            public function authenticate() {
                $this->sendCommand("AUTH LOGIN");
                $response = $this->readResponse();
                
                if (!$this->isResponseOK($response, '334')) {
                    throw new \Exception("AUTH LOGIN失败: $response");
                }
                
                $this->sendCommand(base64_encode($this->username));
                $response = $this->readResponse();
                
                if (!$this->isResponseOK($response, '334')) {
                    throw new \Exception("用户名认证失败: $response");
                }
                
                $this->sendCommand(base64_encode($this->password));
                $response = $this->readResponse();
                
                if (!$this->isResponseOK($response, '235')) {
                    throw new \Exception("密码认证失败: $response");
                }
            }
            
            public function sendMail($from, $to, $subject, $body) {
                $this->sendCommand("MAIL FROM: <$from>");
                $response = $this->readResponse();
                
                if (!$this->isResponseOK($response, '250')) {
                    throw new \Exception("MAIL FROM失败: $response");
                }
                
                $this->sendCommand("RCPT TO: <$to>");
                $response = $this->readResponse();
                
                if (!$this->isResponseOK($response, '250')) {
                    throw new \Exception("RCPT TO失败: $response");
                }
                
                $this->sendCommand("DATA");
                $response = $this->readResponse();
                
                if (!$this->isResponseOK($response, '354')) {
                    throw new \Exception("DATA失败: $response");
                }
                
                $headers = "From: $from\r\n";
                $headers .= "To: $to\r\n";
                $headers .= "Subject: $subject\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                $headers .= "Content-Transfer-Encoding: 8bit\r\n";
                $headers .= "\r\n";
                
                $this->sendCommand($headers . $body . "\r\n.");
                $response = $this->readResponse();
                
                if (!$this->isResponseOK($response, '250')) {
                    throw new \Exception("邮件发送失败: $response");
                }
            }
            
            public function quit() {
                if ($this->socket) {
                    $this->sendCommand("QUIT");
                    fclose($this->socket);
                }
            }
            
            private function sendCommand($command) {
                fwrite($this->socket, $command . "\r\n");
            }
            
            private function readResponse() {
                return trim(fgets($this->socket, 512));
            }
            
            private function isResponseOK($response, $expectedCode) {
                return strpos($response, $expectedCode) === 0;
            }
        };
    }
}