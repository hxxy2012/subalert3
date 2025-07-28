<h2>我的提醒</h2>
<?php if (empty($reminders)): ?>
    <p>暂无提醒</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>订阅名称</th>
                <th>提醒时间</th>
                <th>提前天数</th>
                <th>提醒方式</th>
                <th>状态</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reminders as $rem): ?>
            <tr>
                <td><?php echo htmlspecialchars($rem['subscription_name']); ?></td>
                <td><?php echo htmlspecialchars($rem['remind_at']); ?></td>
                <td><?php echo htmlspecialchars($rem['remind_days']); ?></td>
                <td><?php echo htmlspecialchars($rem['remind_type']); ?></td>
                <td><?php echo htmlspecialchars($rem['status']); ?></td>
                <td>
                    <?php if ($rem['status'] === 'pending' || $rem['status'] === 'sent'): ?>
                        <a href="/?r=reminder-action&id=<?php echo $rem['id']; ?>&op=renew">已续费</a> |
                        <a href="/?r=reminder-action&id=<?php echo $rem['id']; ?>&op=delay">延迟</a> |
                        <a href="/?r=reminder-action&id=<?php echo $rem['id']; ?>&op=cancel">取消</a> |
                        <a href="/?r=reminder-action&id=<?php echo $rem['id']; ?>&op=read">忽略</a>
                    <?php else: ?>
                        <span>--</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>