<h1 class="page-title">
    <i class="fas fa-plus"></i>
    添加新订阅
</h1>

<div class="d-flex justify-content-center">
    <div class="card" style="max-width: 600px; width: 100%;">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit"></i>
                订阅信息
            </h3>
        </div>
        <div class="card-body">
            <form method="post" action="/?r=subscription-create">
                <!-- 基本订阅信息 -->
                <div class="form-group">
                    <label for="name" class="form-label">
                        <i class="fas fa-tag"></i>
                        订阅名称 <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           class="form-control"
                           placeholder="例如：Netflix 高级版"
                           required>
                    <small class="text-muted">请输入订阅服务的名称</small>
                </div>

                <div class="form-group">
                    <label for="type" class="form-label">
                        <i class="fas fa-tag"></i>
                        服务类型 <span class="text-danger">*</span>
                    </label>
                    <select id="type" name="type" class="form-control" required>
                        <option value="">请选择服务类型</option>
                        <option value="video">📺 视频娱乐</option>
                        <option value="music">🎵 音乐</option>
                        <option value="software">💻 软件工具</option>
                        <option value="communication">💬 通讯社交</option>
                        <option value="other">📦 其他</option>
                    </select>
                </div>

                <div class="d-flex gap-3">
                    <div class="form-group" style="flex: 1;">
                        <label for="price" class="form-label">
                            <i class="fas fa-dollar-sign"></i>
                            订阅价格 <span class="text-danger">*</span>
                        </label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--gray-500);">¥</span>
                            <input type="number"
                                   step="0.01"
                                   id="price"
                                   name="price"
                                   class="form-control"
                                   style="padding-left: 2rem;"
                                   placeholder="0.00"
                                   required>
                        </div>
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label for="cycle" class="form-label">
                            <i class="fas fa-calendar-alt"></i>
                            订阅周期 <span class="text-danger">*</span>
                        </label>
                        <select id="cycle" name="cycle" class="form-control" required onchange="updateExpireDate()">
                            <option value="monthly">📅 月付</option>
                            <option value="quarterly">📅 季付（3个月）</option>
                            <option value="yearly">📅 年付</option>
                            <option value="custom">📅 自定义</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="expire_at" class="form-label">
                        <i class="fas fa-clock"></i>
                        到期日期 <span class="text-danger">*</span>
                    </label>
                    <input type="date"
                           id="expire_at"
                           name="expire_at"
                           class="form-control"
                           required
                           min="<?php echo date('Y-m-d'); ?>"
                           onchange="updateReminderDate()">
                    <small class="text-muted">请选择服务的到期日期</small>
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" id="auto_renew" name="auto_renew" value="1">
                    <label for="auto_renew" class="d-flex align-items-center gap-2">
                        <i class="fas fa-redo text-success"></i>
                        启用自动续费
                        <small class="text-muted">（到期前会自动计算下次续费时间）</small>
                    </label>
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
                              placeholder="可以添加账号信息、特殊说明等..."></textarea>
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
                                   checked
                                   onchange="toggleReminderSettings()">
                            <label for="enable_reminder" class="d-flex align-items-center gap-2">
                                <i class="fas fa-toggle-on text-success"></i>
                                <div>
                                    <strong>启用到期提醒</strong>
                                    <br><small class="text-muted">系统会在订阅到期前自动发送提醒通知</small>
                                </div>
                            </label>
                        </div>

                        <div id="reminderSettings" class="reminder-settings">
                            <div class="d-flex gap-3 mb-3">
                                <div class="form-group" style="flex: 1;">
                                    <label for="remind_days" class="form-label">
                                        <i class="fas fa-calendar-alt"></i>
                                        提前提醒天数
                                    </label>
                                    <select id="remind_days" name="remind_days" class="form-control" onchange="updateReminderDate()">
                                        <?php for ($i = 1; $i <= 30; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo $i === 3 ? 'selected' : ''; ?>>
                                                <?php echo $i; ?> 天前
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <small class="text-muted">建议 3-7 天，给续费留出充足时间</small>
                                </div>

                                <div class="form-group" style="flex: 1;">
                                    <label for="remind_type" class="form-label">
                                        <i class="fas fa-paper-plane"></i>
                                        提醒方式
                                    </label>
                                    <select id="remind_type" name="remind_type" class="form-control">
                                        <option value="email">📧 邮件提醒</option>
                                        <option value="feishu">🔔 飞书通知</option>
                                        <option value="wechat">💬 企业微信</option>
                                        <option value="weibo">📱 微博发布</option>
                                        <option value="site">🖥️ 站内消息</option>
                                    </select>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>提醒时间预览：</strong>
                                <span id="reminderPreview">请先设置到期日期</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 justify-content-end">
                    <a href="/?r=subscriptions" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i>
                        取消
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i>
                        保存订阅
                        <small style="display: block; font-size: 0.75rem; opacity: 0.9;" id="submitHint">
                            （将同时设置提醒）
                        </small>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 快速添加模板 -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-bolt"></i>
            快速添加常用服务
        </h3>
    </div>
    <div class="card-body">
        <div class="d-flex gap-2 flex-wrap justify-content-center">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillTemplate('Netflix', 'video', 89.00, 'monthly')">
                <i class="fab fa-netflix"></i> Netflix
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillTemplate('Spotify Premium', 'music', 15.00, 'monthly')">
                <i class="fab fa-spotify"></i> Spotify
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillTemplate('YouTube Premium', 'video', 15.00, 'monthly')">
                <i class="fab fa-youtube"></i> YouTube
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillTemplate('Adobe Creative Cloud', 'software', 148.00, 'monthly')">
                <i class="fab fa-adobe"></i> Adobe CC
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillTemplate('Microsoft 365', 'software', 69.00, 'monthly')">
                <i class="fab fa-microsoft"></i> Office 365
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillTemplate('腾讯视频VIP', 'video', 30.00, 'monthly')">
                🎬 腾讯视频
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillTemplate('爱奇艺VIP', 'video', 25.00, 'monthly')">
                📺 爱奇艺
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillTemplate('QQ音乐绿钻', 'music', 15.00, 'monthly')">
                🎵 QQ音乐
            </button>
        </div>
        <p class="text-muted text-center mt-3 mb-0">
            <small>点击上方按钮可快速填入常用服务信息，价格仅供参考</small>
        </p>
    </div>
</div>

<script>
function fillTemplate(name, type, price, cycle) {
    document.getElementById('name').value = name;
    document.getElementById('type').value = type;
    document.getElementById('price').value = price.toFixed(2);
    document.getElementById('cycle').value = cycle;

    // 自动设置到期日期
    updateExpireDate();

    // 滚动到表单顶部
    document.querySelector('.card').scrollIntoView({ behavior: 'smooth' });
}

// 根据周期自动更新到期日期
function updateExpireDate() {
    const cycle = document.getElementById('cycle').value;
    const today = new Date();
    let expireDate = new Date(today);

    switch(cycle) {
        case 'monthly':
            expireDate.setMonth(expireDate.getMonth() + 1);
            break;
        case 'quarterly':
            expireDate.setMonth(expireDate.getMonth() + 3);
            break;
        case 'yearly':
            expireDate.setFullYear(expireDate.getFullYear() + 1);
            break;
        default:
            expireDate.setMonth(expireDate.getMonth() + 1);
    }

    document.getElementById('expire_at').value = expireDate.toISOString().split('T')[0];
    
    // 更新提醒预览
    updateReminderDate();
}

// 更新提醒日期预览
function updateReminderDate() {
    const expireDate = document.getElementById('expire_at').value;
    const remindDays = parseInt(document.getElementById('remind_days').value);
    const previewElement = document.getElementById('reminderPreview');

    if (expireDate && remindDays) {
        const expire = new Date(expireDate);
        const remind = new Date(expire);
        remind.setDate(remind.getDate() - remindDays);

        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            weekday: 'long'
        };
        
        previewElement.innerHTML = `
            将在 <strong>${remind.toLocaleDateString('zh-CN', options)}</strong> 发送提醒
            <br><small>（到期前 ${remindDays} 天）</small>
        `;
    } else {
        previewElement.textContent = '请先设置到期日期';
    }
}

