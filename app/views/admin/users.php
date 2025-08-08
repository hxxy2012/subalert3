<?php include __DIR__ . '/../layout/admin_header.php'; ?>

<!-- Compact User Filtering Interface -->
<div class="page-header">
    <h2>用户列表</h2>
    <div class="page-actions">
        <a href="/admin.php?r=export-users" class="btn btn-outline">
            <i class="fas fa-download"></i>
            导出用户
        </a>
    </div>
</div>

<!-- Compact Filter Bar -->
<div class="filter-bar">
    <form method="GET" action="/admin.php" class="filter-form" id="userFilterForm">
        <input type="hidden" name="r" value="users">
        
        <!-- Main Filter Row -->
        <div class="filter-row">
            <!-- Search Input -->
            <div class="filter-item search-item">
                <div class="input-group">
                    <i class="fas fa-search input-icon"></i>
                    <input type="text" 
                           name="search" 
                           id="searchInput"
                           placeholder="搜索用户邮箱或昵称..." 
                           value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>"
                           class="form-input">
                    <?php if (!empty($filters['search'])): ?>
                        <button type="button" class="clear-btn" onclick="clearSearch()">
                            <i class="fas fa-times"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Status Filter Tags -->
            <div class="filter-item status-tags">
                <span class="filter-label">状态:</span>
                <button type="button" class="status-tag <?php echo empty($filters['status']) ? 'active' : ''; ?>" 
                        onclick="setStatusFilter('')">
                    全部 <span class="count"><?php echo array_sum($statusCounts); ?></span>
                </button>
                <button type="button" class="status-tag normal <?php echo $filters['status'] === 'normal' ? 'active' : ''; ?>" 
                        onclick="setStatusFilter('normal')">
                    正常 <span class="count"><?php echo $statusCounts['normal']; ?></span>
                </button>
                <button type="button" class="status-tag frozen <?php echo $filters['status'] === 'frozen' ? 'active' : ''; ?>" 
                        onclick="setStatusFilter('frozen')">
                    冻结 <span class="count"><?php echo $statusCounts['frozen']; ?></span>
                </button>
                <button type="button" class="status-tag cancelled <?php echo $filters['status'] === 'cancelled' ? 'active' : ''; ?>" 
                        onclick="setStatusFilter('cancelled')">
                    注销 <span class="count"><?php echo $statusCounts['cancelled']; ?></span>
                </button>
            </div>
            
            <!-- Advanced Toggle -->
            <div class="filter-item">
                <button type="button" class="advanced-toggle" onclick="toggleAdvancedFilters()">
                    <i class="fas fa-sliders-h"></i>
                    高级筛选
                    <i class="fas fa-chevron-down arrow" id="advancedArrow"></i>
                </button>
            </div>
            
            <!-- Clear Filters -->
            <?php if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['date_from']) || !empty($filters['date_to'])): ?>
                <div class="filter-item">
                    <button type="button" class="clear-all-btn" onclick="clearAllFilters()">
                        <i class="fas fa-times"></i>
                        清除筛选
                    </button>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Advanced Filters (Collapsible) -->
        <div class="advanced-filters" id="advancedFilters" style="display: <?php echo (!empty($filters['date_from']) || !empty($filters['date_to'])) ? 'block' : 'none'; ?>">
            <div class="filter-row">
                <div class="filter-item">
                    <label class="filter-label">注册时间:</label>
                    <div class="date-range">
                        <input type="date" 
                               name="date_from" 
                               value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>"
                               class="form-input date-input">
                        <span class="date-separator">至</span>
                        <input type="date" 
                               name="date_to" 
                               value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>"
                               class="form-input date-input">
                    </div>
                </div>
                <div class="filter-item">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i>
                        应用筛选
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Hidden Status Input -->
        <input type="hidden" name="status" id="statusInput" value="<?php echo htmlspecialchars($filters['status'] ?? ''); ?>">
    </form>
</div>

<!-- Active Filters Display -->
<?php if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['date_from']) || !empty($filters['date_to'])): ?>
    <div class="active-filters">
        <span class="active-filters-label">当前筛选:</span>
        <?php if (!empty($filters['search'])): ?>
            <span class="active-filter">
                搜索: "<?php echo htmlspecialchars($filters['search']); ?>"
                <button onclick="clearSearch()"><i class="fas fa-times"></i></button>
            </span>
        <?php endif; ?>
        <?php if (!empty($filters['status'])): ?>
            <span class="active-filter">
                状态: <?php echo $filters['status'] === 'normal' ? '正常' : ($filters['status'] === 'frozen' ? '冻结' : '注销'); ?>
                <button onclick="setStatusFilter('')"><i class="fas fa-times"></i></button>
            </span>
        <?php endif; ?>
        <?php if (!empty($filters['date_from']) || !empty($filters['date_to'])): ?>
            <span class="active-filter">
                注册时间: <?php echo $filters['date_from'] ?? '不限'; ?> ~ <?php echo $filters['date_to'] ?? '不限'; ?>
                <button onclick="clearDateFilters()"><i class="fas fa-times"></i></button>
            </span>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- User Data Table -->
