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
    <title>SubAlert - 订阅提醒管理工具</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <header>
        <nav class="navbar">
            <a href="/" class="nav-brand">SubAlert</a>
            <ul class="nav-menu">
                <?php if (current_user()): ?>
                <li><a href="/?r=dashboard&amp;lang=<?php echo current_lang(); ?>"><?php echo __('dashboard'); ?></a></li>
                <li><a href="/?r=subscriptions&amp;lang=<?php echo current_lang(); ?>"><?php echo __('subscriptions'); ?></a></li>
                <li><a href="/?r=reminders&amp;lang=<?php echo current_lang(); ?>"><?php echo __('reminders'); ?></a></li>
                <li><a href="/?r=stats&amp;lang=<?php echo current_lang(); ?>"><?php echo __('stats_analysis'); ?></a></li>
                <li><a href="/?r=settings&amp;lang=<?php echo current_lang(); ?>"><?php echo __('preferences'); ?></a></li>
                <li><a href="/?r=profile&amp;lang=<?php echo current_lang(); ?>"><?php echo __('profile'); ?></a></li>
                <li><a href="/?r=change-password&amp;lang=<?php echo current_lang(); ?>"><?php echo __('change_password'); ?></a></li>
                <li><a href="/?r=logout&amp;lang=<?php echo current_lang(); ?>"><?php echo __('logout'); ?></a></li>
                <?php else: ?>
                <li><a href="/?r=login&amp;lang=<?php echo current_lang(); ?>"><?php echo __('login'); ?></a></li>
                <li><a href="/?r=register&amp;lang=<?php echo current_lang(); ?>"><?php echo __('register'); ?></a></li>
                <?php endif; ?>
                <li>
                    <?php if (current_lang() === 'zh'): ?>
                        <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>?<?php echo http_build_query(array_merge($_GET, ['lang' => 'en'])); ?>">EN</a>
                    <?php else: ?>
                        <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>?<?php echo http_build_query(array_merge($_GET, ['lang' => 'zh'])); ?>">中文</a>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </header>
    <main class="container">
        <?php display_flash(); ?>