<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SubAlert 管理后台</title>

    <!-- 图标库 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- 主样式文件 -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- 管理后台特定样式 -->
    <style>
        .admin-navbar {
            background: var(--primary-color);
            border-bottom: none;
        }

        .admin-navbar .nav-brand {
            color: var(--white);
        }

        .admin-navbar .nav-menu a {
            color: rgba(255, 255, 255, 0.8);
        }

        .admin-navbar .nav-menu a:hover,
        .admin-navbar .nav-menu a.active {
            color: var(--white);
            background-color: rgba(255, 255, 255, 0.1);
        }

        .admin-navbar .nav-toggle {
            color: var(--white);
        }

        .admin-navbar .nav-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .admin-status {
            background: var(--success-color);
            color: var(--white);
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <nav class="navbar admin-navbar">
        <div class="nav-container">
            <a href="/admin.php?r=dashboard" class="nav-brand">
                <i class="fas fa-shield-alt"></i>
                SubAlert 管理后台
            </a>

            <button class="nav-toggle" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>

            <ul class="nav-menu" id="navMenu">
                <?php if (!empty($_SESSION['admin'])): ?>
                    <li>
                        <a href="/admin.php?r=dashboard&amp;lang=<?php echo current_lang(); ?>"
                           class="<?php echo ($_GET['r'] ?? 'dashboard') === 'dashboard' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <?php echo __('dashboard'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="/admin.php?r=users&amp;lang=<?php echo current_lang(); ?>"
                           class="<?php echo ($_GET['r'] ?? '') === 'users' ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i>
                            <?php echo __('users'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="/admin.php?r=stats&amp;lang=<?php echo current_lang(); ?>"
                           class="<?php echo ($_GET['r'] ?? '') === 'stats' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-bar"></i>
                            <?php echo __('stats'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="/admin.php?r=settings&amp;lang=<?php echo current_lang(); ?>"
                           class="<?php echo ($_GET['r'] ?? '') === 'settings' ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i>
                            <?php echo __('settings'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="/admin.php?r=templates&amp;lang=<?php echo current_lang(); ?>"
                           class="<?php echo ($_GET['r'] ?? '') === 'templates' ? 'active' : ''; ?>">
                            <i class="fas fa-file-alt"></i>
                            <?php echo __('templates'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="/admin.php?r=tasks&amp;lang=<?php echo current_lang(); ?>"
                           class="<?php echo ($_GET['r'] ?? '') === 'tasks' ? 'active' : ''; ?>">
                            <i class="fas fa-tasks"></i>
                            <?php echo __('tasks'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="/admin.php?r=backups&amp;lang=<?php echo current_lang(); ?>"
                           class="<?php echo ($_GET['r'] ?? '') === 'backups' ? 'active' : ''; ?>">
                            <i class="fas fa-database"></i>
                            <?php echo __('backups'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="/admin.php?r=logs&amp;lang=<?php echo current_lang(); ?>"
                           class="<?php echo ($_GET['r'] ?? '') === 'logs' ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i>
                            <?php echo __('logs'); ?>
                        </a>
                    </li>

                    <!-- 导出功能下拉菜单 -->
                    <li class="nav-dropdown">
                        <a href="#" class="nav-dropdown-toggle" onclick="toggleDropdown(event, 'exportDropdown')">
                            <i class="fas fa-download"></i>
                            数据导出
                            <i class="fas fa-caret-down"></i>
                        </a>
                        <ul class="nav-dropdown-menu" id="exportDropdown">
                            <li>
                                <a href="/admin.php?r=export-users">
                                    <i class="fas fa-users"></i>
                                    导出用户
                                </a>
                            </li>
                            <li>
                                <a href="/admin.php?r=export-subscriptions">
                                    <i class="fas fa-list"></i>
                                    导出订阅
                                </a>
                            </li>
                            <li>
                                <a href="/admin.php?r=export-reminders">
                                    <i class="fas fa-bell"></i>
                                    导出提醒
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- 管理员信息 -->
                    <li class="nav-dropdown">
                        <a href="#" class="nav-dropdown-toggle" onclick="toggleDropdown(event, 'adminDropdown')">
                            <i class="fas fa-user-shield"></i>
                            <?php echo htmlspecialchars($_SESSION['admin']['username'] ?? '管理员'); ?>
                            <span class="admin-status">在线</span>
                        </a>
                        <ul class="nav-dropdown-menu" id="adminDropdown">
                            <li>
                                <a href="/" target="_blank">
                                    <i class="fas fa-external-link-alt"></i>
                                    访问前台
                                </a>
                            </li>
                            <li>
                                <a href="/admin.php?r=logout">
                                    <i class="fas fa-sign-out-alt"></i>
                                    退出登录
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- 语言切换 -->
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

        <style>
            /* 下拉菜单样式 */
            .nav-dropdown {
                position: relative;
            }

            .nav-dropdown-toggle {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .nav-dropdown-menu {
                display: none;
                position: absolute;
                top: 100%;
                right: 0;
                background: var(--white);
                border: 1px solid var(--gray-200);
                border-radius: var(--border-radius);
                box-shadow: var(--shadow-lg);
                min-width: 200px;
                z-index: 1000;
                padding: 0.5rem 0;
                list-style: none;
            }

            .nav-dropdown-menu.show {
                display: block;
            }

            .nav-dropdown-menu li {
                margin: 0;
            }

            .nav-dropdown-menu a {
                color: var(--gray-700) !important;
                background: transparent !important;
                padding: 0.75rem 1rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                width: 100%;
                border-radius: 0;
                font-size: 0.875rem;
            }

            .nav-dropdown-menu a:hover {
                background-color: var(--gray-50) !important;
                color: var(--primary-color) !important;
            }

            @media (max-width: 768px) {
                .nav-dropdown-menu {
                    position: static;
                    box-shadow: none;
                    border: none;
                    background: rgba(255, 255, 255, 0.1);
                    margin-top: 0.5rem;
                }

                .nav-dropdown-menu a {
                    color: rgba(255, 255, 255, 0.8) !important;
                    padding-left: 2rem;
                }

                .nav-dropdown-menu a:hover {
                    background-color: rgba(255, 255, 255, 0.1) !important;
                    color: var(--white) !important;
                }
            }
        </style>

        <script>
            function toggleMobileMenu() {
                const navMenu = document.getElementById('navMenu');
                navMenu.classList.toggle('show');
            }

            function toggleDropdown(event, dropdownId) {
                event.preventDefault();
                event.stopPropagation();

                // 关闭其他下拉菜单
                const allDropdowns = document.querySelectorAll('.nav-dropdown-menu');
                allDropdowns.forEach(dropdown => {
                    if (dropdown.id !== dropdownId) {
                        dropdown.classList.remove('show');
                    }
                });

                // 切换当前下拉菜单
                const dropdown = document.getElementById(dropdownId);
                dropdown.classList.toggle('show');
            }

            // 点击外部关闭下拉菜单
            document.addEventListener('click', function(event) {
                const navMenu = document.getElementById('navMenu');
                const navToggle = document.querySelector('.nav-toggle');

                // 关闭移动菜单
                if (!navMenu.contains(event.target) && !navToggle.contains(event.target)) {
                    navMenu.classList.remove('show');
                }

                // 关闭下拉菜单
                if (!event.target.closest('.nav-dropdown')) {
                    const allDropdowns = document.querySelectorAll('.nav-dropdown-menu');
                    allDropdowns.forEach(dropdown => {
                        dropdown.classList.remove('show');
                    });
                }
            });

            // 窗口大小改变时关闭移动菜单
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    document.getElementById('navMenu').classList.remove('show');
                    const allDropdowns = document.querySelectorAll('.nav-dropdown-menu');
                    allDropdowns.forEach(dropdown => {
                        dropdown.classList.remove('show');
                    });
                }
            });
        </script>