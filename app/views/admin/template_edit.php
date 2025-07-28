<?php include __DIR__ . '/../layout/admin_header.php'; ?>
<?php
    $id = $template['id'] ?? 0;
    $type = $template['type'] ?? '';
    $name = $template['name'] ?? '';
    $content = $template['content'] ?? '';
?>
<h2><?php echo $id ? '编辑模板' : '新增模板'; ?></h2>
<form method="post" action="">
    <label for="type">模板类型</label>
    <select id="type" name="type">
        <?php
        $types = ['email' => '邮件模板', 'feishu' => '飞书模板', 'wechat' => '企业微信模板', 'site' => '站内消息模板'];
        foreach ($types as $key => $label) {
            $selected = ($type === $key) ? 'selected' : '';
            echo "<option value='{$key}' {$selected}>{$label}</option>";
        }
        ?>
    </select>
    <label for="name">模板名称</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
    <label for="content">模板内容</label>
    <textarea id="content" name="content" rows="10" required><?php echo htmlspecialchars($content); ?></textarea>
    <button type="submit">保存</button>
    <a href="/admin.php?r=templates">返回</a>
</form>
<?php include __DIR__ . '/../layout/admin_footer.php'; ?>