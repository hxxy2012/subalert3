<h2>首页概览</h2>
<div class="dashboard">
    <p>总订阅数量：<?php echo $total; ?></p>
    <h3>即将到期订阅（7天内）</h3>
    <?php if (empty($upcoming)): ?>
        <p>暂无即将到期的订阅。</p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>订阅名称</th><th>到期时间</th><th>价格</th></tr>
            </thead>
            <tbody>
            <?php foreach ($upcoming as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars($item['expire_at']); ?></td>
                    <td><?php echo number_format($item['price'], 2); ?>元</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>