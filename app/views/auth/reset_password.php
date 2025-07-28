<h2>重设密码</h2>
<?php if (!isset($token) || !$token): ?>
    <p>无效的重置链接。</p>
<?php else: ?>
<form method="post" action="/index.php?r=reset-password&token=<?php echo htmlspecialchars($token); ?>">
    <label for="new_password">新密码</label>
    <input type="password" id="new_password" name="new_password" required>
    <label for="confirm_password">确认新密码</label>
    <input type="password" id="confirm_password" name="confirm_password" required>
    <button type="submit">重设密码</button>
</form>
<?php endif; ?>
<p><a href="/?r=login">返回登录</a></p>