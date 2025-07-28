<?php include __DIR__ . '/../layout/admin_header.php'; ?>
<h2>操作日志</h2>
<form method="get" action="/admin.php">
    <input type="hidden" name="r" value="logs">
    <label>
        管理员：
        <select name="admin_id">
            <option value="">全部</option>
            <?php foreach ($admins as $admin): ?>
                <option value="<?php echo htmlspecialchars($admin['id']); ?>" <?php echo ($filters['admin_id'] === (string)$admin['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($admin['username']); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        动作：<input type="text" name="action" value="<?php echo htmlspecialchars($filters['action']); ?>">
    </label>
    <label>
        日期从：<input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
    </label>
    <label>
        到：<input type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
    </label>
    <button type="submit">筛选</button>
</form>
<table>
    <thead>
        <tr>
            <th>管理员</th>
            <th>动作</th>
            <th>描述</th>
            <th>时间</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?php echo htmlspecialchars($log['username'] ?? ('ID ' . $log['admin_id'])); ?></td>
                <td><?php echo htmlspecialchars($log['action']); ?></td>
                <td><?php echo htmlspecialchars($log['description']); ?></td>
                <td><?php echo htmlspecialchars($log['created_at']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php include __DIR__ . '/../layout/admin_footer.php'; ?>