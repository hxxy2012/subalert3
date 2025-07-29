<h1 class="page-title">
    <i class="fas fa-cog"></i>
    个人偏好设置
</h1>

<div class="d-flex justify-content-center">
    <div style="max-width: 800px; width: 100%;">
        <form method="post" action="/index.php?r=settings">

            <!-- 提醒设置 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bell"></i>
                        提醒设置
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-check mb-4">
                        <input type="checkbox"
                               id="reminders_enabled"
                               name="reminders_enabled"
                               value="1"
                               <?php echo ($remindersEnabled === '1') ? 'checked' : ''; ?>>
                        <label for="reminders_enabled" class="d-flex align-items-center gap-2">
                            <i class="fas fa-toggle-on text-success"></i>
                            <div>
                                <strong>启用提醒功能</strong>
                                <br><small class="text-muted">关闭后将不会收到任何提醒通知</small>
                            </div>
                        </label>
                    </div>

                    <div class="d-flex gap-3 mb-4">
                        <div class="form-group" style="flex: 1;">
                            <label for="default_remind_days" class="form-label">
                                <i class="fas fa-calendar-alt"></i>
                                默认提前提醒天数
                            </label>
                            <select id="default_remind_days" name="default_remind_days" class="form-control">
                                <?php for ($i = 1; $i <= 30; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ((int)$defaultDays === $i) ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> 天前
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <small class="text-muted">新添加订阅时的默认提醒时间</small>
                        </div>

                        <div class="form-group" style="flex: 1;">
                            <label for="default_remind_type" class="form-label">
                                <i class="fas fa-paper-plane"></i>
                                默认提醒方式
                            </label>
                            <select id="default_remind_type" name="default_remind_type" class="form-control">
                                <?php
                                $types = [
                                    'email' => '📧 邮件提醒',
                                    'feishu' => '🔔 飞书通知',
                                    'wechat' => '💬 企业微信',
                                    'site' => '🖥️ 站内消息'
                                ];
                                foreach ($types as $key => $label):
                                    $selected = ($defaultType === $key) ? 'selected' : '';
                                    echo "<option value='{$key}' {$selected}>{$label}</option>";
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 通知渠道配置 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-link"></i>
                        通知渠道配置
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="feishu_webhook" class="form-label">
                            <i class="fas fa-robot"></i>
                            飞书机器人 Webhook 地址
                        </label>
                        <input type="url"
                               id="feishu_webhook"
                               name="feishu_webhook"
                               class="form-control"
                               value="<?php echo htmlspecialchars($feishuWebhook); ?>"
                               placeholder="https://open.feishu.cn/open-apis/bot/v2/hook/...">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            在飞书群聊中添加机器人，获取 Webhook 地址
                            <a href="#" class="text-primary" onclick="showFeishuHelp()">查看配置教程</a>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="wechat_webhook" class="form-label">
                            <i class="fas fa-comments"></i>
                            企业微信机器人 Webhook 地址
                        </label>
                        <input type="url"
                               id="wechat_webhook"
                               name="wechat_webhook"
                               class="form-control"
                               value="<?php echo htmlspecialchars($wechatWebhook); ?>"
                               placeholder="https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=...">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            在企业微信群聊中添加机器人，获取 Webhook 地址
                            <a href="#" class="text-primary" onclick="showWechatHelp()">查看配置教程</a>
                        </small>
                    </div>
                </div>
            </div>

            <!-- 免打扰设置 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-moon"></i>
                        免打扰时间
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        在免打扰时间段内，系统将暂停发送提醒通知
                    </div>

                    <div class="d-flex gap-3 align-items-center">
                        <div class="form-group" style="flex: 1;">
                            <label for="mute_start" class="form-label">
                                <i class="fas fa-clock"></i>
                                开始时间
                            </label>
                            <input type="time"
                                   id="mute_start"
                                   name="mute_start"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($muteStart); ?>">
                        </div>

                        <div class="d-flex align-items-center" style="margin-top: 1.5rem;">
                            <i class="fas fa-arrow-right text-muted"></i>
                        </div>

                        <div class="form-group" style="flex: 1;">
                            <label for="mute_end" class="form-label">
                                <i class="fas fa-clock"></i>
                                结束时间
                            </label>
                            <input type="time"
                                   id="mute_end"
                                   name="mute_end"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($muteEnd); ?>">
                        </div>
                    </div>

                    <small class="text-muted">
                        <i class="fas fa-lightbulb"></i>
                        推荐设置：22:00 - 08:00 (避免夜间打扰)
                    </small>
                </div>
            </div>

            <!-- 保存按钮 -->
            <div class="card">
                <div class="card-body text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i>
                        保存设置
                    </button>
                    <div class="mt-3">
                        <small class="text-muted">设置将立即生效，影响后续的提醒行为</small>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

<!-- 帮助模态框 -->
<div id="helpModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center;">
    <div class="card" style="max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title" id="helpTitle">配置教程</h3>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="closeHelp()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="card-body" id="helpContent">
            <!-- 帮助内容将通过 JavaScript 动态填入 -->
        </div>
    </div>
</div>

<script>
function showFeishuHelp() {
    document.getElementById('helpTitle').textContent = '飞书机器人配置教程';
    document.getElementById('helpContent').innerHTML = `
        <ol class="mb-0">
            <li class="mb-3">
                <strong>打开飞书群聊</strong>
                <p class="text-muted mb-2">进入你想要接收提醒的飞书群聊</p>
            </li>
            <li class="mb-3">
                <strong>添加机器人</strong>
                <p class="text-muted mb-2">点击群聊右上角设置 → 群机器人 → 添加机器人</p>
            </li>
            <li class="mb-3">
                <strong>选择自定义机器人</strong>
                <p class="text-muted mb-2">选择"自定义机器人"并设置名称和头像</p>
            </li>
            <li class="mb-3">
                <strong>获取 Webhook 地址</strong>
                <p class="text-muted mb-2">复制生成的 Webhook 地址，粘贴到上方输入框中</p>
            </li>
            <li class="mb-0">
                <strong>测试连接</strong>
                <p class="text-muted mb-2">保存设置后，系统会自动测试连接是否正常</p>
            </li>
        </ol>
        <div class="alert alert-warning mt-3">
            <i class="fas fa-exclamation-triangle"></i>
            注意：Webhook 地址包含敏感信息，请勿分享给他人
        </div>
    `;
    document.getElementById('helpModal').style.display = 'flex';
}

function showWechatHelp() {
    document.getElementById('helpTitle').textContent = '企业微信机器人配置教程';
    document.getElementById('helpContent').innerHTML = `
        <ol class="mb-0">
            <li class="mb-3">
                <strong>打开企业微信群聊</strong>
                <p class="text-muted mb-2">进入你想要接收提醒的企业微信群聊</p>
            </li>
            <li class="mb-3">
                <strong>添加群机器人</strong>
                <p class="text-muted mb-2">点击群聊右上角 → 群机器人 → 新建机器人</p>
            </li>
            <li class="mb-3">
                <strong>设置机器人信息</strong>
                <p class="text-muted mb-2">输入机器人名称，选择头像</p>
            </li>
            <li class="mb-3">
                <strong>获取 Webhook 地址</strong>
                <p class="text-muted mb-2">复制生成的 Webhook 地址，粘贴到上方输入框中</p>
            </li>
            <li class="mb-0">
                <strong>完成配置</strong>
                <p class="text-muted mb-2">保存设置后即可接收提醒通知</p>
            </li>
        </ol>
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle"></i>
            企业微信机器人支持发送文本、图片、文件等多种格式的消息
        </div>
    `;
    document.getElementById('helpModal').style.display = 'flex';
}

function closeHelp() {
    document.getElementById('helpModal').style.display = 'none';
}

// 点击模态框外部关闭
document.getElementById('helpModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeHelp();
    }
});

// Webhook 地址验证
document.addEventListener('DOMContentLoaded', function() {
    const feishuInput = document.getElementById('feishu_webhook');
    const wechatInput = document.getElementById('wechat_webhook');

    function validateWebhook(input, expectedDomain) {
        input.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value && !value.includes(expectedDomain)) {
                this.setCustomValidity(`请输入有效的${expectedDomain}地址`);
                this.style.borderColor = 'var(--danger-color)';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = '';
            }
        });
    }

    validateWebhook(feishuInput, 'open.feishu.cn');
    validateWebhook(wechatInput, 'qyapi.weixin.qq.com');

    // 时间验证
    const muteStart = document.getElementById('mute_start');
    const muteEnd = document.getElementById('mute_end');

    function validateTimeRange() {
        if (muteStart.value && muteEnd.value) {
            // 这里可以添加时间范围验证逻辑
            // 注意跨天的情况（如 22:00 - 08:00）
        }
    }

    muteStart.addEventListener('change', validateTimeRange);
    muteEnd.addEventListener('change', validateTimeRange);
});
</script>