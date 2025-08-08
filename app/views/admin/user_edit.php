<?php include __DIR__ . '/../layout/admin_header.php'; ?>

<h1 class="page-title">
    <i class="fas fa-user-edit"></i>
    编辑用户
</h1>

<!-- 用户信息概览 -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-info-circle"></i>
            用户信息概览
        </h3>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center gap-4 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-id-badge text-primary"></i>
                <span><strong>用户ID：</strong><?php echo $user['id']; ?></span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-envelope text-success"></i>
                <span><strong>邮箱：</strong><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-calendar text-warning"></i>
                <span><strong>注册时间：</strong><?php echo $user['created_at'] ? date('Y-m-d H:i:s', strtotime($user['created_at'])) : '未知'; ?></span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-clock text-info"></i>
                <span><strong>最后登录：</strong><?php echo $user['last_login_at'] ? date('Y-m-d H:i:s', strtotime($user['last_login_at'])) : '从未登录'; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- 编辑用户表单 -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-edit"></i>
            编辑用户信息
        </h3>
    </div>
    <div class="card-body">
        <form method="post" action="/admin.php?r=user-edit&id=<?php echo $user['id']; ?>">
            <div class="d-flex gap-4 flex-wrap">
                <div style="flex: 1; min-width: 250px;">
                    <div class="form-group">
                        <label for="nickname" class="form-label">
                            <i class="fas fa-user"></i>
                            昵称
                        </label>
                        <input type="text" 
                               id="nickname" 
                               name="nickname" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($user['nickname']); ?>" 
                               required
                               placeholder="请输入用户昵称">
                        <div class="form-help">
                            用户显示名称，支持中英文和数字
                        </div>
                    </div>
                </div>
                
                <div style="flex: 1; min-width: 250px;">
                    <div class="form-group">
                        <label for="status" class="form-label">
                            <i class="fas fa-toggle-on"></i>
                            账户状态
                        </label>
                        <select id="status" name="status" class="form-control">
                            <?php
                            $statuses = [
                                'normal' => ['正常', 'text-success', '用户可以正常使用所有功能'],
                                'frozen' => ['冻结', 'text-warning', '用户账户被冻结，无法登录'],
                                'cancelled' => ['注销', 'text-danger', '用户账户已被注销，无法恢复']
                            ];
                            foreach ($statuses as $key => $info):
                                $selected = $user['status'] === $key ? 'selected' : '';
                                echo "<option value='{$key}' {$selected}>{$info[0]} - {$info[2]}</option>";
                            endforeach;
                            ?>
                        </select>
                        <div class="form-help">
                            请谨慎修改用户状态，冻结和注销操作会影响用户使用
                        </div>
                    </div>
                </div>
            </div>

            <!-- 状态说明 -->
            <div class="card-body bg-light mt-3">
                <h6 class="mb-3">
                    <i class="fas fa-info-circle text-primary"></i>
                    状态说明
                </h6>
                <div class="d-flex gap-3 flex-wrap">
                    <?php foreach ($statuses as $key => $info): ?>
                    <div style="flex: 1; min-width: 200px;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="<?php echo $info[1]; ?>">
                                <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                <strong><?php echo $info[0]; ?></strong>
                            </span>
                        </div>
                        <small class="text-muted"><?php echo $info[2]; ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 操作按钮 -->
            <div class="d-flex gap-3 justify-content-end mt-4">
                <a href="/admin.php?r=users" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                    返回列表
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    保存修改
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 危险操作区域 -->
<div class="card mt-4 border-danger">
    <div class="card-header bg-danger text-white">
        <h3 class="card-title mb-0">
            <i class="fas fa-exclamation-triangle"></i>
            危险操作
        </h3>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h6>注销用户账户</h6>
                <p class="text-muted mb-0">注销后用户将无法登录，此操作无法撤销</p>
            </div>
            <?php if ($user['status'] !== 'cancelled'): ?>
            <a href="/admin.php?r=user-delete&id=<?php echo $user['id']; ?>" 
               class="btn btn-danger" 
               onclick="return confirm('确认注销该用户账户吗？此操作无法撤销！');">
                <i class="fas fa-user-times"></i>
                注销用户
            </a>
            <?php else: ?>
            <span class="text-muted">
                <i class="fas fa-check"></i>
                用户已注销
            </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// 表单验证和状态提示
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const form = document.querySelector('form');
    
    // 状态改变提示
    statusSelect.addEventListener('change', function() {
        const value = this.value;
        const statusInfo = {
            'normal': '正常状态，用户可以正常使用所有功能',
            'frozen': '冻结状态，用户将无法登录系统',
            'cancelled': '注销状态，用户账户将被永久禁用'
        };
        
        if (statusInfo[value]) {
            // 可以添加状态变更的确认提示
            console.log('状态变更为：' + statusInfo[value]);
        }
    });
    
    // 表单提交前确认
    form.addEventListener('submit', function(e) {
        const nickname = document.getElementById('nickname').value.trim();
        if (!nickname) {
            e.preventDefault();
            alert('请输入用户昵称');
            return false;
        }
        
        if (statusSelect.value === 'cancelled') {
            if (!confirm('您正在将用户状态设置为"注销"，这将永久禁用该用户账户。确认继续吗？')) {
                e.preventDefault();
                return false;
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../layout/admin_footer.php'; ?>