// 切换提醒设置显示
function toggleReminderSettings() {
    const enabled = document.getElementById('enable_reminder').checked;
    const settings = document.getElementById('reminderSettings');
    const submitHint = document.getElementById('submitHint');
    
    if (enabled) {
        settings.style.display = 'block';
        settings.style.opacity = '1';
        submitHint.textContent = '（将同时设置提醒）';
    } else {
        settings.style.display = 'none';
        settings.style.opacity = '0.5';
        submitHint.textContent = '（不设置提醒）';
    }
}

// 表单验证
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const priceInput = document.getElementById('price');
    const expireDateInput = document.getElementById('expire_at');

    // 初始化到期日期
    updateExpireDate();

    // 价格输入验证
    priceInput.addEventListener('input', function() {
        const value = parseFloat(this.value);
        if (value <= 0) {
            this.setCustomValidity('价格必须大于0');
        } else {
            this.setCustomValidity('');
        }
    });

    // 日期验证
    expireDateInput.addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (selectedDate < today) {
            this.setCustomValidity('到期日期不能早于今天');
        } else {
            this.setCustomValidity('');
        }
        
        updateReminderDate();
    });

    // 表单提交验证
    form.addEventListener('submit', function(e) {
        const price = parseFloat(priceInput.value);
        if (isNaN(price) || price <= 0) {
            e.preventDefault();
            alert('请输入有效的价格（必须大于0）');
            priceInput.focus();
            return false;
        }

        const expireDate = new Date(expireDateInput.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (expireDate < today) {
            e.preventDefault();
            alert('到期日期不能早于今天');
            expireDateInput.focus();
            return false;
        }

        // 显示提交状态
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 保存中...';
    });

    // 监听提醒天数变化
    document.getElementById('remind_days').addEventListener('change', updateReminderDate);
});
</script>

<style>
.reminder-settings {
    transition: all 0.3s ease;
}

.reminder-settings.disabled {
    opacity: 0.5;
    pointer-events: none;
}

/* 提醒设置区块特殊样式 */
.card .card-header h4 {
    margin: 0;
    font-size: 1.1rem;
}

.btn-lg small {
    font-weight: normal;
    margin-top: 0.25rem;
}

/* 提醒预览样式 */
#reminderPreview {
    font-weight: 500;
}

#reminderPreview strong {
    color: var(--primary-color);
}

/* 表单组间距调整 */
.form-group + .card {
    margin-top: 2rem;
}

/* 响应式优化 */
@media (max-width: 768px) {
    .d-flex.gap-3 {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn-lg {
        font-size: 1rem;
        padding: 0.875rem 1.5rem;
    }
}
</style>