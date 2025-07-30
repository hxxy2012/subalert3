<style>
/* 登录页面专用样式 - 覆盖全局样式 */
body {
    background: linear-gradient(135deg, var(--primary-light) 0%, var(--gray-50) 100%);
    min-height: 100vh;
}

.container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 125px); /* 减去导航栏高度 */
    padding: 2rem 1rem 1rem 1rem; /* 底部留小一点间距给footer */
}

/* 登录卡片 - 平衡的尺寸 */
.auth-card {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--gray-200);
    width: 100%;
    max-width: 450px;
    padding: 2.25rem;
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s ease-out;
}

.auth-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--primary-dark));
}

.auth-header {
    text-align: center;
    margin-bottom: 1.75rem;
}

.auth-brand {
    font-size: 1.875rem;
    font-weight: 700;
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.625rem;
    margin-bottom: 0.875rem;
}

.auth-brand i {
    background: var(--primary-color);
    color: white;
    width: 46px;
    height: 46px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.auth-header h1 {
    font-size: 1.625rem;
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: 0.4375rem;
}

.auth-header p {
    color: var(--gray-500);
    font-size: 0.875rem;
}

/* 表单样式 */
.auth-form .form-group {
    margin-bottom: 1.3125rem;
    position: relative;
}

.auth-form .form-label {
    display: block;
    margin-bottom: 0.4375rem;
    font-weight: 500;
    color: var(--gray-700);
    font-size: 0.875rem;
}

.auth-form .form-control {
    width: 100%;
    padding: 0.8125rem 1rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    font-size: 0.9375rem;
    transition: var(--transition);
    background-color: var(--white);
}

.auth-form .form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px var(--primary-light);
}

.auth-form .form-control::placeholder {
    color: var(--gray-400);
}

.auth-form .form-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.3125rem;
}

.auth-form .form-check input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: var(--primary-color);
}

.auth-form .form-check label {
    color: var(--gray-600);
    font-size: 0.8125rem;
}

/* 按钮样式 */
.auth-form .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.8125rem 1.375rem;
    font-weight: 500;
    border-radius: var(--border-radius);
    border: 1px solid transparent;
    cursor: pointer;
    text-decoration: none;
    transition: var(--transition);
    font-size: 0.9375rem;
    width: 100%;
}

.auth-form .btn-primary {
    background-color: var(--primary-color);
    color: var(--white);
}

.auth-form .btn-primary:hover {
    background-color: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
}

.auth-form .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* 链接区域 - 横向布局 */
.auth-links {
    margin-top: 1.3125rem;
}

.auth-links-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.auth-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.8125rem;
    transition: var(--transition);
    white-space: nowrap;
}

.auth-link:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}

/* 输入框聚焦效果 */
.auth-form .form-group.focused .form-control {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px var(--primary-light);
}

/* 进场动画 */
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

/* 响应式设计 */
@media (max-width: 768px) {
    .container {
        padding: 1.25rem 1rem 1rem 1rem;
        min-height: calc(100vh - 70px);
    }

    .auth-card {
        max-width: 400px;
        padding: 2rem;
    }

    .auth-brand {
        font-size: 1.75rem;
    }

    .auth-brand i {
        width: 42px;
        height: 42px;
    }

    .auth-header h1 {
        font-size: 1.5rem;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 1rem 0.75rem 0.75rem 0.75rem;
        min-height: calc(100vh - 110px); 
    }

    .auth-card {
        max-width: 360px;
        padding: 1.75rem;
    }

    .auth-brand {
        font-size: 1.625rem;
    }

    .auth-brand i {
        width: 38px;
        height: 38px;
    }

    .auth-header h1 {
        font-size: 1.375rem;
    }

    .auth-form .form-control {
        padding: 0.75rem 0.875rem;
    }

    .auth-form .btn {
        padding: 0.75rem 1.25rem;
    }
    
    .auth-links-row {
        flex-direction: column;
        gap: 0.75rem;
        text-align: center;
    }
}

@media (max-width: 360px) {
    .auth-card {
        max-width: 320px;
        padding: 1.5rem;
    }

    .auth-header {
        margin-bottom: 1.5rem;
    }

    .auth-form .form-group {
        margin-bottom: 1.125rem;
    }
}
</style>

<div class="auth-card">
    <div class="auth-header">
        <div class="auth-brand">
            <i class="fas fa-bell"></i>
            SubAlert
        </div>
        <h1>用户登录</h1>
        <p>欢迎回来，请登录您的账户</p>
    </div>

    <form method="post" action="/?r=login" class="auth-form" id="loginForm">
        <div class="form-group">
            <label for="email" class="form-label">
                <i class="fas fa-envelope"></i> 邮箱地址
            </label>
            <input type="email"
                   id="email"
                   name="email"
                   class="form-control"
                   placeholder="请输入您的邮箱地址"
                   required
                   autocomplete="email"
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="password" class="form-label">
                <i class="fas fa-lock"></i> 密码
            </label>
            <input type="password"
                   id="password"
                   name="password"
                   class="form-control"
                   placeholder="请输入您的密码"
                   required
                   autocomplete="current-password">
        </div>

        <div class="form-check">
            <input type="checkbox" 
                   id="remember" 
                   name="remember" 
                   value="1"
                   <?php echo !empty($_POST['remember']) ? 'checked' : ''; ?>>
            <label for="remember">记住我</label>
        </div>

        <button type="submit" class="btn btn-primary" id="loginBtn">
            <i class="fas fa-sign-in-alt"></i>
            登录
        </button>
    </form>

    <div class="auth-links">
        <div class="auth-links-row">
            <a href="/?r=register" class="auth-link">立即注册</a>
            <a href="/?r=forgot-password" class="auth-link">
                <i class="fas fa-question-circle"></i>
                忘记密码？
            </a>
        </div>
    </div>
</div>

<script>
// 表单交互增强
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const inputs = document.querySelectorAll('.auth-form .form-control');
    
    // 输入框焦点效果
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

    // 表单提交处理
    loginForm.addEventListener('submit', function(e) {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;

        if (!email || !password) {
            e.preventDefault();
            alert('请填写完整的登录信息');
            return;
        }

        if (!isValidEmail(email)) {
            e.preventDefault();
            alert('请输入有效的邮箱地址');
            document.getElementById('email').focus();
            return;
        }

        // 显示加载状态
        loginBtn.disabled = true;
        loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 登录中...';
    });

    // 自动聚焦第一个输入框
    const emailInput = document.getElementById('email');
    if (emailInput && !emailInput.value) {
        setTimeout(() => {
            emailInput.focus();
        }, 300);
    }
});

// 邮箱验证函数
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// 防止重复提交
let isSubmitting = false;
document.getElementById('loginForm').addEventListener('submit', function(e) {
    if (isSubmitting) {
        e.preventDefault();
        return false;
    }
    isSubmitting = true;
});
</script>