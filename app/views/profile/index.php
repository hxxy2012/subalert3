<h1 class="page-title">
    <i class="fas fa-user"></i>
    个人资料
</h1>

<div class="d-flex justify-content-center">
    <div style="max-width: 600px; width: 100%;">

        <!-- 基本信息卡片 -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-id-card"></i>
                    基本信息
                </h3>
            </div>
            <div class="card-body">
                <form method="post" action="/index.php?r=profile" enctype="multipart/form-data">

                    <!-- 头像上传 -->
                    <div class="form-group text-center">
                        <label class="form-label">头像</label>
                        <div class="mb-3">
                            <?php if (!empty($user['avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($user['avatar']); ?>"
                                     alt="avatar"
                                     id="avatarPreview"
                                     style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--gray-200);">
                            <?php else: ?>
                                <div id="avatarPreview"
                                     style="width: 100px; height: 100px; border-radius: 50%; background: var(--gray-200); display: flex; align-items: center; justify-content: center; margin: 0 auto; border: 3px solid var(--gray-200);">
                                    <i class="fas fa-user text-muted" style="font-size: 2rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="file"
                               id="avatar"
                               name="avatar"
                               class="form-control"
                               accept="image/*"
                               onchange="previewAvatar(this)">
                        <small class="text-muted">支持 JPG, PNG, GIF 格式，建议尺寸 200x200 像素</small>
                    </div>

                    <!-- 邮箱（只读） -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope mr-2"></i>邮箱地址
                        </label>
                        <input type="email"
                               class="form-control"
                               value="<?php echo htmlspecialchars($user['email']); ?>"
                               readonly
                               style="background-color: var(--gray-100);">
                        <small class="text-muted">邮箱地址不可修改，如需更换请联系客服</small>
                    </div>

                    <!-- 昵称 -->
                    <div class="form-group">
                        <label for="nickname" class="form-label">
                            <i class="fas fa-user mr-2"></i>用户昵称 <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="nickname"
                               name="nickname"
                               class="form-control"
                               value="<?php echo htmlspecialchars($user['nickname'] ?? ''); ?>"
                               required
                               maxlength="20"
                               placeholder="请输入您的昵称">
                        <small class="text-muted">用于在系统中显示您的身份</small>
                    </div>

                    <!-- 手机号 -->
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone mr-2"></i>手机号码
                        </label>
                        <input type="text"
                               id="phone"
                               name="phone"
                               class="form-control"
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                               placeholder="请输入手机号码（选填）"
                               pattern="^1[3-9]\d{9}$">
                        <small class="text-muted">用于接收重要通知（可选）</small>
                    </div>

                    <div class="d-flex gap-3 justify-content-end">
                        <a href="/?r=dashboard" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i>
                            返回首页
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            保存修改
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 账户安全卡片 -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shield-alt"></i>
                    账户安全
                </h3>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1">登录密码</h5>
                        <small class="text-muted">定期更换密码可提高账户安全性</small>
                    </div>
                    <a href="/?r=change-password" class="btn btn-outline-primary">
                        <i class="fas fa-key"></i>
                        修改密码
                    </a>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">账户状态</h5>
                        <small class="text-muted">
                            <?php
                            $statusInfo = [
                                'normal' => ['正常', 'text-success', 'fas fa-check-circle'],
                                'frozen' => ['已冻结', 'text-warning', 'fas fa-pause-circle'],
                                'cancelled' => ['已注销', 'text-danger', 'fas fa-times-circle']
                            ];
                            $currentStatus = $statusInfo[$user['status']] ?? ['未知', 'text-muted', 'fas fa-question-circle'];
                            ?>
                            <span class="<?php echo $currentStatus[1]; ?>">
                                <i class="<?php echo $currentStatus[2]; ?>"></i>
                                <?php echo $currentStatus[0]; ?>
                            </span>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 账户统计 -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i>
                    账户统计
                </h3>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1">注册时间</h5>
                        <small class="text-muted"><?php echo date('Y年m月d日', strtotime($user['created_at'])); ?></small>
                    </div>
                    <div class="text-right">
                        <h5 class="mb-1">最后登录</h5>
                        <small class="text-muted">
                            <?php echo $user['last_login_at'] ? date('Y-m-d H:i', strtotime($user['last_login_at'])) : '从未登录'; ?>
                        </small>
                    </div>
                </div>

                <div class="d-flex justify-content-center">
                    <a href="/?r=stats" class="btn btn-outline-primary">
                        <i class="fas fa-chart-bar"></i>
                        查看详细统计
                    </a>
                </div>
            </div>
        </div>

        <!-- 危险操作 -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title text-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    危险操作
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-warning"></i>
                    <strong>注意：</strong>以下操作不可逆，请谨慎执行
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-danger">注销账户</h5>
                        <small class="text-muted">永久删除账户和所有相关数据</small>
                    </div>
                    <button type="button"
                            class="btn btn-danger"
                            onclick="confirmDeleteAccount()">
                        <i class="fas fa-user-times"></i>
                        注销账户
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- 注销确认模态框 -->
<div id="deleteAccountModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center;">
    <div class="card" style="max-width: 500px; width: 90%;">
        <div class="card-header">
            <h3 class="card-title text-danger">
                <i class="fas fa-exclamation-triangle"></i>
                确认注销账户
            </h3>
        </div>
        <div class="card-body">
            <div class="alert alert-danger">
                <i class="fas fa-warning"></i>
                <strong>警告：此操作不可撤销！</strong>
            </div>

            <p class="mb-3">注销账户后，以下数据将被永久删除：</p>
            <ul class="mb-3">
                <li>个人资料信息</li>
                <li>所有订阅记录</li>
                <li>提醒设置</li>
                <li>统计数据</li>
            </ul>

            <p class="mb-4"><strong>此操作无法恢复，请确认您真的要注销账户。</strong></p>

            <div class="d-flex gap-3 justify-content-end">
                <button type="button" class="btn btn-outline-primary" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i>
                    取消
                </button>
                <a href="/?r=delete-account"
                   class="btn btn-danger"
                   onclick="return confirm('最后确认：您确定要注销账户吗？这将删除所有数据且无法恢复！')">
                    <i class="fas fa-user-times"></i>
                    确认注销
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// 头像预览
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                // 替换默认头像div为img
                const img = document.createElement('img');
                img.id = 'avatarPreview';
                img.src = e.target.result;
                img.alt = 'avatar';
                img.style.cssText = 'width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--gray-200);';
                preview.parentNode.replaceChild(img, preview);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// 显示注销确认框
function confirmDeleteAccount() {
    document.getElementById('deleteAccountModal').style.display = 'flex';
}

// 关闭注销确认框
function closeDeleteModal() {
    document.getElementById('deleteAccountModal').style.display = 'none';
}

// 点击模态框外部关闭
document.getElementById('deleteAccountModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// 表单验证
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const nicknameInput = document.getElementById('nickname');
    const phoneInput = document.getElementById('phone');

    form.addEventListener('submit', function(e) {
        const nickname = nicknameInput.value.trim();
        const phone = phoneInput.value.trim();

        // 昵称验证
        if (nickname === '') {
            e.preventDefault();
            alert('昵称不能为空');
            nicknameInput.focus();
            return false;
        }

        if (nickname.length < 2 || nickname.length > 20) {
            e.preventDefault();
            alert('昵称长度应在2-20个字符之间');
            nicknameInput.focus();
            return false;
        }

        // 手机号验证（如果填写了）
        if (phone && !/^1[3-9]\d{9}$/.test(phone)) {
            e.preventDefault();
            alert('请输入正确的手机号码格式');
            phoneInput.focus();
            return false;
        }
    });

    // 实时验证昵称长度
    nicknameInput.addEventListener('input', function() {
        const length = this.value.length;
        const small = this.parentElement.querySelector('small');

        if (length > 20) {
            small.textContent = '昵称长度不能超过20个字符';
            small.className = 'text-danger';
        } else if (length < 2 && length > 0) {
            small.textContent = '昵称长度至少2个字符';
            small.className = 'text-warning';
        } else {
            small.textContent = '用于在系统中显示您的身份';
            small.className = 'text-muted';
        }
    });

    // 实时验证手机号
    phoneInput.addEventListener('input', function() {
        const phone = this.value.trim();
        const small = this.parentElement.querySelector('small');

        if (phone && !/^1[3-9]\d{9}$/.test(phone)) {
            small.textContent = '手机号格式不正确';
            small.className = 'text-danger';
        } else {
            small.textContent = '用于接收重要通知（可选）';
            small.className = 'text-muted';
        }
    });
});
</script>