<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="<?php echo current_lang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SubAlert - 智能订阅管理系统</title>

    <!-- 图标库 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- 主样式文件 -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- SEO Meta -->
    <meta name="description" content="SubAlert - 专业的订阅服务管理工具，智能提醒，费用统计">
    <meta name="keywords" content="订阅管理,费用管理,提醒服务,订阅统计">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="/" class="nav-brand">
                <i class="fas fa-bell"></i>
                SubAlert
            </a>

            <button class="nav-toggle" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>

            <ul class="nav-menu" id="navMenu">
                <?php if (current_user()): ?>
                    <li>
                        <a href="/?r=dashboard&amp;lang=<?php echo current_lang(); ?>"
                           class="<?php echo ($_GET['r'] ?? 'dashboard') === 'dashboard' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <?php echo __('dashboard'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="/?r=subscriptions&amp;lang=<?php echo current_lang(); ?>"
                           class="<?php echo ($_GET['r'] ?? '') === 'subscriptions' ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i>
                            <?php echo __('subscriptions'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="/?r=reminders&amp;lang=<?php echo current_lang(); ?>"
                           class="<?php echo ($_GET['r'] ?? '') === 'reminders' ? 'active' : ''; ?>">
                            <i class="fas fa-bell"></i>
                            <?php echo __('reminders'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="/?r=stats&amp;lang=<?php echo current_lang(); ?>"
                           class="<?php echo ($_GET['r'] ?? '') === 'stats' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-bar"></i>
                            <?php echo __('stats_analysis'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="/?r=settings&amp;lang=<?php echo current_lang(); ?>"
                           class="<?php echo ($_GET['r'] ?? '') === 'settings' ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i>
                            <?php echo __('preferences'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="/?r=profile&amp;lang=<?php echo current_lang(); ?>"
                           class="<?php echo ($_GET['r'] ?? '') === 'profile' ? 'active' : ''; ?>">
                            <i class="fas fa-user"></i>
                            <?php echo __('profile'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="/?r=logout&amp;lang=<?php echo current_lang(); ?>">
                            <i class="fas fa-sign-out-alt"></i>
                            <?php echo __('logout'); ?>
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="/?r=login&amp;lang=<?php echo current_lang(); ?>"
                           class="<?php echo ($_GET['r'] ?? '') === 'login' ? 'active' : ''; ?>">
                            <i class="fas fa-sign-in-alt"></i>
                            <?php echo __('login'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="/?r=register&amp;lang=<?php echo current_lang(); ?>"
                           class="<?php echo ($_GET['r'] ?? '') === 'register' ? 'active' : ''; ?>">
                            <i class="fas fa-user-plus"></i>
                            <?php echo __('register'); ?>
                        </a>
                    </li>
                <?php endif; ?>
                <li>
                    <?php if (current_lang() === 'zh'): ?>
                        <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>?<?php echo http_build_query(array_merge($_GET, ['lang' => 'en'])); ?>">
                            <i class="fas fa-globe"></i> EN
                        </a>
                    <?php else: ?>
                        <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>?<?php echo http_build_query(array_merge($_GET, ['lang' => 'zh'])); ?>">
                            <i class="fas fa-globe"></i> 中文
                        </a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <?php display_flash(); ?>

        <script>
            function toggleMobileMenu() {
                const navMenu = document.getElementById('navMenu');
                navMenu.classList.toggle('show');
            }

            // 点击外部关闭菜单
            document.addEventListener('click', function(event) {
                const navMenu = document.getElementById('navMenu');
                const navToggle = document.querySelector('.nav-toggle');

                if (!navMenu.contains(event.target) && !navToggle.contains(event.target)) {
                    navMenu.classList.remove('show');
                }
            });

            // 窗口大小改变时关闭移动菜单
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    document.getElementById('navMenu').classList.remove('show');
                }
            });
        </script>