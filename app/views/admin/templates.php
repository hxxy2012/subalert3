<?php include __DIR__ . '/../layout/admin_header.php'; ?>
<h2>提醒模板管理</h2>
<p><a href="/admin.php?r=template-edit">新增模板</a></p>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>类型</th>
            <th>名称</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($templates as $tpl): ?>
        <tr>
            <td><?php echo $tpl['id']; ?></td>
            <td><?php echo htmlspecialchars($tpl['type']); ?></td>
            <td><?php echo htmlspecialchars($tpl['name']); ?></td>
            <td>
                <a href="/admin.php?r=template-edit&id=<?php echo $tpl['id']; ?>">编辑</a> |
                <a href="/admin.php?r=template-preview&id=<?php echo $tpl['id']; ?>" target="_blank">预览</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php include __DIR__ . '/../layout/admin_footer.php'; ?>