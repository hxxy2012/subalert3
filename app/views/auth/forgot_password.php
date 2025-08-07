<style>
/* 忘记密码页面专用样式 - 覆盖全局样式 */
body {
    background: linear-gradient(135deg, var(--primary-light) 0%, var(--gray-50) 100%);
    min-height: 100vh;
}

.container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 125px);
    padding: 2rem 1rem 1rem 1rem;
}

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
    line-height: 1.5;
}

.info-notice {
    background: var(--primary-light);
    border: 1px solid #bfdbfe;
    border-radius: var(--border-radius);
    padding: 1rem;
    margin-bottom: 2rem;
    text-align: left;
    color: #1e40af;
}

.info-notice i {
    color: var(--primary-color);
    margin-right: 0.5rem;
}

.info-notice ul {
    margin: 0.5rem 0 0 0;
    padding-left: 1.5rem;
}

.info-notice li {
    margin-bottom: 0.25rem;
}

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
}

.auth-link:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}

.auth-form .form-group.focused .form-control {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px var(--primary-light);
}

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

/* 邮件发送成功状态样式 */
.email-success-info {
    background: var(--success-light);
    border: 1px solid #a7f3d0;
    color: #065f46;
    padding: 1rem;
    border-radius: var(--border-radius);
    margin-bottom: 1rem;
}

.email-success-info h4 {
    color: #047857;
    margin-bottom: 0.5rem;
}

.email-warning-info {
    background: var(--warning-light);
    border: 1px solid #fde68a;
    color: #92400e;
    padding: 1rem;
    border-radius: var(--border-radius);
    margin-bottom: 1rem;
}

.email-warning-info h4 {
    color: #d97706;
    margin-bottom: 0.5rem;
}

/* 输入状态样式 */
.form-group.has-error .form-control {
    border-color: var(--danger-color);
    box-shadow: 0 0 0 3px var(--danger-light);
}

.form-group.has-success .form-control {
    border-color: var(--success-color);
    box-shadow: 0 0 0 3px var(--success-light);
}

/* 滚动优化 - 确保页面顶部可见 */
.scroll-to-top {
    scroll-behavior: smooth;
}

/* Flash 消息正常文档流显示 */
.flash-message-container {
    margin-bottom: 1rem;
    /* 移除 sticky 定位，避免覆盖导航栏 */
}
</style>

<div class="auth-card">
    <!-- Flash 消息容器 - 固定在顶部 -->
    <div class="flash-message-container" id="flashContainer">
        <!-- Flash 消息将通过 JavaScript 插入这里 -->
    </div>

    <div class="auth-header">
        <div class="auth-brand">
            <i class="fas fa-unlock-alt"></i>
            SubAlert
        </div>
        <h1>找回密码</h1>
        <p>请输入您的注册邮箱地址<br>我们将发送密码重置链接到您的邮箱</p>
    </div>

    <div class="info-notice">
        <i class="fas fa-info-circle"></i>
        <strong>操作说明：</strong>
        <ul>
            <li>请确保输入正确的注册邮箱地址</li>
            <li>重置链接有效期为1小时</li>
            <li>如未收到邮件，请检查垃圾邮件文件夹</li>
            <li>每个邮箱地址30秒内只能申请一次</li>
            <li>重置链接将通过邮件发送，不会在页面显示</li>
        </ul>
    </div>

    <form method="post" action="/index.php?r=forgot-password" class="auth-form" id="forgotForm">
        <div class="form-group">
            <label for="email" class="form-label">
                <i class="fas fa-envelope"></i> 注册邮箱地址 <span class="text-danger">*</span>
            </label>
            <input type="email"
                   id="email"
                   name="email"
                   class="form-control"
                   placeholder="请输入您的注册邮箱地址"
                   required
                   autocomplete="email"
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            <small class="text-muted">请输入您注册账户时使用的邮箱地址</small>
        </div>

        <button type="submit" class="btn btn-primary" id="forgotBtn">
            <i class="fas fa-paper-plane"></i>
            发送重置邮件
        </button>
    </form>

    <div class="auth-links">
        <span class="text-muted">想起密码了？</span>
        <a href="/?r=login" class="auth-link">
            <i class="fas fa-sign-in-alt"></i>
            立即登录
        </a>
    </div>
</div>

