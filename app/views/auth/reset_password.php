<style>
/* 重置密码页面专用样式 - 覆盖全局样式 */
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

/* 重置密码卡片 - 与登录页面保持一致 */
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

/* 状态提示 */
.status-notice {
    background: var(--danger-light);
    border: 1px solid #fecaca;
    border-radius: var(--border-radius);
    padding: 1rem;
    margin-bottom: 2rem;
    text-align: center;
    color: #991b1b;
}

.status-notice.success {
    background: var(--success-light);
    border-color: #a7f3d0;
    color: #065f46;
}

.status-notice i {
    margin-right: 0.5rem;
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

/* 链接区域 */
.auth-links {
    margin-top: 1.3125rem;
    text-align: center;
}

.auth-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.8125rem;
    transition: var(--transition);
    margin: 0 0.5rem;
}

.auth-link:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}

/* 密码强度提示 */
.password-strength {
    margin-top: 0.5rem;
    font-size: 0.75rem;
}

.strength-weak { color: var(--danger-color); }
.strength-medium { color: var(--warning-color); }
.strength-strong { color: var(--success-color); }

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
            <i class="fas fa-key"></i>
            SubAlert
        </div>
        <h1>重设密码</h1>
        <p>为您的账户设置新的登录密码</p>
    </div>

    <?php if (!isset($token) || !$token): ?>
        <div class="status-notice">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>链接无效</strong>
            <p class="mb-0 mt-2">重置链接无效或已过期，请重新申请密码重置</p>
        </div>
        
        <div class="auth-links">
            <a href="/?r=forgot-password" class="auth-link">
                <i class="fas fa-arrow-left"></i>
                重新申请重置密码
            </a>
            <span class="mx-2">|</span>
            <a href="/?r=login" class="auth-link">
                <i class="fas fa-sign-in-alt"></i>
                返回登录
            </a>
        </div>
    <?php else: ?>
        <form method="post" action="/index.php?r=reset-password&token=<?php echo htmlspecialchars($token); ?>" class="auth-form" id="resetForm">
            <div class="form-group">
                <label for="new_password" class="form-label">
                    <i class="fas fa-lock"></i> 新密码 <span class="text-danger">*</span>
                </label>
                <input type="password"
                       id="new_password"
                       name="new_password"
                       class="form-control"
                       placeholder="请输入新密码（至少8位）"
                       required
                       minlength="8"
                       autocomplete="new-password">
                <div id="passwordStrength" class="password-strength"></div>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="form-label">
                    <i class="fas fa-lock"></i> 确认新密码 <span class="text-danger">*</span>
                </label>
                <input type="password"
                       id="confirm_password"
                       name="confirm_password"
                       class="form-control"
                       placeholder="请再次输入新密码"
                       required
                       autocomplete="new-password">
                <small id="confirmHint" class="text-muted">请确保两次输入的密码一致</small>
            </div>

            <button type="submit" class="btn btn-primary" id="resetBtn">
                <i class="fas fa-check"></i>
                重设密码
            </button>
        </form>

        <div class="auth-links">
            <a href="/?r=login" class="auth-link">
                <i class="fas fa-arrow-left"></i>
                返回登录页面
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
// 表单交互增强
document.addEventListener('DOMContentLoaded', function() {
    const resetForm = document.getElementById('resetForm');
    if (!resetForm) return; // 如果没有表单则退出
    
    const resetBtn = document.getElementById('resetBtn');
    const inputs = document.querySelectorAll('.auth-form .form-control');
    const passwordInput = document.getElementById('new_password');
    const confirmInput = document.getElementById('confirm_password');
    
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

    // 密码强度检查
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strengthDiv = document.getElementById('passwordStrength');
        
        if (password.length === 0) {
            strengthDiv.textContent = '';
            return;
        }
        
        let strength = 0;
        let tips = [];
        
        if (password.length >= 8) strength++;
        else tips.push('至少8位');
        
        if (/[a-z]/.test(password)) strength++;
        else tips.push('包含小写字母');
        
        if (/[A-Z]/.test(password)) strength++;
        else tips.push('包含大写字母');
        
        if (/[0-9]/.test(password)) strength++;
        else tips.push('包含数字');
        
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        else tips.push('包含特殊字符');
        
        if (strength <= 2) {
            strengthDiv.className = 'password-strength strength-weak';
            strengthDiv.textContent = '密码强度：弱 (建议' + tips.slice(0, 2).join('、') + ')';
        } else if (strength <= 3) {
            strengthDiv.className = 'password-strength strength-medium';
            strengthDiv.textContent = '密码强度：中等';
        } else {
            strengthDiv.className = 'password-strength strength-strong';
            strengthDiv.textContent = '密码强度：强';
        }
        
        // 触发确认密码检查
        checkPasswordMatch();
    });

    // 确认密码检查
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        const hint = document.getElementById('confirmHint');
        
        if (confirm.length === 0) {
            hint.textContent = '请确保两次输入的密码一致';
            hint.className = 'text-muted';
            return;
        }
        
        if (password === confirm) {
            hint.textContent = '密码确认一致';
            hint.className = 'text-success';
        } else {
            hint.textContent = '两次输入的密码不一致';
            hint.className = 'text-danger';
        }
    }

    confirmInput.addEventListener('input', checkPasswordMatch);

    // 表单提交处理
    resetForm.addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const confirm = confirmInput.value;

        // 密码验证
        if (password.length < 8) {
            e.preventDefault();
            showError('密码长度至少8位');
            passwordInput.focus();
            return;
        }

        // 密码确认验证
        if (password !== confirm) {
            e.preventDefault();
            showError('两次输入的密码不一致');
            confirmInput.focus();
            return;
        }

        // 显示加载状态
        resetBtn.disabled = true;
        resetBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 重设中...';
    });

    // 自动聚焦第一个输入框
    setTimeout(() => {
        passwordInput.focus();
    }, 300);

    // 错误提示函数
    function showError(message) {
        // 移除现有错误提示
        const existingError = document.querySelector('.temp-error');
        if (existingError) {
            existingError.remove();
        }

        // 创建新的错误提示
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger temp-error';
        errorDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            ${message}
        `;

        resetForm.insertBefore(errorDiv, resetForm.firstChild);

        // 3秒后自动移除
        setTimeout(() => {
            errorDiv.remove();
        }, 3000);
    }

    // 防止重复提交
    let isSubmitting = false;
    resetForm.addEventListener('submit', function(e) {
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }
        isSubmitting = true;
    });
});

// 页面加载动画
window.addEventListener('load', function() {
    document.querySelector('.auth-card').style.animation = 'fadeInUp 0.6s ease-out';
});

// 键盘快捷键支持
document.addEventListener('keydown', function(e) {
    // Escape键返回登录页
    if (e.key === 'Escape') {
        window.location.href = '/?r=login';
    }
});

// CSS动画定义
const style = document.createElement('style');
style.textContent = `
    .alert-danger {
        background-color: var(--danger-light);
        border: 1px solid #fecaca;
        color: #991b1b;
        padding: 1rem;
        border-radius: var(--border-radius);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
`;
document.head.appendChild(style);
</script>