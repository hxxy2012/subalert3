<h2>用户注册</h2>
<form method="post" action="/?r=register">
    <label for="email">邮箱</label>
    <input type="email" id="email" name="email" required>
    <label for="password">密码</label>
    <input type="password" id="password" name="password" required>
    <label for="confirm">确认密码</label>
    <input type="password" id="confirm" name="confirm" required>
    <label for="nickname">昵称</label>
    <input type="text" id="nickname" name="nickname" required>
    <button type="submit">注册</button>
</form>
<p>已有账户？<a href="/?r=login">直接登录</a></p>