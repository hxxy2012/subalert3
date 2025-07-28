<?php include __DIR__ . '/../layout/admin_header.php'; ?>
<h2>系统设置</h2>
<form method="post" action="/admin.php?r=settings">
    <label for="site_name">站点名称</label>
    <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($siteName); ?>">
    <h3>SMTP配置</h3>
    <label for="smtp_host">SMTP Host</label>
    <input type="text" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($smtpHost); ?>">
    <label for="smtp_port">SMTP Port</label>
    <input type="text" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($smtpPort); ?>">
    <label for="smtp_user">SMTP 用户名</label>
    <input type="text" id="smtp_user" name="smtp_user" value="<?php echo htmlspecialchars($smtpUser); ?>">
    <label for="smtp_pass">SMTP 密码</label>
    <input type="password" id="smtp_pass" name="smtp_pass" value="<?php echo htmlspecialchars($smtpPass); ?>">
    <button type="submit">保存设置</button>
</form>
<?php include __DIR__ . '/../layout/admin_footer.php'; ?>