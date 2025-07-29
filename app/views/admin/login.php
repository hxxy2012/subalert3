<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - SubAlert</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-login-container {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
        }

        .admin-login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .admin-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .admin-header .brand {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .admin-header .brand i {
            background: var(--primary-color);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .admin-header p {
            color: var(--gray-500);
            font-size: 0.9rem;
        }

        .security-notice {
            background: var(--primary-light);
            border: 1px solid #bfdbfe;
            border-radius: var(--border-radius);
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .security-notice i {
            color: var(--primary-color);
            margin-right: 0.5rem;
        }

        .login-form .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .login-form .form-control {
            padding-left: 3rem;
            height: 3rem;
            font-size: 0.95rem;
        }

        .login-form .form-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 1.1rem;
        }

        .login-form .form-control:focus + .form-icon {
            color: var(--primary-color);
        }

        .login-btn {
            width: 100%;
            height: 3rem;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .login-btn .spinner {
            display: none;
        }

        .login-btn.loading .spinner {
            display: inline-block;
            margin-right: 0.5rem;
        }

        .back-to-site {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--gray-200);
        }

        .back-to-site a {
            color: var(--gray-500);
            text-decoration: none;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .back-to-site a:hover {
            color: var(--primary-color);
        }

        @media (max-width: 480px) {
            .admin-login-container {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }

            .admin-header .brand {
                font-size: 1.5rem;
            }

            .admin-header .brand i {
                width: 40px;
                height: 40px;
            }

            .admin-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-header">
            <div class="brand">
                <i class="fas fa-shield-alt"></i>
                SubAlert
            </div>
            <h1>管理员登录</h1>
            <p>请使用管理员账户登录后台系统</p>
        </div>

        <div class="security-notice">
            <i class="fas fa-lock"></i>
            此页面为管理员专用，请确保环境安全
        </div>

        <?php if (!empty($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $msg): ?>
                <div class="alert alert-<?php echo $msg['type'] === 'error' ? 'error' : 'info'; ?>">
                    <i class="fas fa-<?php echo $msg['type'] === 'error' ? 'exclamation-triangle' : 'info-circle'; ?>"></i>
                    <?php echo htmlspecialchars($msg['message']); ?>
                </div>
            <?php endforeach; ?>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <form method="post" action="/admin.php?r=login" class="login-form" id="loginForm">
            <div class="form-group">
                <input type="text"
                       id="username"
                       name="username"
                       class="form-control"
                       placeholder="请输入管理员用户名"
                       required
                       autocomplete="username">
                <i class="fas fa-user form-icon"></i>
            </div>

            <div class="form-group">
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control"
                       placeholder="请输入管理员密码"
                       required
                       autocomplete="current-password">
                <i class="fas fa-lock form-icon"></i>
            </div>

            <button type="submit" class="btn btn-primary login-btn" id="loginBtn">
                <span class="spinner"><i class="fas fa-spinner fa-spin"></i></span>
                <span class="btn-text">登录管理后台</span>
            </button>
        </form>

        <div class="back-to-site">
            <a href="/">
                <i class="fas fa-arrow-left"></i>
                返回网站首页
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');

            // 表单提交处理
            form.addEventListener('submit', function(e) {
                const username = usernameInput.value.trim();
                const password = passwordInput.value;

                if (!username || !password) {
                    e.preventDefault();
                    showError('请填写完整的登录信息');
                    return;
                }

                // 显示加载状态
                loginBtn.classList.add('loading');
                loginBtn.disabled = true;

                // 模拟加载时间（实际项目中不需要）
                setTimeout(() => {
                    // 表单正常提交
                }, 100);
            });

            // 输入框焦点效果
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });

                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });

                // 初始化时检查是否有值
                if (input.value) {
                    input.parentElement.classList.add('focused');
                }
            });

            // 键盘快捷键
            document.addEventListener('keydown', function(e) {
                // Enter键快速提交
                if (e.key === 'Enter' && (document.activeElement === usernameInput || document.activeElement === passwordInput)) {
                    form.dispatchEvent(new Event('submit'));
                }

                // Escape键清空表单
                if (e.key === 'Escape') {
                    usernameInput.value = '';
                    passwordInput.value = '';
                    usernameInput.focus();
                }
            });

            // 错误提示函数
            function showError(message) {
                // 移除现有错误提示
                const existingError = document.querySelector('.temp-error');
                if (existingError) {
                    existingError.remove();
                }

                // 创建新的错误提示
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-error temp-error';
                errorDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i>
                    ${message}
                `;

                form.insertBefore(errorDiv, form.firstChild);

                // 3秒后自动移除
                setTimeout(() => {
                    errorDiv.remove();
                }, 3000);
            }

            // 自动聚焦用户名输入框
            usernameInput.focus();
        });

        // 页面加载动画
        window.addEventListener('load', function() {
            document.querySelector('.admin-login-container').style.animation = 'fadeInUp 0.6s ease-out';
        });

        // CSS动画定义
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .form-group.focused .form-control {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 3px var(--primary-light);
            }

            .form-group.focused .form-icon {
                color: var(--primary-color);
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>