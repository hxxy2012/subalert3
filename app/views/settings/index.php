<h2>偏好设置</h2>
<form method="post" action="/index.php?r=settings">
    <div class="form-group">
        <label for="default_remind_days">默认提前提醒天数</label>
        <select id="default_remind_days" name="default_remind_days">
            <?php for ($i = 1; $i <= 30; $i++): ?>
                <option value="<?php echo $i; ?>" <?php echo ((int)$defaultDays === $i) ? 'selected' : ''; ?>><?php echo $i; ?>天</option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="default_remind_type">默认提醒方式</label>
        <select id="default_remind_type" name="default_remind_type">
            <?php
            $types = ['email' => '邮件', 'feishu' => '飞书', 'wechat' => '企业微信', 'site' => '站内消息'];
            foreach ($types as $key => $label) {
                $selected = ($defaultType === $key) ? 'selected' : '';
                echo "<option value='{$key}' {$selected}>{$label}</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label for="feishu_webhook">飞书 Webhook</label>
        <input type="text" id="feishu_webhook" name="feishu_webhook" value="<?php echo htmlspecialchars($feishuWebhook); ?>" placeholder="https://open.feishu.cn/...">
    </div>
    <div class="form-group">
        <label for="wechat_webhook">企业微信 Webhook</label>
        <input type="text" id="wechat_webhook" name="wechat_webhook" value="<?php echo htmlspecialchars($wechatWebhook); ?>" placeholder="https://qyapi.weixin.qq.com/...">
    </div>
    <div class="form-group">
        <label for="mute_start">免打扰开始时间</label>
        <input type="time" id="mute_start" name="mute_start" value="<?php echo htmlspecialchars($muteStart); ?>">
    </div>
    <div class="form-group">
        <label for="mute_end">免打扰结束时间</label>
        <input type="time" id="mute_end" name="mute_end" value="<?php echo htmlspecialchars($muteEnd); ?>">
    </div>
    <div class="form-group">
        <label>
            <input type="checkbox" name="reminders_enabled" value="1" <?php echo ($remindersEnabled === '1') ? 'checked' : ''; ?>>
            启用所有提醒
        </label>
    </div>
    <button type="submit">保存设置</button>
</form>