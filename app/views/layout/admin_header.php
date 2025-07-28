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
    <title>SubAlert 后台管理</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <a href="/admin.php?r=dashboard" class="nav-brand">SubAlert 管理后台</a>
            <ul class="nav-menu">
                <?php if (!empty($_SESSION['admin'])): ?>
                <li><a href="/admin.php?r=dashboard&amp;lang=<?php echo current_lang(); ?>"><?php echo __('dashboard'); ?></a></li>
                <li><a href="/admin.php?r=users&amp;lang=<?php echo current_lang(); ?>"><?php echo __('users'); ?></a></li>
                <li><a href="/admin.php?r=settings&amp;lang=<?php echo current_lang(); ?>"><?php echo __('settings'); ?></a></li>
                <li><a href="/admin.php?r=stats&amp;lang=<?php echo current_lang(); ?>"><?php echo __('stats'); ?></a></li>
                <li><a href="/admin.php?r=templates&amp;lang=<?php echo current_lang(); ?>"><?php echo __('templates'); ?></a></li>
                <li><a href="/admin.php?r=tasks&amp;lang=<?php echo current_lang(); ?>"><?php echo __('tasks'); ?></a></li>
                <li><a href="/admin.php?r=export-users&amp;lang=<?php echo current_lang(); ?>"><?php echo __('export_users'); ?></a></li>
                <li><a href="/admin.php?r=export-subscriptions&amp;lang=<?php echo current_lang(); ?>"><?php echo __('export_subscriptions'); ?></a></li>
                <li><a href="/admin.php?r=export-reminders&amp;lang=<?php echo current_lang(); ?>"><?php echo __('export_reminders'); ?></a></li>
                <li><a href="/admin.php?r=logs&amp;lang=<?php echo current_lang(); ?>"><?php echo __('logs'); ?></a></li>
                <li><a href="/admin.php?r=backups&amp;lang=<?php echo current_lang(); ?>"><?php echo __('backups'); ?></a></li>
                <li><a href="/admin.php?r=logout&amp;lang=<?php echo current_lang(); ?>"><?php echo __('logout'); ?></a></li>
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