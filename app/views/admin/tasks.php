<?php include __DIR__ . '/../layout/admin_header.php'; ?>
<h2>定时任务管理</h2>
<table>
    <thead>
        <tr>
            <th>任务名称</th>
            <th>描述</th>
            <th>最后执行时间</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tasks as $key => $task): ?>
        <tr>
            <td><?php echo htmlspecialchars($task['name']); ?></td>
            <td><?php echo htmlspecialchars($task['description']); ?></td>
            <td><?php echo htmlspecialchars($task['last_run']); ?></td>
            <td><a href="/admin.php?r=task-run&task=<?php echo $key; ?>" onclick="return confirm('确认手动执行该任务吗？');">执行</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php include __DIR__ . '/../layout/admin_footer.php'; ?>