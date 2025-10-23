<?php
// 获取当前订阅的提醒设置
$pdo = \App\Models\DB::getConnection();
$stmt = $pdo->prepare('SELECT * FROM reminders WHERE subscription_id = ? ORDER BY created_at DESC LIMIT 1');
$stmt->execute([$subscription['id']]);
$currentReminder = $stmt->fetch();

// 获取用户默认设置
$user = current_user();
$settingsStmt = $pdo->prepare('SELECT setting_key, setting_value FROM user_settings WHERE user_id = ?');
$settingsStmt->execute([$user['id']]);
$userSettings = [];
while ($row = $settingsStmt->fetch()) {
    $userSettings[$row['setting_key']] = $row['setting_value'];
}

$defaultDays = $userSettings['default_remind_days'] ?? 3;
$defaultType = $userSettings['default_remind_type'] ?? 'email';
?>

<h1 class="page-title">
    <i class="fas fa-edit"></i>
    编辑订阅
</h1>

<div class="d-flex justify-content-center">
    <div class="card" style="max-width: 600px; width: 100%;">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-alt"></i>
                订阅信息
            </h3>
        </div>
        <div class="card-body">
            <form method="post" action="/?r=subscription-edit&id=<?php echo $subscription['id']; ?>">
                <!-- 订阅名称 -->
                <div class="form-group">
                    <label for="name" class="form-label">
                        <i class="fas fa-tag"></i>
                        订阅名称 <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        value="<?php echo htmlspecialchars($subscription['name']); ?>"
                        required
                    >
                </div>

                <!-- 服务类型 -->
                <div class="form-group">
                    <label for="type" class="form-label">
                        <i class="fas fa-layer-group"></i>
                        服务类型 <span class="text-danger">*</span>
                    </label>
                    <select id="type" name="type" class="form-control" required>
                        <?php
                        $types = ['video' => '视频', 'music' => '音乐', 'software' => '软件', 'communication' => '通讯', 'other' => '其他'];
                        foreach ($types as $key => $label):
                            $selected = $subscription['type'] === $key ? 'selected' : '';
                            echo "<option value='{$key}' {$selected}>{$label}</option>";
                        endforeach;
                        ?>
                    </select>
                </div>

                <!-- 价格 & 周期 -->
                <div class="d-flex gap-3">
                    <div class="form-group" style="flex: 1;">
                        <label for="price" class="form-label">
                            <i class="fas fa-dollar-sign"></i>
                            订阅价格（元） <span class="text-danger">*</span>
                        </label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--gray-500);">¥</span>
                            <input
                                type="number"
                                step="0.01"
                                id="price"
                                name="price"
                                class="form-control"
                                style="padding-left: 2rem;"
                                value="<?php echo $subscription['price']; ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label for="cycle" class="form-label">
                            <i class="fas fa-calendar-alt"></i>
                            订阅周期 <span class="text-danger">*</span>
                        </label>
                        <select id="cycle" name="cycle" class="form-control" required>
                            <?php
                            $cycles = ['monthly' => '月付', 'quarterly' => '季付', 'yearly' => '年付', 'custom' => '自定义'];
                            foreach ($cycles as $key => $label):
                                $selected = $subscription['cycle'] === $key ? 'selected' : '';
                                echo "<option value='{$key}' {$selected}>{$label}</option>";
                            endforeach;
                            ?>
                        </select>
                    </div>
                </div>

                <!-- 到期日期 -->
                <div class="form-group">
                    <label for="expire_at" class="form-label">
                        <i class="fas fa-clock"></i>
                        到期日期 <span class="text-danger">*</span>
                    </label>
                    <input type="date"
                           id="expire_at"
                           name="expire_at"
                           class="form-control"
                           value="<?php echo $subscription['expire_at']; ?>"
                           required
                           min="<?php echo date('Y-m-d'); ?>"
                           onchange="updateReminderDate()">
                    <small class="text-muted">请选择服务的到期日期</small>
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" id="auto_renew" name="auto_renew" value="1" <?php echo $subscription['auto_renew'] ? 'checked' : ''; ?>>
                    <label for="auto_renew" class="d-flex align-items-center gap-2">
                        <i class="fas fa-redo text-success"></i>
                        启用自动续费
                        <small class="text-muted">（到期前会自动计算下次续费时间）</small>
                    </label>
                </div>

                <!-- 状态 -->
                <div class="form-group">
                    <label for="status" class="form-label">
                        <i class="fas fa-toggle-on"></i>
                        状态
                    </label>
                    <select id="status" name="status" class="form-control">
                        <?php
                        $statuses = ['active' => '正常', 'paused' => '暂停', 'cancelled' => '已取消', 'expired' => '已过期'];
                        foreach ($statuses as $key => $label):
                            $selected = $subscription['status'] === $key ? 'selected' : '';
                            echo "<option value='{$key}' {$selected}>{$label}</option>";
                        endforeach;
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="note" class="form-label">
                        <i class="fas fa-sticky-note"></i>
                        备注信息
                    </label>
                    <textarea id="note"
                              name="note"
                              class="form-control"
                              rows="3"
                              placeholder="可以添加账号信息、特殊说明等..."><?php echo htmlspecialchars($subscription['note']); ?></textarea>
                    <small class="text-muted">选填，用于记录相关说明</small>
                </div>

                <!-- 提醒设置区块 -->
                <div class="card mb-4" style="border: 2px solid var(--primary-color); background-color: var(--primary-light);">
                    <div class="card-header" style="background-color: var(--primary-color); color: white;">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-bell"></i>
                            提醒设置
                            <small style="opacity: 0.9;">（推荐开启，避免忘记续费）</small>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input type="checkbox"
                                   id="enable_reminder"
                                   name="enable_reminder"
                                   value="1"
                                   <?php echo $currentReminder ? 'checked' : ''; ?>
                                   onchange="toggleReminderSettings()">
                            <label for="enable_reminder" class="d-flex align-items-center gap-2">
                                <i class="fas fa-toggle-on text-success"></i>
                                <div>
                                    <strong>启用到期提醒</strong>
                                    <br><small class="text-muted">系统会在订阅到期前自动发送提醒通知</small>
                                </div>
                            </label>
                        </div>

                        <div id="reminderSettings" class="reminder-settings" style="<?php echo $currentReminder ? '' : 'display: none;'; ?>">
                            <div class="d-flex gap-3 mb-3">
                                <div class="form-group" style="flex: 1;">
                                    <label for="remind_days" class="form-label">
                                        <i class="fas fa-calendar-alt"></i>
                                        提前提醒天数
                                    </label>
                                    <select id="remind_days" name="remind_days" class="form-control" onchange="updateReminderDate()">
                                        <?php for ($i = 1; $i <= 30; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo ($currentReminder && (int)$currentReminder['remind_days'] === $i) ? 'selected' : ((!$currentReminder && $i == $defaultDays) ? 'selected' : ''); ?>>
                                                <?php echo $i; ?> 天前
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <small class="text-muted">在到期前多少天发送提醒</small>
                                </div>

                                <div class="form-group" style="flex: 1;">
                                    <label for="remind_type" class="form-label">
                                        <i class="fas fa-paper-plane"></i>
                                        提醒方式
                                    </label>
                                    <select id="remind_type" name="remind_type" class="form-control">
                                        <?php
                                        $types = [
                                            'email' => '📧 邮件提醒',
                                            'feishu' => '🔔 飞书通知',
                                            'wechat' => '💬 企业微信',
                                            'weibo' => '📱 微博发布',
                                            'site' => '🖥️ 站内消息'
                                        ];
                                        foreach ($types as $key => $label):
                                            $isSelected = $currentReminder ?
                                                ($currentReminder['remind_type'] === $key) :
                                                ($defaultType === $key);
                                            $selected = $isSelected ? 'selected' : '';
                                            echo "<option value='{$key}' {$selected}>{$label}</option>";
                                        endforeach;
                                        ?>
                                    </select>
                                    <small class="text-muted">选择接收提醒的方式</small>
                                </div>
                            </div>

                            <div class="alert alert-info mb-0" id="reminderPreview">
                                <i class="fas fa-info-circle"></i>
                                <strong>提醒预览：</strong>
                                <span id="reminderText">系统将在到期前 <?php echo $currentReminder ? $currentReminder['remind_days'] : $defaultDays; ?> 天发送提醒</span>
                                <br>
                                <small class="text-muted">
                                    预计提醒时间：<span id="reminderDate"><?php
                                        $remindDate = date('Y-m-d', strtotime($subscription['expire_at'] . ' -' . ($currentReminder ? $currentReminder['remind_days'] : $defaultDays) . ' day'));
                                        echo $remindDate;
                                    ?></span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 操作按钮 -->
                <div class="d-flex gap-3 justify-content-end">
                    <a href="/?r=subscriptions" class="btn btn-outline-primary">
                        <i class="fas fa-undo"></i>
                        取消
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        保存
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// 切换提醒设置显示/隐藏
function toggleReminderSettings() {
    const checkbox = document.getElementById('enable_reminder');
    const settings = document.getElementById('reminderSettings');

    if (checkbox.checked) {
        settings.style.display = 'block';
        updateReminderDate();
    } else {
        settings.style.display = 'none';
    }
}

// 更新提醒日期预览
function updateReminderDate() {
    const expireDate = document.getElementById('expire_at').value;
    const remindDays = parseInt(document.getElementById('remind_days').value);

    if (expireDate && remindDays) {
        const expire = new Date(expireDate);
        const remind = new Date(expire.getTime() - (remindDays * 24 * 60 * 60 * 1000));

        const reminderText = document.getElementById('reminderText');
        const reminderDate = document.getElementById('reminderDate');

        reminderText.textContent = `系统将在到期前 ${remindDays} 天发送提醒`;
        reminderDate.textContent = remind.toLocaleDateString('zh-CN');

        // 检查提醒日期是否已过
        const now = new Date();
        now.setHours(0, 0, 0, 0);

        if (remind < now) {
            reminderDate.style.color = '#dc3545';
            reminderDate.innerHTML += ' <small>(已过期)</small>';
        } else {
            reminderDate.style.color = '#28a745';
        }
    }
}

// 页面加载时初始化
document.addEventListener('DOMContentLoaded', function() {
    updateReminderDate();
});
</script>

<style>
.reminder-settings {
    transition: all 0.3s ease;
}

.form-check input[type="checkbox"] {
    margin-right: 0.5rem;
}

.alert-info {
    background-color: #e3f2fd;
    border-color: #2196f3;
    color: #1976d2;
}

:root {
    --primary-color: #007bff;
    --primary-light: #f8f9ff;
    --gray-500: #6c757d;
}
</style>