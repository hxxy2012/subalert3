<?php
/**
 * SubAlert API 入口文件
 * 处理所有的API请求路由
 */

// 错误报告设置
error_reporting(E_ALL);
ini_set('display_errors', 0); // 生产环境设为0

// 自动加载类
spl_autoload_register(function ($class) {
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

// 加载辅助函数
require __DIR__ . '/../app/helpers/functions.php';

// 设置响应头
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 获取请求路径和方法
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// 移除查询参数
$path = parse_url($requestUri, PHP_URL_PATH);

// 移除 /api 前缀
$path = preg_replace('#^/api#', '', $path);
$path = trim($path, '/');

// 解析路由
$segments = explode('/', $path);
$module = $segments[0] ?? '';
$action = $segments[1] ?? '';
$id = $segments[2] ?? '';

try {
    // 路由分发
    switch ($module) {
        case 'auth':
            $controller = new \App\Controllers\Api\AuthController();
            
            switch ($action) {
                case 'register':
                    if ($requestMethod === 'POST') {
                        $controller->register();
                    } else {
                        throw new Exception('Method not allowed', 405);
                    }
                    break;
                    
                case 'login':
                    if ($requestMethod === 'POST') {
                        $controller->login();
                    } else {
                        throw new Exception('Method not allowed', 405);
                    }
                    break;
                    
                case 'logout':
                    if ($requestMethod === 'POST') {
                        $controller->logout();
                    } else {
                        throw new Exception('Method not allowed', 405);
                    }
                    break;
                    
                case 'forgot-password':
                    if ($requestMethod === 'POST') {
                        $controller->forgotPassword();
                    } else {
                        throw new Exception('Method not allowed', 405);
                    }
                    break;
                    
                case 'reset-password':
                    if ($requestMethod === 'POST') {
                        $controller->resetPassword();
                    } else {
                        throw new Exception('Method not allowed', 405);
                    }
                    break;
                    
                case 'verify':
                    if ($requestMethod === 'GET') {
                        $controller->verify();
                    } else {
                        throw new Exception('Method not allowed', 405);
                    }
                    break;
                    
                case 'refresh':
                    if ($requestMethod === 'POST') {
                        $controller->refresh();
                    } else {
                        throw new Exception('Method not allowed', 405);
                    }
                    break;
                    
                case 'user':
                    if ($requestMethod === 'GET') {
                        $controller->user();
                    } else {
                        throw new Exception('Method not allowed', 405);
                    }
                    break;
                    
                default:
                    throw new Exception('API endpoint not found', 404);
            }
            break;
            
        // 其他模块后续添加
        case 'subscriptions':
            // TODO: 实现订阅管理API
            throw new Exception('Subscriptions API not implemented yet', 501);
            break;
            
        case 'reminders':
            // TODO: 实现提醒管理API
            throw new Exception('Reminders API not implemented yet', 501);
            break;
            
        case 'user':
            // TODO: 实现用户管理API
            throw new Exception('User API not implemented yet', 501);
            break;
            
        case 'dashboard':
            // TODO: 实现仪表盘API
            throw new Exception('Dashboard API not implemented yet', 501);
            break;
            
        case '':
            // API根路径，返回API信息
            http_response_code(200);
            echo json_encode([
                'name' => 'SubAlert API',
                'version' => '1.0.0',
                'timestamp' => date('c'),
                'endpoints' => [
                    'auth' => [
                        'POST /api/auth/register' => '用户注册',
                        'POST /api/auth/login' => '用户登录',
                        'POST /api/auth/logout' => '用户登出',
                        'POST /api/auth/forgot-password' => '忘记密码',
                        'POST /api/auth/reset-password' => '重置密码',
                        'GET /api/auth/verify' => '验证Token',
                        'POST /api/auth/refresh' => '刷新Token',
                        'GET /api/auth/user' => '获取当前用户信息'
                    ]
                ]
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            throw new Exception('API module not found', 404);
    }
    
} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    
    echo json_encode([
        'code' => $code,
        'message' => $e->getMessage(),
        'data' => null,
        'timestamp' => date('c'),
        'success' => false
    ], JSON_UNESCAPED_UNICODE);
}