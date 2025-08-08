<?php include __DIR__ . '/../layout/admin_header.php'; ?>

<style>
.user-management {
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--gray-900);
    margin: 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: var(--shadow-sm);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stat-icon.total { background: var(--blue-100); color: var(--blue-600); }
.stat-icon.normal { background: var(--green-100); color: var(--green-600); }
.stat-icon.frozen { background: var(--yellow-100); color: var(--yellow-600); }
.stat-icon.cancelled { background: var(--red-100); color: var(--red-600); }

.stat-content h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    color: var(--gray-900);
}

.stat-content p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.search-section {
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-sm);
}

.search-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
}

.form-group input,
.form-group select {
    padding: 0.75rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    font-size: 0.875rem;
    transition: var(--transition);
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-actions {
    display: flex;
    gap: 0.5rem;
}

.btn {
    padding: 0.75rem 1rem;
    border: none;
    border-radius: var(--border-radius);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    white-space: nowrap;
}

.btn-primary {
    background: var(--primary-color);
    color: var(--white);
}

.btn-primary:hover {
    background: var(--primary-600);
}

.btn-secondary {
    background: var(--gray-100);
    color: var(--gray-700);
    border: 1px solid var(--gray-300);
}

.btn-secondary:hover {
    background: var(--gray-200);
}

.btn-success {
    background: var(--success-color);
    color: var(--white);
}

.btn-warning {
    background: var(--warning-color);
    color: var(--white);
}

.btn-danger {
    background: var(--danger-color);
    color: var(--white);
}

.btn-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
}

.content-section {
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.section-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.section-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--gray-900);
    margin: 0;
}

.table-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.batch-actions {
    display: none;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    background: var(--blue-50);
    border-bottom: 1px solid var(--gray-200);
}

.batch-actions.show {
    display: flex;
}

.user-table {
    width: 100%;
    border-collapse: collapse;
}

.user-table th,
.user-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--gray-200);
}

.user-table th {
    background: var(--gray-50);
    font-weight: 600;
    color: var(--gray-700);
    font-size: 0.875rem;
}

.user-table tbody tr:hover {
    background: var(--gray-50);
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--primary-100);
    color: var(--primary-600);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
}

.status-normal {
    background: var(--green-100);
    color: var(--green-800);
}

.status-frozen {
    background: var(--yellow-100);
    color: var(--yellow-800);
}

.status-cancelled {
    background: var(--red-100);
    color: var(--red-800);
}

.user-actions {
    display: flex;
    gap: 0.5rem;
}

.pagination {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--gray-200);
    display: flex;
    justify-content: between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.pagination-info {
    color: var(--gray-600);
    font-size: 0.875rem;
}

.pagination-nav {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.pagination-nav a,
.pagination-nav span {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--gray-300);
    text-decoration: none;
    color: var(--gray-700);
    border-radius: var(--border-radius);
    font-size: 0.875rem;
    min-width: 40px;
    text-align: center;
}

.pagination-nav a:hover {
    background: var(--gray-100);
}

.pagination-nav .current {
    background: var(--primary-color);
    color: var(--white);
    border-color: var(--primary-color);
}

.pagination-nav .disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.per-page-selector {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-form {
        grid-template-columns: 1fr;
    }
    
    .user-table {
        font-size: 0.875rem;
    }
    
    .user-table th,
    .user-table td {
        padding: 0.75rem 0.5rem;
    }
    
    .pagination {
        flex-direction: column;
        align-items: stretch;
    }
    
    .pagination-nav {
        justify-content: center;
    }
}
</style>

