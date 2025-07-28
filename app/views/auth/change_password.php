<h2>修改密码</h2>
<form method="post" action="/index.php?r=change-password">
    <label for="current_password">当前密码</label>
    <input type="password" id="current_password" name="current_password" required>
    <label for="new_password">新密码</label>
    <input type="password" id="new_password" name="new_password" required>
    <label for="confirm_password">确认新密码</label>
    <input type="password" id="confirm_password" name="confirm_password" required>
    <button type="submit">修改</button>
    <a href="/?r=dashboard">取消</a>
</form>