<?php
// 增强的SMTP客户端 - 兼容Amazon SES和传统SMTP
// 文件：app/helpers/SMTPClient.php

namespace App\Helpers;

class SMTPClient {
    private $socket;
    private $host;
    private $port;
    private $username;
    private $password;
    private $timeout = 60;
    private $isAmazonSES;
    
    public function __construct($config) {
        $this->host = $config['host'];
        $this->port = intval($config['port']);
        $this->username = $config['user'];
        $this->password = $config['pass'];
        $this->isAmazonSES = strpos($this->host, 'amazonaws.com') !== false;
        
        // 调整超时时间
        $this->timeout = $this->isAmazonSES ? 60 : 30;
    }
    
    public function connect() {
        if ($this->isAmazonSES && $this->port == 587) {
            // Amazon SES 使用 STARTTLS
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
            throw new \Exception("SMTP连接失败: [$errno] $errstr");
        }
        
        stream_set_timeout($this->socket, $this->timeout);
        
        $response = $this->readResponse();
        if (!$this->isResponseOK($response, '220')) {
            throw new \Exception("SMTP服务器欢迎失败: $response");
        }
        
        return true;
    }
    
    public function ehlo($hostname = 'localhost') {
        $this->sendCommand("EHLO $hostname");
        $response = $this->readMultilineResponse();
        
        if (!$this->isResponseOK($response, '250')) {
            throw new \Exception("EHLO失败: $response");
        }
        
        // 如果是Amazon SES的587端口，需要升级到TLS
        if ($this->isAmazonSES && $this->port == 587 && strpos($response, 'STARTTLS') !== false) {
            $this->startTLS();
            
            // 升级后需要重新发送EHLO
            $this->sendCommand("EHLO $hostname");
            $response = $this->readMultilineResponse();
            
            if (!$this->isResponseOK($response, '250')) {
                throw new \Exception("TLS升级后EHLO失败: $response");
            }
        }
        
        return $response;
    }
    
    private function startTLS() {
        $this->sendCommand("STARTTLS");
        $response = $this->readResponse();
        
        if (!$this->isResponseOK($response, '220')) {
            throw new \Exception("STARTTLS失败: $response");
        }
        
        $result = stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        
        if (!$result) {
            throw new \Exception("TLS升级失败");
        }
    }
    
    public function authenticate() {
        $this->sendCommand("AUTH LOGIN");
        $response = $this->readResponse();
        
        if (!$this->isResponseOK($response, '334')) {
            throw new \Exception("AUTH LOGIN失败: $response");
        }
        
        $encodedUser = base64_encode($this->username);
        $this->sendCommand($encodedUser);
        $response = $this->readResponse();
        
        if (!$this->isResponseOK($response, '334')) {
            throw new \Exception("用户名认证失败: $response");
        }
        
        $encodedPass = base64_encode($this->password);
        $this->sendCommand($encodedPass);
        $response = $this->readResponse();
        
        if (!$this->isResponseOK($response, '235')) {
            $errorMsg = "密码认证失败: $response";
            if ($this->isAmazonSES) {
                $errorMsg = "Amazon SES认证失败，请检查SMTP凭证: $response";
            }
            throw new \Exception($errorMsg);
        }
        
        return true;
    }
    
    public function sendMail($from, $to, $subject, $body, $fromName = null) {
        // MAIL FROM - 使用正确的邮箱地址
        $this->sendCommand("MAIL FROM: <$from>");
        $response = $this->readResponse();
        
        if (!$this->isResponseOK($response, '250')) {
            $errorMsg = "MAIL FROM失败: $response";
            if ($this->isAmazonSES) {
                $errorMsg = "MAIL FROM失败 - 发件人邮箱 '$from' 可能未在Amazon SES中验证: $response";
            }
            throw new \Exception($errorMsg);
        }
        
        // RCPT TO
        $this->sendCommand("RCPT TO: <$to>");
        $response = $this->readResponse();
        
        if (!$this->isResponseOK($response, '250')) {
            $errorMsg = "RCPT TO失败: $response";
            if ($this->isAmazonSES && strpos($response, 'MessageRejected') !== false) {
                $errorMsg = "RCPT TO失败 - Amazon SES沙盒模式限制，请验证收件人邮箱或申请移出沙盒模式: $response";
            }
            throw new \Exception($errorMsg);
        }
        
        // DATA
        $this->sendCommand("DATA");
        $response = $this->readResponse();
        
        if (!$this->isResponseOK($response, '354')) {
            throw new \Exception("DATA失败: $response");
        }
        
        // 构建邮件
        $email = $this->buildEmailMessage($from, $to, $subject, $body, $fromName);
        
        // 发送邮件内容
        fwrite($this->socket, $email);
        fflush($this->socket);
        
        $response = $this->readResponse();
        
        if (!$this->isResponseOK($response, '250')) {
            throw new \Exception("邮件发送失败: $response");
        }
        
        return true;
    }
    
    private function buildEmailMessage($from, $to, $subject, $body, $fromName = null) {
        $timestamp = date('r');
        $messageId = '<' . uniqid('mail-', true) . '@' . 
                    ($this->isAmazonSES ? 'amazonses.com' : $this->host) . '>';
        
        // 构建发件人显示名称
        $fromDisplay = $fromName ? "$fromName <$from>" : $from;
        
        $email = "From: $fromDisplay\r\n";
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
        
        return $email;
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
        $response = fgets($this->socket, 1024);
        if ($response === false) {
            throw new \Exception("读取服务器响应失败");
        }
        return trim($response);
    }
    
    private function readMultilineResponse() {
        $response = '';
        while (true) {
            $line = fgets($this->socket, 1024);
            if ($line === false) break;
            
            $response .= $line;
            
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