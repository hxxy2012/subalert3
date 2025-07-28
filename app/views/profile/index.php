<h2>个人资料</h2>
<form method="post" action="/index.php?r=profile" enctype="multipart/form-data">
    <p>邮箱：<?php echo htmlspecialchars($user['email']); ?></p>
    <label for="nickname">昵称</label>
    <input type="text" id="nickname" name="nickname" value="<?php echo htmlspecialchars($user['nickname'] ?? ''); ?>" required>
    <label for="phone">手机号</label>
    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
    <label for="avatar">头像</label>
    <?php if (!empty($user['avatar'])): ?>
        <div><img src="<?php echo $user['avatar']; ?>" alt="avatar" style="width:80px;height:80px;border-radius:40px;"></div>
    <?php endif; ?>
    <input type="file" id="avatar" name="avatar" accept="image/*">
    <button type="submit">保存</button>
    <a href="/?r=delete-account" onclick="return confirm('确认注销账户？');">注销账户</a>
</form>