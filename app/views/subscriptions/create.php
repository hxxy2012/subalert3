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
                        <select id="cycle" name="cycle" class="form-control" required>
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
                           min="<?php echo date('Y-m-d'); ?>">
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

                <div class="d-flex gap-3 justify-content-end">
                    <a href="/?r=subscriptions" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i>
                        取消
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        保存订阅
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

    // 自动设置到期日期（一个月后）
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

    // 滚动到表单顶部
    document.querySelector('.card').scrollIntoView({ behavior: 'smooth' });
}

// 表单验证
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const priceInput = document.getElementById('price');
    const expireDateInput = document.getElementById('expire_at');

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
    });
});
</script>