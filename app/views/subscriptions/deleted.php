<h2>已删除订阅</h2>
<p><a href="/?r=subscriptions">返回订阅列表</a></p>
<form method="post" action="/index.php?r=subscriptions-deleted">
<table>
    <thead>
        <tr>
            <th><input type="checkbox" id="select_all_deleted" onclick="toggleSelectAll(this)"></th>
            <th>名称</th>
            <th>类型</th>
            <th>价格</th>
            <th>周期</th>
            <th>到期时间</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($subscriptions)): ?>
            <tr><td colspan="7">暂无删除订阅</td></tr>
        <?php else: ?>
            <?php foreach ($subscriptions as $sub): ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?php echo $sub['id']; ?>"></td>
                    <td><?php echo htmlspecialchars($sub['name']); ?></td>
                    <td><?php echo htmlspecialchars($sub['type']); ?></td>
                    <td><?php echo number_format($sub['price'], 2); ?>元</td>
                    <td><?php echo htmlspecialchars($sub['cycle']); ?></td>
                    <td><?php echo htmlspecialchars($sub['expire_at']); ?></td>
                    <td><a href="/?r=subscription-edit&id=<?php echo $sub['id']; ?>">查看/编辑</a></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<div style="margin-top:10px;">
    <select name="batch_action">
        <option value="">批量操作</option>
        <option value="restore">恢复</option>
    </select>
    <button type="submit">执行</button>
</div>
</form>
<script>
function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('input[name="ids[]"]');
    for (const cb of checkboxes) {
        cb.checked = source.checked;
    }
}
</script>