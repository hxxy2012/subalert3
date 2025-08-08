<?php
// Autoload
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
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $path = str_replace('\\', '/', $relativeClass) . '.php';
    $file = $baseDir . $path;
    if (!file_exists($file)) {
        $pos = strrpos($path, '/');
        if ($pos !== false) {
            $dir = strtolower(substr($path, 0, $pos));
            $filename = substr($path, $pos + 1);
            $file = $baseDir . $dir . '/' . $filename;
        } else {
            $file = $baseDir . strtolower($path);
        }
    }
    if (file_exists($file)) {
        require $file;
    }
});
require __DIR__ . '/../app/helpers/functions.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set language based on ?lang= parameter
set_language_from_request();

// Determine route
$route = $_GET['r'] ?? 'login';

use App\Controllers\Admin\AdminAuthController;
use App\Controllers\Admin\AdminDashboardController;
use App\Controllers\Admin\AdminUserController;

// Admin-only access control
function require_admin_login(): void
{
    if (empty($_SESSION['admin'])) {
        redirect('/admin.php?r=login');
    }
}

// Check admin role(s). Accept single role string or array of roles.
function require_admin_role($roles): void
{
    require_admin_login();
    $admin   = $_SESSION['admin'] ?? [];
    $role    = $admin['role'] ?? '';
    $allowed = is_array($roles) ? $roles : [$roles];
    if (!in_array($role, $allowed)) {
        flash('error', '没有权限访问该功能');
        redirect('/admin.php?r=dashboard');
    }
}

switch ($route) {
    case 'login':
        (new AdminAuthController())->login();
        break;
    case 'logout':
        (new AdminAuthController())->logout();
        break;
    case 'dashboard':
        require_admin_login();
        (new AdminDashboardController())->index();
        break;
    case 'users':
        require_admin_role(['super', 'user_admin']);
        (new AdminUserController())->index();
        break;
    case 'user-edit':
        require_admin_role(['super', 'user_admin']);
        (new AdminUserController())->edit();
        break;
    case 'user-delete':
        require_admin_role(['super', 'user_admin']);
        (new AdminUserController())->delete();
        break;
    case 'user-batch':
        require_admin_role(['super', 'user_admin']);
        (new AdminUserController())->batchAction();
        break;
    case 'settings':
        require_admin_role(['super', 'system_admin']);
        (new \App\Controllers\Admin\AdminSettingsController())->index();
        break;
    case 'stats':
        require_admin_role(['super', 'analyst', 'user_admin', 'system_admin']);
        (new \App\Controllers\Admin\AdminStatsController())->index();
        break;
    case 'templates':
        require_admin_role(['super', 'system_admin']);
        (new \App\Controllers\Admin\AdminTemplateController())->index();
        break;
    case 'template-edit':
        require_admin_role(['super', 'system_admin']);
        (new \App\Controllers\Admin\AdminTemplateController())->edit();
        break;
    case 'template-preview':
        require_admin_role(['super', 'system_admin']);
        (new \App\Controllers\Admin\AdminTemplateController())->preview();
        break;
    case 'tasks':
        require_admin_role(['super', 'system_admin']);
        (new \App\Controllers\Admin\AdminTaskController())->index();
        break;
    case 'task-run':
        require_admin_role(['super', 'system_admin']);
        (new \App\Controllers\Admin\AdminTaskController())->run();
        break;
    case 'export-users':
        require_admin_role(['super', 'user_admin', 'analyst']);
        (new AdminUserController())->export();
        break;
    case 'logs':
        require_admin_role(['super', 'system_admin', 'analyst']);
        (new \App\Controllers\Admin\AdminLogController())->index();
        break;
    case 'backups':
        require_admin_role(['super', 'system_admin']);
        (new \App\Controllers\Admin\AdminBackupController())->index();
        break;
    case 'backup-create':
        require_admin_role(['super', 'system_admin']);
        (new \App\Controllers\Admin\AdminBackupController())->create();
        break;
    case 'export-subscriptions':
        require_admin_role(['super', 'user_admin', 'analyst', 'system_admin']);
        (new \App\Controllers\Admin\AdminExportController())->exportSubscriptions();
        break;
    case 'export-reminders':
        require_admin_role(['super', 'user_admin', 'analyst', 'system_admin']);
        (new \App\Controllers\Admin\AdminExportController())->exportReminders();
        break;
    default:
        redirect('/admin.php?r=login');
        break;
}
