<?php

/**
 * Render a view with the provided data.
 * This function includes the header and footer layout automatically.
 *
 * @param string $viewPath Relative path inside views directory (without .php)
 * @param array  $data     Variables to extract into the view
 */
function view(string $viewPath, array $data = []): void
{
    $basePath = __DIR__ . '/../views/';
    $viewFile = $basePath . $viewPath . '.php';
    if (!file_exists($viewFile)) {
        http_response_code(404);
        echo "View not found: {$viewPath}";
        return;
    }
    extract($data);
    include $basePath . 'layout/header.php';
    include $viewFile;
    include $basePath . 'layout/footer.php';
}

/**
 * Get the current language from session. Defaults to 'zh'.
 *
 * @return string Two-letter language code
 */
function current_lang(): string
{
    return $_SESSION['lang'] ?? 'zh';
}

/**
 * Load translation strings and return translation for a given key.
 * If translation is not available, return the key itself.
 *
 * Usage: echo __("dashboard");
 *
 * @param string $key
 * @return string
 */
function __(string $key): string
{
    static $translations;
    // Load translations once
    if ($translations === null) {
        $file = __DIR__ . '/../lang.php';
        if (file_exists($file)) {
            $translations = require $file;
        } else {
            $translations = [];
        }
    }
    $lang = current_lang();
    return $translations[$lang][$key] ?? $key;
}

/**
 * Update language based on ?lang= parameter in the request.
 * Should be called at the beginning of each request before output.
 */
function set_language_from_request(): void
{
    if (!empty($_GET['lang'])) {
        $lang = $_GET['lang'];
        // Accept only predefined languages
        if (in_array($lang, ['zh', 'en'])) {
            $_SESSION['lang'] = $lang;
        }
    }
}

/**
 * Redirect to a different URL.
 *
 * @param string $url
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Check if user is logged in. Returns user data from session or null.
 */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Require user to be authenticated. If not logged in, redirect to login page.
 */
function require_login(): void
{
    if (!current_user()) {
        redirect('/?r=login');
    }
}

/**
 * Flash a message to session to be displayed once.
 * 
 * @param string $type    Message type (success, error, warning, info)
 * @param string $message Message text (can contain HTML)
 * @param bool   $allowHtml Whether to allow HTML in the message (default: false)
 */
function flash(string $type, string $message, bool $allowHtml = false): void
{
    $_SESSION['flash'][] = [
        'type' => $type, 
        'message' => $message,
        'allow_html' => $allowHtml
    ];
}

/**
 * Display flashed messages, then clear them.
 * Enhanced version with better styling and HTML support.
 */
function display_flash(): void
{
    if (!empty($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $msg) {
            $type = htmlspecialchars($msg['type']);
            $allowHtml = $msg['allow_html'] ?? false;
            
            // 安全地处理消息内容
            if ($allowHtml) {
                // 如果允许HTML，则不进行转义，但需要确保内容来源可信
                $message = $msg['message'];
            } else {
                // 默认情况下转义HTML以防止XSS
                $message = htmlspecialchars($msg['message']);
            }
            
            // 根据类型选择图标
            $icons = [
                'success' => 'fas fa-check-circle',
                'error' => 'fas fa-exclamation-triangle', 
                'warning' => 'fas fa-exclamation-circle',
                'info' => 'fas fa-info-circle'
            ];
            $icon = $icons[$msg['type']] ?? 'fas fa-info-circle';
            
            // 输出增强的Flash消息
            echo "<div class='flash-message flash-{$type}' id='flash-" . uniqid() . "'>";
            echo "<div class='flash-content'>";
            echo "<i class='{$icon}'></i>";
            echo "<div class='flash-text'>{$message}</div>";
            echo "<button class='flash-close' onclick='this.parentElement.parentElement.remove()'>";
            echo "<i class='fas fa-times'></i>";
            echo "</button>";
            echo "</div>";
            echo "</div>";
        }
        unset($_SESSION['flash']);
    }
}

/**
 * Record an admin operation into admin_logs table.
 *
 * @param string $action      Short action identifier
 * @param string $description Detailed description of operation
 */
function log_admin_action(string $action, string $description): void
{
    if (isset($_SESSION['admin']['id'])) {
        $pdo = \App\Models\DB::getConnection();
        $stmt = $pdo->prepare('INSERT INTO admin_logs (admin_id, action, description, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$_SESSION['admin']['id'], $action, $description]);
    }
}

// 增强的邮件发送函数 - 添加到 app/helpers/functions.php

/**
 * 发送邮件的统一函数 - 兼容Amazon SES和传统SMTP
 * 
 * @param string $to 收件人邮箱
 * @param string $subject 邮件主题
 * @param string $body 邮件内容 (HTML)
 * @param array $smtpConfig SMTP配置
 * @param string $fromEmail 发件人邮箱 (对于Amazon SES必须是验证过的)
 * @param string $fromName 发件人显示名称
 * @return bool 发送成功返回true，失败返回false
 */
function sendEmailSMTP($to, $subject, $body, $smtpConfig, $fromEmail = null, $fromName = 'SubAlert') {
    try {
        // 确保包含SMTP客户端类
        if (!class_exists('\App\Helpers\SMTPClient')) {
            require_once __DIR__ . '/SMTPClient.php';
        }
        
        $smtp = new \App\Helpers\SMTPClient($smtpConfig);
        
        // 确定发件人邮箱
        $from = $fromEmail ?: $smtpConfig['user'];
        
        // 对于Amazon SES，检查是否需要使用特定的发件人邮箱
        $isAmazonSES = strpos($smtpConfig['host'], 'amazonaws.com') !== false;
        if ($isAmazonSES && !$fromEmail) {
            // 如果是Amazon SES但没有指定发件人邮箱，记录警告
            error_log("Amazon SES需要指定已验证的发件人邮箱地址");
        }
        
        $smtp->connect();
        $smtp->ehlo($_SERVER['HTTP_HOST'] ?? 'localhost');
        $smtp->authenticate();
        $smtp->sendMail($from, $to, $subject, $body, $fromName);
        $smtp->quit();
        
        return true;
    } catch (\Exception $e) {
        error_log("邮件发送失败: " . $e->getMessage());
        return false;
    }
}

/**
 * 获取系统默认的发件人邮箱
 * 根据SMTP配置自动判断合适的发件人地址
 */
function getDefaultFromEmail($smtpConfig) {
    $isAmazonSES = strpos($smtpConfig['host'], 'amazonaws.com') !== false;
    
    if ($isAmazonSES) {
        // 对于Amazon SES，尝试从配置中获取默认发件人邮箱
        // 可以在settings表中添加一个 'default_from_email' 配置项
        $pdo = \App\Models\DB::getConnection();
        $stmt = $pdo->prepare('SELECT value FROM settings WHERE `key` = ?');
        $stmt->execute(['default_from_email']);
        $result = $stmt->fetch();
        
        if ($result) {
            return $result['value'];
        }
        
        // 如果没有配置，返回一个基于域名的默认邮箱
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return "noreply@$domain";
    } else {
        // 对于传统SMTP，使用SMTP用户名
        return $smtpConfig['user'];
    }
}