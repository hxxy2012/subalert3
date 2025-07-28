<h2>设置提醒</h2>
<p>订阅名称：<?php echo htmlspecialchars($subscription['name']); ?></p>
<p>到期时间：<?php echo htmlspecialchars($subscription['expire_at']); ?></p>
<form method="post" action="">
    <label for="remind_days">提前提醒天数</label>
    <select id="remind_days" name="remind_days">
        <?php for ($i = 1; $i <= 30; $i++): ?>
            <option value="<?php echo $i; ?>" <?php echo (isset($defaultDays) && (int)$defaultDays === $i) ? 'selected' : ''; ?>><?php echo $i; ?>天</option>
        <?php endfor; ?>
    </select>
    <label for="remind_type">提醒方式</label>
    <select id="remind_type" name="remind_type">
        <?php
        $types = ['email' => '邮件', 'feishu' => '飞书', 'wechat' => '企业微信', 'site' => '站内消息'];
        foreach ($types as $key => $label) {
            $selected = (isset($defaultType) && $defaultType === $key) ? 'selected' : '';
            echo "<option value='{$key}' {$selected}>{$label}</option>";
        }
        ?>
    </select>
    <button type="submit">保存</button>
    <a href="/?r=subscriptions">返回</a>
</form>