<div class="user-management">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-users"></i>
            用户管理
        </h1>
        <div class="table-actions">
            <a href="/admin.php?r=export-users<?php echo $search || $status || $dateFrom || $dateTo ? '&' . http_build_query(['search' => $search, 'status' => $status, 'date_from' => $dateFrom, 'date_to' => $dateTo]) : ''; ?>" 
               class="btn btn-secondary">
                <i class="fas fa-download"></i>
                导出数据
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($totalUsers); ?></h3>
                <p>总用户数</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon normal">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($statusStats['normal'] ?? 0); ?></h3>
                <p>正常用户</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon frozen">
                <i class="fas fa-user-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($statusStats['frozen'] ?? 0); ?></h3>
                <p>冻结用户</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon cancelled">
                <i class="fas fa-user-times"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($statusStats['cancelled'] ?? 0); ?></h3>
                <p>注销用户</p>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="search-section">
        <form method="GET" action="/admin.php" class="search-form">
            <input type="hidden" name="r" value="users">
            
            <div class="form-group">
                <label for="search">搜索用户</label>
                <input type="text" id="search" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="邮箱、昵称或用户ID">
            </div>
            
            <div class="form-group">
                <label for="status">用户状态</label>
                <select id="status" name="status">
                    <option value="">全部状态</option>
                    <option value="normal" <?php echo $status === 'normal' ? 'selected' : ''; ?>>正常</option>
                    <option value="frozen" <?php echo $status === 'frozen' ? 'selected' : ''; ?>>冻结</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>注销</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="date_from">注册日期从</label>
                <input type="date" id="date_from" name="date_from" 
                       value="<?php echo htmlspecialchars($dateFrom); ?>">
            </div>
            
            <div class="form-group">
                <label for="date_to">注册日期至</label>
                <input type="date" id="date_to" name="date_to" 
                       value="<?php echo htmlspecialchars($dateTo); ?>">
            </div>
            
            <div class="form-group">
                <label for="per_page">每页显示</label>
                <select id="per_page" name="per_page">
                    <option value="10" <?php echo $perPage === 10 ? 'selected' : ''; ?>>10条</option>
                    <option value="20" <?php echo $perPage === 20 ? 'selected' : ''; ?>>20条</option>
                    <option value="50" <?php echo $perPage === 50 ? 'selected' : ''; ?>>50条</option>
                    <option value="100" <?php echo $perPage === 100 ? 'selected' : ''; ?>>100条</option>
                </select>
            </div>
            
            <div class="search-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    搜索
                </button>
                <a href="/admin.php?r=users" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    清除
                </a>
            </div>
        </form>
    </div>

    <!-- User List -->
    <div class="content-section">
        <div class="section-header">
            <h2 class="section-title">
                用户列表
                <?php if ($totalUsers > 0): ?>
                    <span style="font-weight: normal; color: var(--gray-600);">
                        (显示第 <?php echo number_format($startRecord); ?> - <?php echo number_format($endRecord); ?> 条，共 <?php echo number_format($totalUsers); ?> 条)
                    </span>
                <?php endif; ?>
            </h2>
        </div>

        <!-- Batch Actions -->
        <div class="batch-actions" id="batchActions">
            <span>已选择 <strong id="selectedCount">0</strong> 个用户：</span>
            <button type="button" onclick="batchAction('activate')" class="btn btn-success btn-sm">
                <i class="fas fa-user-check"></i>
                激活
            </button>
            <button type="button" onclick="batchAction('freeze')" class="btn btn-warning btn-sm">
                <i class="fas fa-user-clock"></i>
                冻结
            </button>
            <button type="button" onclick="batchAction('cancel')" class="btn btn-danger btn-sm">
                <i class="fas fa-user-times"></i>
                注销
            </button>
        </div>

        <?php if (empty($users)): ?>
            <div style="padding: 3rem; text-align: center; color: var(--gray-500);">
                <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                <p>没有找到符合条件的用户</p>
            </div>
        <?php else: ?>
            <form id="batchForm" method="POST" action="/admin.php?r=user-batch">
                <!-- Preserve search parameters -->
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                <input type="hidden" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>">
                <input type="hidden" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>">
                <input type="hidden" name="per_page" value="<?php echo $perPage; ?>">
                <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
                <input type="hidden" name="action" id="batchActionInput">

                <table class="user-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>用户</th>
                            <th>邮箱</th>
                            <th>状态</th>
                            <th>注册时间</th>
                            <th>最后登录</th>
                            <th style="width: 120px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="user_ids[]" value="<?php echo $user['id']; ?>" 
                                       class="user-checkbox" onchange="updateBatchActions()">
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($user['nickname'] ?: $user['email'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 500; color: var(--gray-900);">
                                            <?php echo htmlspecialchars($user['nickname'] ?: '未设置'); ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--gray-500);">
                                            ID: <?php echo $user['id']; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="color: var(--gray-700);">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $user['status']; ?>">
                                    <?php 
                                    $statusLabels = [
                                        'normal' => '正常',
                                        'frozen' => '冻结', 
                                        'cancelled' => '注销'
                                    ];
                                    echo $statusLabels[$user['status']] ?? $user['status'];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <span style="color: var(--gray-600); font-size: 0.875rem;">
                                    <?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?>
                                </span>
                            </td>
                            <td>
                                <span style="color: var(--gray-600); font-size: 0.875rem;">
                                    <?php echo $user['last_login_at'] ? date('Y-m-d H:i', strtotime($user['last_login_at'])) : '从未登录'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="user-actions">
                                    <a href="/admin.php?r=user-edit&id=<?php echo $user['id']; ?>" 
                                       class="btn btn-secondary btn-sm" title="编辑用户">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['status'] !== 'cancelled'): ?>
                                    <a href="/admin.php?r=user-delete&id=<?php echo $user['id']; ?>" 
                                       class="btn btn-danger btn-sm" title="注销用户"
                                       onclick="return confirm('确认注销该用户吗？此操作将注销用户账户。');">
                                        <i class="fas fa-user-times"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <div class="pagination-info">
                显示第 <?php echo number_format($startRecord); ?> - <?php echo number_format($endRecord); ?> 条，共 <?php echo number_format($totalUsers); ?> 条记录
            </div>
            
            <div class="pagination-nav">
                <?php
                $params = [
                    'r' => 'users',
                    'search' => $search,
                    'status' => $status,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'per_page' => $perPage
                ];
                $params = array_filter($params);
                ?>
                
                <?php if ($currentPage > 1): ?>
                    <a href="/admin.php?<?php echo http_build_query(array_merge($params, ['page' => 1])); ?>">首页</a>
                    <a href="/admin.php?<?php echo http_build_query(array_merge($params, ['page' => $currentPage - 1])); ?>">上一页</a>
                <?php else: ?>
                    <span class="disabled">首页</span>
                    <span class="disabled">上一页</span>
                <?php endif; ?>
                
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                
                for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                    <?php if ($i == $currentPage): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="/admin.php?<?php echo http_build_query(array_merge($params, ['page' => $i])); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($currentPage < $totalPages): ?>
                    <a href="/admin.php?<?php echo http_build_query(array_merge($params, ['page' => $currentPage + 1])); ?>">下一页</a>
                    <a href="/admin.php?<?php echo http_build_query(array_merge($params, ['page' => $totalPages])); ?>">末页</a>
                <?php else: ?>
                    <span class="disabled">下一页</span>
                    <span class="disabled">末页</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBatchActions();
}

