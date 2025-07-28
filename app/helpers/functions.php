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
 * @param string $type    Message type (success, error, warning)
 * @param string $message Message text
 */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Display flashed messages, then clear them.
 */
function display_flash(): void
{
    if (!empty($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $msg) {
            $class = htmlspecialchars($msg['type']);
            echo "<div class='alert {$class}'>" . htmlspecialchars($msg['message']) . "</div>";
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