<?php include __DIR__ . '/../layout/admin_header.php'; ?>
<h2>编辑用户</h2>
<form method="post" action="/admin.php?r=user-edit&id=<?php echo $user['id']; ?>">
    <p>ID: <?php echo $user['id']; ?></p>
    <label for="nickname">昵称</label>
    <input type="text" id="nickname" name="nickname" value="<?php echo htmlspecialchars($user['nickname']); ?>" required>
    <label for="status">状态</label>
    <select id="status" name="status">
        <?php
        $statuses = ['normal' => '正常', 'frozen' => '冻结', 'cancelled' => '注销'];
        foreach ($statuses as $key => $label):
            $selected = $user['status'] === $key ? 'selected' : '';
            echo "<option value='{$key}' {$selected}>{$label}</option>";
        endforeach;
        ?>
    </select>
    <button type="submit">保存</button>
    <a href="/admin.php?r=users">返回</a>
</form>
<?php include __DIR__ . '/../layout/admin_footer.php'; ?>