function updateBatchActions() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    const batchActions = document.getElementById('batchActions');
    const selectedCount = document.getElementById('selectedCount');
    
    selectedCount.textContent = checkboxes.length;
    
    if (checkboxes.length > 0) {
        batchActions.classList.add('show');
    } else {
        batchActions.classList.remove('show');
    }
    
    // Update select all checkbox
    const allCheckboxes = document.querySelectorAll('.user-checkbox');
    const selectAll = document.getElementById('selectAll');
    selectAll.indeterminate = checkboxes.length > 0 && checkboxes.length < allCheckboxes.length;
    selectAll.checked = checkboxes.length === allCheckboxes.length && allCheckboxes.length > 0;
}

function batchAction(action) {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    
    if (checkboxes.length === 0) {
        alert('请选择要操作的用户');
        return;
    }
    
    const actionLabels = {
        'activate': '激活',
        'freeze': '冻结', 
        'cancel': '注销'
    };
    
    const label = actionLabels[action];
    
    if (!confirm(`确认${label} ${checkboxes.length} 个用户吗？`)) {
        return;
    }
    
    document.getElementById('batchActionInput').value = action;
    document.getElementById('batchForm').submit();
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+A to select all
    if (e.ctrlKey && e.key === 'a' && e.target.tagName !== 'INPUT') {
        e.preventDefault();
        document.getElementById('selectAll').checked = true;
        toggleSelectAll();
    }
    
    // Escape to clear selection
    if (e.key === 'Escape') {
        document.getElementById('selectAll').checked = false;
        toggleSelectAll();
    }
});

// Initialize batch actions visibility
document.addEventListener('DOMContentLoaded', function() {
    updateBatchActions();
});
</script>

<?php include __DIR__ . '/../layout/admin_footer.php'; ?>