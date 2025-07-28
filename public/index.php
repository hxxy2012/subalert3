<?php
// Autoload classes using PSR-4 simple loader
spl_autoload_register(function ($class) {
    // Basic PSR‑4 autoload implementation for our App namespace. Because the
    // project structure uses lowercase folder names (e.g. app/controllers) on
    // case‑sensitive file systems, we attempt both the natural PSR‑4 mapping
    // and a lowercase variant when locating class files. This prevents
    // "Class ... not found" errors when the namespace's directory casing
    // doesn't match the actual filesystem casing.
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    // Only handle classes in our App namespace
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    // Convert namespace separators to directory separators
    $path = str_replace('\\', '/', $relativeClass) . '.php';
    $file = $baseDir . $path;
    if (!file_exists($file)) {
        // If the file doesn't exist (likely due to directory casing), attempt to
        // lowercase only the directory portion of the path while preserving the
        // original filename casing. This allows mapping namespaces like
        // `App\Models\DB` to `app/models/DB.php` when the directory names are
        // lowercase but file names use a particular casing.
        $pos = strrpos($path, '/');
        if ($pos !== false) {
            $dir = strtolower(substr($path, 0, $pos));
            $filename = substr($path, $pos + 1);
            $file = $baseDir . $dir . '/' . $filename;
        } else {
            // No directory separators, fallback to lowercase entire path
            $file = $baseDir . strtolower($path);
        }
    }
    if (file_exists($file)) {
        require $file;
    }
});

require __DIR__ . '/../app/helpers/functions.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set language from request if provided
set_language_from_request();

// Get route
$route = $_GET['r'] ?? 'home';

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\SubscriptionController;
use App\Controllers\ReminderController;
use App\Controllers\StatisticsController;
use App\Controllers\SettingsController;
use App\Controllers\ProfileController;
use App\Controllers\PasswordController;
use App\Controllers\ForgotPasswordController;

switch ($route) {
    case 'register':
        (new AuthController())->register();
        break;
    case 'login':
        (new AuthController())->login();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;
    case 'dashboard':
        require_login();
        (new DashboardController())->index();
        break;
    case 'subscriptions':
        require_login();
        (new SubscriptionController())->index();
        break;
    case 'subscription-create':
        require_login();
        (new SubscriptionController())->create();
        break;
    case 'subscription-edit':
        require_login();
        (new SubscriptionController())->edit();
        break;
    case 'subscription-delete':
        require_login();
        (new SubscriptionController())->delete();
        break;
    case 'subscriptions-deleted':
        require_login();
        (new SubscriptionController())->deleted();
        break;
    case 'reminders':
        require_login();
        (new \App\Controllers\ReminderController())->index();
        break;
    case 'reminder-create':
        require_login();
        (new \App\Controllers\ReminderController())->create();
        break;
    case 'reminder-action':
        require_login();
        (new \App\Controllers\ReminderController())->action();
        break;
    case 'stats':
        require_login();
        (new \App\Controllers\StatisticsController())->index();
        break;
    case 'stats-export':
        require_login();
        (new \App\Controllers\StatisticsController())->export();
        break;
    case 'settings':
        require_login();
        (new \App\Controllers\SettingsController())->index();
        break;
    case 'profile':
        require_login();
        (new \App\Controllers\ProfileController())->index();
        break;
    case 'change-password':
        require_login();
        (new \App\Controllers\PasswordController())->index();
        break;
    case 'forgot-password':
        (new \App\Controllers\ForgotPasswordController())->forgot();
        break;
    case 'reset-password':
        (new \App\Controllers\ForgotPasswordController())->reset();
        break;
    case 'delete-account':
        require_login();
        (new \App\Controllers\ProfileController())->deleteAccount();
        break;
    case 'home':
    default:
        if (current_user()) {
            redirect('/?r=dashboard');
        } else {
            redirect('/?r=login');
        }
}
