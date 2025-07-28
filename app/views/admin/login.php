<?php include __DIR__ . '/../layout/admin_header.php'; ?>
<h2>管理员登录</h2>
<form method="post" action="/admin.php?r=login">
    <label for="username">用户名</label>
    <input type="text" id="username" name="username" required>
    <label for="password">密码</label>
    <input type="password" id="password" name="password" required>
    <button type="submit">登录</button>
</form>
<?php include __DIR__ . '/../layout/admin_footer.php'; ?>