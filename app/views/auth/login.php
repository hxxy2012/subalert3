<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="nav-brand mb-3">
                <i class="fas fa-bell"></i>
                SubAlert
            </div>
            <h1>用户登录</h1>
            <p>欢迎回来，请登录您的账户</p>
        </div>

        <form method="post" action="/?r=login">
            <div class="form-group">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope mr-2"></i>邮箱地址
                </label>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control"
                       placeholder="请输入您的邮箱地址"
                       required
                       autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock mr-2"></i>密码
                </label>
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control"
                       placeholder="请输入您的密码"
                       required
                       autocomplete="current-password">
            </div>

            <div class="form-check mb-4">
                <input type="checkbox" id="remember" name="remember" value="1">
                <label for="remember">记住我</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">
                <i class="fas fa-sign-in-alt"></i>
                登录
            </button>
        </form>

        <div class="text-center">
            <p class="text-muted mb-3">还没有账户？
                <a href="/?r=register" class="text-primary">立即注册</a>
            </p>
            <p class="text-muted">
                <a href="/?r=forgot-password" class="text-primary">
                    <i class="fas fa-question-circle"></i>
                    忘记密码？
                </a>
            </p>
        </div>
    </div>
</div>

<style>
/* 登录页面专用样式 */
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--gray-50);
    padding: 1rem;
}

.auth-card {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    padding: 3rem;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--gray-200);
    width: 100%;
    max-width: 400px;
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-header .nav-brand {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.auth-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 0.5rem;
}

.auth-header p {
    color: var(--gray-500);
    font-size: 0.875rem;
}

@media (max-width: 480px) {
    .auth-card {
        padding: 2rem 1.5rem;
        margin: 0.5rem;
    }

    .auth-header h1 {
        font-size: 1.5rem;
    }

    .auth-header .nav-brand {
        font-size: 1.75rem;
    }
}
</style>