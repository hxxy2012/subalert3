<?php include __DIR__ . '/../layout/admin_header.php'; ?>
<h2>后台仪表盘</h2>
<p>站点名称：<?php echo htmlspecialchars($siteName); ?></p>
<p>用户总数：<?php echo htmlspecialchars($userCnt); ?></p>
<p>订阅总数：<?php echo htmlspecialchars($subCnt); ?></p>
<p>提醒统计：</p>
<ul>
    <li>待发送：<?php echo htmlspecialchars($remCounts['pending'] ?? 0); ?></li>
    <li>已发送：<?php echo htmlspecialchars($remCounts['sent'] ?? 0); ?></li>
    <li>已读：<?php echo htmlspecialchars($remCounts['read'] ?? 0); ?></li>
    <li>已完成：<?php echo htmlspecialchars($remCounts['done'] ?? 0); ?></li>
    <li>已取消：<?php echo htmlspecialchars($remCounts['cancelled'] ?? 0); ?></li>
</ul>
<p>模板数量：<?php echo htmlspecialchars($templateCnt); ?></p>
<p>备份数量：<?php echo htmlspecialchars($backupCnt); ?></p>
<?php include __DIR__ . '/../layout/admin_footer.php'; ?>