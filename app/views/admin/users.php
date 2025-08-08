<?php include __DIR__ . '/../layout/admin_header.php'; ?>

<h1 class="page-title">
    <i class="fas fa-users"></i>
    用户管理
</h1>

<!-- 用户统计卡片 -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon primary">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($totalUsers); ?></div>
        <div class="stat-label">用户总数</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon success">
                <i class="fas fa-user-check"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($activeUsers); ?></div>
        <div class="stat-label">正常用户</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon warning">
                <i class="fas fa-user-clock"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($frozenUsers); ?></div>
        <div class="stat-label">冻结用户</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon danger">
                <i class="fas fa-user-times"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($cancelledUsers); ?></div>
        <div class="stat-label">注销用户</div>
    </div>
</div>

<!-- 用户管理操作 -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-search"></i>
            搜索与操作
        </h3>
    </div>
    <div class="card-body">
        <div class="d-flex gap-3 justify-content-between flex-wrap align-items-center">
            <div class="d-flex gap-3 flex-wrap">
                <input type="text" id="userSearch" class="form-control" placeholder="搜索用户邮箱或昵称..." style="min-width: 200px;">
                <select id="statusFilter" class="form-control">
                    <option value="">所有状态</option>
                    <option value="normal">正常</option>
                    <option value="frozen">冻结</option>
                    <option value="cancelled">注销</option>
                </select>
            </div>
            <div class="d-flex gap-2">
                <a href="/admin.php?r=export-users" class="btn btn-outline-primary">
                    <i class="fas fa-download"></i>
                    导出数据
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 用户列表 -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            用户列表
        </h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table" id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>邮箱</th>
                        <th>昵称</th>
                        <th>状态</th>
                        <th>注册时间</th>
                        <th>最后登录</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr data-status="<?php echo htmlspecialchars($u['status']); ?>" data-search="<?php echo htmlspecialchars(strtolower($u['email'] . ' ' . $u['nickname'])); ?>">
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['nickname']); ?></td>
                        <td>
                            <?php 
                            $statusInfo = [
                                'normal' => ['正常', 'text-success', 'fas fa-check-circle'],
                                'frozen' => ['冻结', 'text-warning', 'fas fa-pause-circle'],
                                'cancelled' => ['注销', 'text-danger', 'fas fa-times-circle']
                            ];
                            $info = $statusInfo[$u['status']] ?? ['未知', 'text-muted', 'fas fa-question-circle'];
                            ?>
                            <span class="<?php echo $info[1]; ?>">
                                <i class="<?php echo $info[2]; ?>"></i>
                                <?php echo $info[0]; ?>
                            </span>
                        </td>
                        <td><?php echo $u['created_at'] ? date('Y-m-d H:i', strtotime($u['created_at'])) : '-'; ?></td>
                        <td><?php echo $u['last_login_at'] ? date('Y-m-d H:i', strtotime($u['last_login_at'])) : '从未登录'; ?></td>
                        <td class="table-actions">
                            <div class="d-flex gap-2">
                                <a href="/admin.php?r=user-edit&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                    编辑
                                </a>
                                <?php if ($u['status'] !== 'cancelled'): ?>
                                <a href="/admin.php?r=user-delete&id=<?php echo $u['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('确认注销该用户吗？');">
                                    <i class="fas fa-user-times"></i>
                                    注销
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// 搜索和筛选功能
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearch');
    const statusFilter = document.getElementById('statusFilter');
    const tableRows = document.querySelectorAll('#usersTable tbody tr');

    function filterUsers() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;

        tableRows.forEach(row => {
            const searchData = row.getAttribute('data-search');
            const statusData = row.getAttribute('data-status');
            
            const matchesSearch = !searchTerm || searchData.includes(searchTerm);
            const matchesStatus = !statusValue || statusData === statusValue;
            
            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterUsers);
    statusFilter.addEventListener('change', filterUsers);
});
</script>

<?php include __DIR__ . '/../layout/admin_footer.php'; ?>