<div class="data-table-container">
    <table class="data-table">
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
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7" class="no-data">
                        <div class="no-data-content">
                            <i class="fas fa-users"></i>
                            <p>没有找到符合条件的用户</p>
                            <?php if (!empty($filters['search']) || !empty($filters['status'])): ?>
                                <button onclick="clearAllFilters()" class="btn btn-outline">清除筛选条件</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo $u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo htmlspecialchars($u['nickname']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $u['status']; ?>">
                            <?php echo $u['status'] === 'normal' ? '正常' : ($u['status'] === 'frozen' ? '冻结' : '注销'); ?>
                        </span>
                    </td>
                    <td><?php echo date('Y-m-d H:i', strtotime($u['created_at'])); ?></td>
                    <td><?php echo $u['last_login_at'] ? date('Y-m-d H:i', strtotime($u['last_login_at'])) : '从未登录'; ?></td>
                    <td class="actions">
                        <a href="/admin.php?r=user-edit&id=<?php echo $u['id']; ?>" class="action-btn edit-btn" title="编辑">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php if ($u['status'] !== 'cancelled'): ?>
                            <a href="/admin.php?r=user-delete&id=<?php echo $u['id']; ?>" 
                               class="action-btn delete-btn" 
                               title="注销"
                               onclick="return confirm('确认注销该用户吗？');">
                                <i class="fas fa-user-times"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Filtering & Interaction Scripts -->
<style>
/* Compact Filter Bar Styles */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray-200);
}

.page-header h2 {
    margin: 0;
    color: var(--gray-900);
    font-size: 1.5rem;
    font-weight: 600;
}

.page-actions {
    display: flex;
    gap: 0.75rem;
}

.filter-bar {
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius);
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: var(--shadow);
}

.filter-form {
    margin: 0;
}

.filter-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.filter-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.search-item {
    flex: 1;
    min-width: 300px;
}

.input-group {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}

.input-icon {
    position: absolute;
    left: 0.75rem;
    color: var(--gray-400);
    z-index: 1;
}

.form-input {
    width: 100%;
    padding: 0.5rem 0.75rem 0.5rem 2.25rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    font-size: 0.875rem;
    transition: var(--transition);
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.clear-btn {
    position: absolute;
    right: 0.5rem;
    background: none;
    border: none;
    color: var(--gray-400);
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 50%;
    transition: var(--transition);
}

.clear-btn:hover {
    color: var(--gray-600);
    background: var(--gray-100);
}

.filter-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--gray-600);
    white-space: nowrap;
}

