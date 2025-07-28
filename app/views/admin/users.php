<?php include __DIR__ . '/../layout/admin_header.php'; ?>
<h2>用户列表</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>邮箱</th>
            <th>昵称</th>
            <th>状态</th>
            <th>注册时间</th>
            <th>最后登录</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?php echo $u['id']; ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td><?php echo htmlspecialchars($u['nickname']); ?></td>
            <td><?php echo htmlspecialchars($u['status']); ?></td>
            <td><?php echo $u['created_at']; ?></td>
            <td><?php echo $u['last_login_at']; ?></td>
            <td>
                <a href="/admin.php?r=user-edit&id=<?php echo $u['id']; ?>">编辑</a> |
                <a href="/admin.php?r=user-delete&id=<?php echo $u['id']; ?>" onclick="return confirm('确认注销该用户吗？');">注销</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php include __DIR__ . '/../layout/admin_footer.php'; ?>