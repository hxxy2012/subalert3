<?php include __DIR__ . '/../layout/admin_header.php'; ?>
<h2>数据库备份管理</h2>
<p>
    <a href="/admin.php?r=dashboard">返回仪表盘</a>
    | <a href="/admin.php?r=backup-create">创建新备份</a>
</p>

<?php if (empty($files)): ?>
    <p>当前没有备份文件。</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>文件名</th>
                <th>创建时间</th>
                <th>大小</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($files as $file): ?>
                <?php $filePath = __DIR__ . '/../../../public/backups/' . $file; ?>
                <tr>
                    <td><?php echo htmlspecialchars($file); ?></td>
                    <td><?php echo date('Y-m-d H:i:s', filemtime($filePath)); ?></td>
                    <td><?php echo round(filesize($filePath) / 1024, 2); ?> KB</td>
                    <td><a href="/backups/<?php echo urlencode($file); ?>" target="_blank">下载</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php include __DIR__ . '/../layout/admin_footer.php'; ?>