<script>
// 页面滚动优化和 Flash 消息处理
document.addEventListener('DOMContentLoaded', function() {
    const forgotForm = document.getElementById('forgotForm');
    const forgotBtn = document.getElementById('forgotBtn');
    const emailInput = document.getElementById('email');
    const inputs = document.querySelectorAll('.auth-form .form-control');
    const flashContainer = document.getElementById('flashContainer');
    
    // 页面加载时立即滚动到顶部
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
    
    // 处理服务端 Flash 消息 - 移动到顶部容器并滚动到可视区域
    const existingFlashMessages = document.querySelectorAll('.flash-message');
    if (existingFlashMessages.length > 0) {
        existingFlashMessages.forEach(function(flashMsg) {
            // 克隆消息到顶部容器
            const clonedMsg = flashMsg.cloneNode(true);
            flashContainer.appendChild(clonedMsg);
            
            // 移除原来的消息
            flashMsg.remove();
        });
        
        // 确保 Flash 消息可见 - 滚动到卡片顶部而不是页面顶部
        setTimeout(function() {
            scrollToCard();
        }, 100);
    }
    
    // 输入框焦点效果
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
            this.parentElement.classList.remove('has-error');
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
            validateInput(this);
        });
        
        // 初始化时检查是否有值
        if (input.value) {
            input.parentElement.classList.add('focused');
        }
    });

    // 邮箱实时验证
    emailInput.addEventListener('input', function() {
        validateInput(this);
    });

    // 输入验证函数
    function validateInput(input) {
        const formGroup = input.parentElement;
        const small = formGroup.querySelector('small');
        
        if (input.type === 'email') {
            const email = input.value.trim();
            
            if (email && !isValidEmail(email)) {
                formGroup.classList.add('has-error');
                formGroup.classList.remove('has-success');
                small.textContent = '请输入有效的邮箱地址格式';
                small.className = 'text-danger';
            } else if (email && isValidEmail(email)) {
                formGroup.classList.remove('has-error');
                formGroup.classList.add('has-success');
                small.textContent = '邮箱格式正确';
                small.className = 'text-success';
            } else {
                formGroup.classList.remove('has-error', 'has-success');
                small.textContent = '请输入您注册账户时使用的邮箱地址';
                small.className = 'text-muted';
            }
        }
    }

    // 表单提交处理
    forgotForm.addEventListener('submit', function(e) {
        const email = emailInput.value.trim();

        if (!email) {
            e.preventDefault();
            showError('请输入邮箱地址');
            emailInput.focus();
            scrollToCard(); // 改为滚动到卡片位置
            return;
        }

        if (!isValidEmail(email)) {
            e.preventDefault();
            showError('请输入有效的邮箱地址');
            emailInput.focus();
            scrollToCard(); // 改为滚动到卡片位置
            return;
        }

        // 显示加载状态
        forgotBtn.disabled = true;
        forgotBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 发送中...';
        
        // 添加提示信息
        showInfo('正在发送邮件，请稍候...');
        scrollToCard(); // 改为滚动到卡片位置
    });

    // 自动聚焦邮箱输入框
    setTimeout(() => {
        if (!emailInput.value) {
            emailInput.focus();
        }
    }, 300);

    // 智能滚动函数 - 滚动到卡片顶部，避免覆盖导航栏
    function scrollToCard() {
        const authCard = document.querySelector('.auth-card');
        if (authCard) {
            // 计算导航栏高度，确保不被遮挡
            const navHeight = 80; // 导航栏大概高度
            const cardTop = authCard.getBoundingClientRect().top + window.pageYOffset;
            const targetPosition = Math.max(0, cardTop - navHeight - 20); // 额外留20px间距
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    }

    // 滚动到顶部函数 - 用于初始化
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // 错误提示函数
    function showError(message) {
        showMessage(message, 'error');
    }

    // 信息提示函数
    function showInfo(message) {
        showMessage(message, 'info');
    }

    // 通用消息显示函数
    function showMessage(message, type) {
        removeExistingAlerts();
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} temp-alert`;
        alertDiv.innerHTML = `
            <i class="fas fa-${getIconForType(type)}"></i>
            ${message}
        `;

        // 插入到顶部容器
        flashContainer.insertBefore(alertDiv, flashContainer.firstChild);
        
        // 滚动到卡片位置确保消息可见
        setTimeout(() => {
            scrollToCard();
        }, 50);
        
        // 5秒后自动移除（除非是持久消息）
        if (type !== 'success') {
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    }

    // 根据类型获取图标
    function getIconForType(type) {
        const icons = {
            'error': 'exclamation-triangle',
            'warning': 'exclamation-circle',
            'info': 'info-circle',
            'success': 'check-circle'
        };
        return icons[type] || 'info-circle';
    }

    // 移除现有提示
    function removeExistingAlerts() {
        const existingAlerts = flashContainer.querySelectorAll('.temp-alert');
        existingAlerts.forEach(alert => alert.remove());
    }

    // 防止重复提交
    let isSubmitting = false;
    forgotForm.addEventListener('submit', function(e) {
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }
        isSubmitting = true;
        
        // 30秒后允许重新提交（与后端频率限制保持一致）
        setTimeout(() => {
            isSubmitting = false;
            if (forgotBtn.disabled) {
                forgotBtn.disabled = false;
                forgotBtn.innerHTML = '<i class="fas fa-paper-plane"></i> 发送重置邮件';
            }
        }, 30000);
    });

    // 页面刷新/离开时恢复按钮状态
    window.addEventListener('beforeunload', function() {
        if (forgotBtn.disabled) {
            forgotBtn.disabled = false;
            forgotBtn.innerHTML = '<i class="fas fa-paper-plane"></i> 发送重置邮件';
        }
    });

    // 处理浏览器后退按钮
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            scrollToTop();
            if (forgotBtn.disabled) {
                forgotBtn.disabled = false;
                forgotBtn.innerHTML = '<i class="fas fa-paper-plane"></i> 发送重置邮件';
            }
        }
    });

    // 监听窗口大小变化，确保布局正确
    window.addEventListener('resize', function() {
        // 延迟滚动，等待布局调整完成
        setTimeout(() => {
            if (flashContainer.children.length > 0) {
                scrollToCard(); // 改为滚动到卡片位置
            }
        }, 200);
    });
});

// 邮箱验证函数
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// 页面加载动画
window.addEventListener('load', function() {
    document.querySelector('.auth-card').style.animation = 'fadeInUp 0.6s ease-out';
    
    // 确保页面在顶部
    setTimeout(() => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }, 100);
});

// 键盘快捷键支持
document.addEventListener('keydown', function(e) {
    // Escape键返回登录页
    if (e.key === 'Escape') {
        window.location.href = '/?r=login';
    }
    
    // Ctrl+Home 或 Home 键快速回到卡片顶部
    if (e.key === 'Home' || (e.ctrlKey && e.key === 'Home')) {
        e.preventDefault();
        scrollToCard(); // 改为滚动到卡片位置
    }
});

// 增强样式
const style = document.createElement('style');
style.textContent = `
    .alert {
        padding: 1rem;
        border-radius: var(--border-radius);
        margin-bottom: 1rem;
        border: 1px solid;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.9rem;
        animation: slideInDown 0.3s ease-out;
    }
    
    .alert-danger {
        background-color: var(--danger-light);
        border-color: #fecaca;
        color: #991b1b;
    }
    
    .alert-info {
        background-color: var(--primary-light);
        border-color: #bfdbfe;
        color: #1e40af;
    }
    
    .alert-success {
        background-color: var(--success-light);
        border-color: #a7f3d0;
        color: #065f46;
    }
    
    .temp-alert {
        animation: slideInDown 0.3s ease-out;
    }
    
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* 确保页面滚动平滑 */
    html {
        scroll-behavior: smooth;
    }
    
    /* Flash 消息容器样式优化 */
    .flash-message-container {
        min-height: 0;
        transition: all 0.3s ease;
        /* 正常文档流，不覆盖导航栏 */
    }
    
    .flash-message-container:empty {
        display: none;
    }
    
    /* 登录链接特殊样式 */
    .login-link {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background: var(--success-color);
        color: white !important;
        text-decoration: none !important;
        border-radius: var(--border-radius);
        font-weight: 600;
        margin: 0.5rem 0;
        transition: var(--transition);
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
    }
    
    .login-link:hover {
        background: #059669;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(16, 185, 129, 0.4);
        color: white !important;
    }
`;
document.head.appendChild(style);
</script>