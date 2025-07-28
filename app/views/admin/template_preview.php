<?php include __DIR__ . '/../layout/admin_header.php'; ?>
<h2>模板预览</h2>
<p><strong>类型：</strong><?php echo htmlspecialchars($template['type']); ?></p>
<p><strong>名称：</strong><?php echo htmlspecialchars($template['name']); ?></p>
<pre style="background:#f8f8f8;border:1px solid #ccc;padding:10px;white-space:pre-wrap;"><?php echo htmlspecialchars($template['content']); ?></pre>
<p><a href="/admin.php?r=templates">返回列表</a></p>
<?php include __DIR__ . '/../layout/admin_footer.php'; ?>