.status-tags {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.status-tag {
    background: var(--gray-100);
    border: 1px solid var(--gray-200);
    padding: 0.375rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.status-tag:hover {
    background: var(--gray-200);
}

.status-tag.active {
    background: var(--primary-color);
    color: var(--white);
    border-color: var(--primary-color);
}

.status-tag.normal.active {
    background: var(--success-color);
    border-color: var(--success-color);
}

.status-tag.frozen.active {
    background: var(--warning-color);
    border-color: var(--warning-color);
}

.status-tag.cancelled.active {
    background: var(--danger-color);
    border-color: var(--danger-color);
}

.count {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.125rem 0.375rem;
    border-radius: 0.75rem;
    font-size: 0.6875rem;
    font-weight: 600;
}

.status-tag:not(.active) .count {
    background: var(--gray-300);
    color: var(--gray-600);
}

.advanced-toggle {
    background: none;
    border: 1px solid var(--gray-300);
    padding: 0.5rem 0.75rem;
    border-radius: var(--border-radius);
    font-size: 0.875rem;
    color: var(--gray-600);
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.advanced-toggle:hover {
    background: var(--gray-50);
    border-color: var(--gray-400);
}

.arrow {
    transition: transform 0.2s ease;
}

.arrow.rotated {
    transform: rotate(180deg);
}

.clear-all-btn {
    background: none;
    border: 1px solid var(--danger-color);
    color: var(--danger-color);
    padding: 0.5rem 0.75rem;
    border-radius: var(--border-radius);
    font-size: 0.875rem;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.clear-all-btn:hover {
    background: var(--danger-color);
    color: var(--white);
}

.advanced-filters {
    border-top: 1px solid var(--gray-200);
    margin-top: 1rem;
    padding-top: 1rem;
}

.date-range {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.date-input {
    width: 140px;
    padding: 0.5rem 0.75rem;
}

.date-separator {
    color: var(--gray-500);
    font-size: 0.875rem;
}

.active-filters {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.active-filters-label {
    font-size: 0.875rem;
    color: var(--gray-600);
    font-weight: 500;
}

.active-filter {
    background: var(--primary-light);
    color: var(--primary-color);
    padding: 0.25rem 0.5rem;
    border-radius: var(--border-radius);
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.active-filter button {
    background: none;
    border: none;
    color: var(--primary-color);
    cursor: pointer;
    padding: 0;
    border-radius: 50%;
    width: 1rem;
    height: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.active-filter button:hover {
    background: rgba(59, 130, 246, 0.2);
}

/* Data Table Styles */
.data-table-container {
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow);
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.data-table th {
    background: var(--gray-50);
    color: var(--gray-700);
    font-weight: 600;
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid var(--gray-200);
    white-space: nowrap;
}

.data-table td {
    padding: 0.75rem;
    border-bottom: 1px solid var(--gray-100);
    vertical-align: middle;
}

.data-table tbody tr:hover {
    background: var(--gray-50);
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.75rem;
    font-size: 0.6875rem;
    font-weight: 600;
    text-align: center;
    display: inline-block;
    min-width: 60px;
}

.status-normal {
    background: var(--success-light);
    color: var(--success-color);
}

.status-frozen {
    background: var(--warning-light);
    color: var(--warning-color);
}

.status-cancelled {
    background: var(--danger-light);
    color: var(--danger-color);
}

.actions {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: var(--border-radius);
    text-decoration: none;
    transition: var(--transition);
    font-size: 0.75rem;
}

.edit-btn {
    background: var(--gray-100);
    color: var(--gray-600);
}

.edit-btn:hover {
    background: var(--primary-color);
    color: var(--white);
}

.delete-btn {
    background: var(--gray-100);
    color: var(--gray-600);
}

.delete-btn:hover {
    background: var(--danger-color);
    color: var(--white);
}

.no-data {
    text-align: center;
    padding: 3rem 1rem;
}

.no-data-content {
    color: var(--gray-500);
}

.no-data-content i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.no-data-content p {
    margin-bottom: 1rem;
    font-size: 1rem;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border: 1px solid transparent;
    border-radius: var(--border-radius);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    white-space: nowrap;
}

.btn-primary {
    background: var(--primary-color);
    color: var(--white);
}

.btn-primary:hover {
    background: var(--primary-dark);
}

.btn-outline {
    border-color: var(--gray-300);
    color: var(--gray-700);
}

.btn-outline:hover {
    background: var(--gray-50);
    border-color: var(--gray-400);
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .filter-row {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
    }
    
    .search-item {
        min-width: auto;
    }
    
    .status-tags {
        justify-content: flex-start;
    }
    
    .advanced-filters .filter-row {
        flex-direction: column;
    }
    
    .date-range {
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
    }
    
    .date-input {
        width: 100%;
    }
    
    .data-table-container {
        overflow-x: auto;
    }
    
    .data-table {
        min-width: 600px;
    }
}
</style>

<script>
// Real-time search functionality
let searchTimeout;
const searchInput = document.getElementById('searchInput');

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (this.value.length >= 2 || this.value.length === 0) {
            submitForm();
        }
    }, 300); // 300ms delay for real-time search
});

// Form submission function
function submitForm() {
    document.getElementById('userFilterForm').submit();
}

// Status filter functions
function setStatusFilter(status) {
    document.getElementById('statusInput').value = status;
    submitForm();
}

// Clear functions
function clearSearch() {
    document.getElementById('searchInput').value = '';
    submitForm();
}

function clearAllFilters() {
    const form = document.getElementById('userFilterForm');
    form.elements.search.value = '';
    form.elements.status.value = '';
    form.elements.date_from.value = '';
    form.elements.date_to.value = '';
    submitForm();
}

function clearDateFilters() {
    document.querySelector('input[name="date_from"]').value = '';
    document.querySelector('input[name="date_to"]').value = '';
    submitForm();
}

// Advanced filters toggle
function toggleAdvancedFilters() {
    const advanced = document.getElementById('advancedFilters');
    const arrow = document.getElementById('advancedArrow');
    
    if (advanced.style.display === 'none' || !advanced.style.display) {
        advanced.style.display = 'block';
        arrow.classList.add('rotated');
    } else {
        advanced.style.display = 'none';
        arrow.classList.remove('rotated');
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+F to focus search
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        document.getElementById('searchInput').focus();
    }
    
    // Escape to clear search when focused
    if (e.key === 'Escape' && document.activeElement === searchInput) {
        clearSearch();
    }
});

// Initialize advanced filters state
document.addEventListener('DOMContentLoaded', function() {
    const hasDateFilters = document.querySelector('input[name="date_from"]').value || 
                          document.querySelector('input[name="date_to"]').value;
    if (hasDateFilters) {
        document.getElementById('advancedArrow').classList.add('rotated');
    }
});
</script>

<?php include __DIR__ . '/../layout/admin_footer.php'; ?>