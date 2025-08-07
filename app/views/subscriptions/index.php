<?php
// 分页参数
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 3; // 每页显示10条 (从3改回10)
$offset = ($page - 1) * $perPage;

// 构建查询条件
$whereConditions = ['user_id = ?'];
$params = [current_user()['id']];

// 类型筛选
if (!empty($_GET['type'])) {
    $whereConditions[] = 'type = ?';
    $params[] = $_GET['type'];
}

// 状态筛选
if (!empty($_GET['status'])) {
    $whereConditions[] = 'status = ?';
    $params[] = $_GET['status'];
} else {
    $whereConditions[] = 'status != ?';
    $params[] = 'deleted';
}

// 构建完整查询
$whereClause = implode(' AND ', $whereConditions);

// 获取总数
$pdo = \App\Models\DB::getConnection();
$countQuery = "SELECT COUNT(*) as total FROM subscriptions WHERE {$whereClause}";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalSubscriptions = $countStmt->fetch()['total'];

// 计算分页信息
$totalPages = ceil($totalSubscriptions / $perPage);
$hasNextPage = $page < $totalPages;
$hasPrevPage = $page > 1;

// 获取当前页数据
$dataQuery = "SELECT * FROM subscriptions
              WHERE {$whereClause}
              ORDER BY expire_at ASC
              LIMIT {$perPage} OFFSET {$offset}";
$dataStmt = $pdo->prepare($dataQuery);
$dataStmt->execute($params);
$subscriptions = $dataStmt->fetchAll();
?>

<!-- 操作栏 -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <a href="/?r=subscription-create" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    添加订阅
                </a>
                <a href="/?r=subscriptions-deleted" class="btn btn-outline-primary">
                    <i class="fas fa-trash"></i>
                    回收站
                </a>
            </div>

            <!-- 筛选器 -->
            <div class="d-flex gap-2 flex-wrap">
                <select class="form-control" style="width: auto;" onchange="filterSubscriptions(this)">
                    <option value="">所有类型</option>
                    <option value="video" <?php echo ($_GET['type'] ?? '') === 'video' ? 'selected' : ''; ?>>📺 视频</option>
                    <option value="music" <?php echo ($_GET['type'] ?? '') === 'music' ? 'selected' : ''; ?>>🎵 音乐</option>
                    <option value="software" <?php echo ($_GET['type'] ?? '') === 'software' ? 'selected' : ''; ?>>💻 软件</option>
                    <option value="communication" <?php echo ($_GET['type'] ?? '') === 'communication' ? 'selected' : ''; ?>>💬 通讯</option>
                    <option value="other" <?php echo ($_GET['type'] ?? '') === 'other' ? 'selected' : ''; ?>>📦 其他</option>
                </select>

                <select class="form-control" style="width: auto;" onchange="filterStatus(this)">
                    <option value="">所有状态</option>
                    <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>正常</option>
                    <option value="paused" <?php echo ($_GET['status'] ?? '') === 'paused' ? 'selected' : ''; ?>>暂停</option>
                    <option value="cancelled" <?php echo ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>已取消</option>
                    <option value="expired" <?php echo ($_GET['status'] ?? '') === 'expired' ? 'selected' : ''; ?>>已过期</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- 订阅列表 -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($subscriptions)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>暂无订阅</h3>
                <?php if ($page > 1): ?>
                    <p>当前页面没有数据，请尝试其他页面</p>
                    <a href="/?r=subscriptions&page=1" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i>
                        返回第一页
                    </a>
                <?php else: ?>
                    <p>您还没有添加任何订阅服务</p>
                    <a href="/?r=subscription-create" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        添加第一个订阅
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <form method="post" action="/index.php?r=subscriptions" id="batchForm">
                <!-- 保留分页和筛选参数 -->
                <input type="hidden" name="page" value="<?php echo $page; ?>">
                <input type="hidden" name="type" value="<?php echo htmlspecialchars($_GET['type'] ?? ''); ?>">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($_GET['status'] ?? ''); ?>">

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                </th>
                                <th>订阅名称</th>
                                <th>类型</th>
                                <th>价格</th>
                                <th>周期</th>
                                <th>到期时间</th>
                                <th>状态</th>
                                <th width="180">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscriptions as $sub): ?>
                                <?php
                                // 计算剩余天数和状态样式
                                $expireDate = new DateTime($sub['expire_at']);
                                $today = new DateTime();
                                $diff = $today->diff($expireDate);
                                $daysLeft = $expireDate > $today ? $diff->days : -$diff->days;

                                $statusClass = '';
                                $statusText = '';
                                switch ($sub['status']) {
                                    case 'active':
                                        $statusClass = 'text-success';
                                        $statusText = '正常';
                                        break;
                                    case 'paused':
                                        $statusClass = 'text-warning';
                                        $statusText = '暂停';
                                        break;
                                    case 'cancelled':
                                        $statusClass = 'text-danger';
                                        $statusText = '已取消';
                                        break;
                                    case 'expired':
                                        $statusClass = 'text-danger';
                                        $statusText = '已过期';
                                        break;
                                    default:
                                        $statusClass = 'text-muted';
                                        $statusText = $sub['status'];
                                }

                                $expireClass = '';
                                if ($sub['status'] === 'active') {
                                    if ($daysLeft <= 1) {
                                        $expireClass = 'text-danger';
                                    } elseif ($daysLeft <= 7) {
                                        $expireClass = 'text-warning';
                                    }
                                }
                                ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="ids[]" value="<?php echo $sub['id']; ?>">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php
                                            // 订阅图标
                                            $icon = 'fas fa-play-circle';
                                            $iconColor = '#6b7280';

                                            switch ($sub['type']) {
                                                case 'video':
                                                    $icon = 'fas fa-play-circle';
                                                    $iconColor = '#ef4444';
                                                    break;
                                                case 'music':
                                                    $icon = 'fas fa-music';
                                                    $iconColor = '#10b981';
                                                    break;
                                                case 'software':
                                                    $icon = 'fas fa-laptop-code';
                                                    $iconColor = '#3b82f6';
                                                    break;
                                                case 'communication':
                                                    $icon = 'fas fa-comments';
                                                    $iconColor = '#f59e0b';
                                                    break;
                                                default:
                                                    $icon = 'fas fa-cube';
                                                    $iconColor = '#6b7280';
                                            }
                                            ?>
                                            <i class="<?php echo $icon; ?>" style="color: <?php echo $iconColor; ?>; font-size: 1.1rem;"></i>
                                            <div>
                                                <strong><?php echo htmlspecialchars($sub['name']); ?></strong>
                                                <?php if (!empty($sub['note'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($sub['note'], 0, 30)); ?><?php echo strlen($sub['note']) > 30 ? '...' : ''; ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $typeLabels = [
                                            'video' => '视频',
                                            'music' => '音乐',
                                            'software' => '软件',
                                            'communication' => '通讯',
                                            'other' => '其他'
                                        ];
                                        echo $typeLabels[$sub['type']] ?? $sub['type'];
                                        ?>
                                    </td>
                                    <td>
                                        <strong>¥<?php echo number_format($sub['price'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $cycleLabels = [
                                            'monthly' => '月付',
                                            'quarterly' => '季付',
                                            'yearly' => '年付',
                                            'custom' => '自定义'
                                        ];
                                        echo $cycleLabels[$sub['cycle']] ?? $sub['cycle'];
                                        ?>
                                    </td>
                                    <td>
                                        <span class="<?php echo $expireClass; ?>">
                                            <?php echo htmlspecialchars($sub['expire_at']); ?>
                                            <?php if ($sub['status'] === 'active' && $daysLeft >= 0): ?>
                                                <br><small>(<?php echo $daysLeft; ?>天后)</small>
                                            <?php elseif ($sub['status'] === 'active' && $daysLeft < 0): ?>
                                                <br><small class="text-danger">(已过期<?php echo abs($daysLeft); ?>天)</small>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="<?php echo $statusClass; ?>">
                                            <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="/?r=subscription-edit&id=<?php echo $sub['id']; ?>"
                                               class="btn btn-outline-primary btn-sm"
                                               title="编辑">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="/?r=reminder-create&id=<?php echo $sub['id']; ?>"
                                               class="btn btn-warning btn-sm"
                                               title="设置提醒">
                                                <i class="fas fa-bell"></i>
                                            </a>
                                            <a href="/?r=subscription-delete&id=<?php echo $sub['id']; ?>"
                                               class="btn btn-danger btn-sm"
                                               title="删除"
                                               onclick="return confirm('确定删除该订阅吗？');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- 批量操作 -->
                <div class="card-body border-top">
                    <div class="d-flex align-items-center gap-3">
                        <select name="batch_action" class="form-control" style="width: auto;">
                            <option value="">批量操作</option>
                            <option value="delete">删除选中</option>
                        </select>
                        <button type="submit" class="btn btn-outline-primary" onclick="return confirmBatchAction()">
                            <i class="fas fa-check"></i>
                            执行
                        </button>
                        <span class="text-muted" id="selectedCount">未选择任何项目</span>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- 分页导航 -->
<?php if ($totalPages > 1): ?>
<div class="card mt-4" id="paginationCard">
    <div class="card-body">
        <nav aria-label="订阅分页">
            <div class="d-flex justify-content-center">
                <!-- 分页按钮 -->
                <ul class="pagination mb-0">
                    <!-- 首页 -->
                    <?php if ($page > 2): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo buildPaginationUrl(1); ?>" data-page="1">
                                <i class="fas fa-angle-double-left"></i>
                                首页
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- 上一页 -->
                    <?php if ($hasPrevPage): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo buildPaginationUrl($page - 1); ?>" data-page="<?php echo $page - 1; ?>">
                                <i class="fas fa-angle-left"></i>
                                上一页
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- 页码 -->
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);

                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo buildPaginationUrl($i); ?>" data-page="<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- 下一页 -->
                    <?php if ($hasNextPage): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo buildPaginationUrl($page + 1); ?>" data-page="<?php echo $page + 1; ?>">
                                下一页
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- 末页 -->
                    <?php if ($page < $totalPages - 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo buildPaginationUrl($totalPages); ?>" data-page="<?php echo $totalPages; ?>">
                                末页
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </div>
</div>
<?php endif; ?>

<?php
// 构建分页URL的辅助函数
function buildPaginationUrl($pageNum) {
    $params = $_GET;
    $params['page'] = $pageNum;
    return '/?r=subscriptions&' . http_build_query($params);
}
?>

<script>
function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('input[name="ids[]"]');
    for (const cb of checkboxes) {
        cb.checked = source.checked;
    }
    updateSelectedCount();
}

function updateSelectedCount() {
    const selected = document.querySelectorAll('input[name="ids[]"]:checked');
    const countElement = document.getElementById('selectedCount');
    if (selected.length === 0) {
        countElement.textContent = '未选择任何项目';
        countElement.className = 'text-muted';
    } else {
        countElement.textContent = `已选择 ${selected.length} 个项目`;
        countElement.className = 'text-primary';
    }
}

function confirmBatchAction() {
    const selected = document.querySelectorAll('input[name="ids[]"]:checked');
    const action = document.querySelector('select[name="batch_action"]').value;

    if (selected.length === 0) {
        alert('请先选择要操作的项目');
        return false;
    }

    if (!action) {
        alert('请选择要执行的操作');
        return false;
    }

    const actionText = action === 'delete' ? '删除' : '操作';
    return confirm(`确定要${actionText}选中的 ${selected.length} 个订阅吗？`);
}

function filterSubscriptions(select) {
    const url = new URL(window.location);
    if (select.value) {
        url.searchParams.set('type', select.value);
    } else {
        url.searchParams.delete('type');
    }
    url.searchParams.delete('page'); // 筛选时重置到第一页
    window.location = url;
}

function filterStatus(select) {
    const url = new URL(window.location);
    if (select.value) {
        url.searchParams.set('status', select.value);
    } else {
        url.searchParams.delete('status');
    }
    url.searchParams.delete('page'); // 筛选时重置到第一页
    window.location = url;
}

// 监听复选框变化
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[name="ids[]"]');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
    updateSelectedCount();

    // 增强分页体验 - 保持滚动位置
    initPaginationScrollPreservation();

    // 键盘导航支持
    document.addEventListener('keydown', function(e) {
        // 支持左右箭头键翻页
        if (e.target.tagName.toLowerCase() !== 'input' && !e.ctrlKey && !e.altKey) {
            if (e.key === 'ArrowLeft' && <?php echo $hasPrevPage ? 'true' : 'false'; ?>) {
                e.preventDefault();
                navigateToPageWithScroll('<?php echo buildPaginationUrl($page - 1); ?>');
            } else if (e.key === 'ArrowRight' && <?php echo $hasNextPage ? 'true' : 'false'; ?>) {
                e.preventDefault();
                navigateToPageWithScroll('<?php echo buildPaginationUrl($page + 1); ?>');
            }
        }
    });
});

/**
 * 初始化分页滚动位置保持功能
 */
function initPaginationScrollPreservation() {
    // 处理所有分页链接
    const paginationLinks = document.querySelectorAll('.pagination .page-link');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            navigateToPageWithScroll(this.href);
        });
    });

    // 页面加载后恢复滚动位置
    restoreScrollPosition();
}

/**
 * 带滚动位置保持的页面导航
 */
function navigateToPageWithScroll(url) {
    // 记录当前滚动位置
    saveCurrentScrollPosition();
    
    // 显示加载状态
    showPageLoadingState();
    
    // 导航到新页面
    window.location.href = url;
}

/**
 * 保存当前滚动位置
 */
function saveCurrentScrollPosition() {
    const scrollY = window.scrollY;
    const timestamp = Date.now();
    
    // 保存到sessionStorage，5分钟内有效
    const scrollData = {
        position: scrollY,
        timestamp: timestamp,
        page: <?php echo $page; ?>,
        path: window.location.pathname + window.location.search.replace(/[&?]scroll_pos=\d+/, '')
    };
    
    sessionStorage.setItem('subscriptions_scroll_data', JSON.stringify(scrollData));
    
    // 同时添加到URL参数作为备选方案
    const url = new URL(window.location);
    url.searchParams.set('scroll_pos', scrollY);
    
    // 静默更新URL，不触发页面刷新
    if (history.replaceState) {
        history.replaceState(null, '', url.toString());
    }
}

/**
 * 恢复滚动位置
 */
function restoreScrollPosition() {
    // 方案1：从sessionStorage恢复
    try {
        const savedData = sessionStorage.getItem('subscriptions_scroll_data');
        if (savedData) {
            const scrollData = JSON.parse(savedData);
            const now = Date.now();
            
            // 检查数据是否在5分钟内且路径匹配
            if (now - scrollData.timestamp < 300000) { // 5分钟
                const currentPath = window.location.pathname + window.location.search.replace(/[&?]scroll_pos=\d+/, '');
                const savedPath = scrollData.path;
                
                // 如果是相邻页面，恢复滚动位置
                if (shouldRestoreScroll(currentPath, savedPath)) {
                    smoothScrollToPosition(scrollData.position);
                    return;
                }
            }
        }
    } catch (e) {
        console.log('从sessionStorage恢复滚动位置失败:', e);
    }
    
    // 方案2：从URL参数恢复
    const urlParams = new URLSearchParams(window.location.search);
    const scrollPos = urlParams.get('scroll_pos');
    if (scrollPos) {
        const position = parseInt(scrollPos);
        if (!isNaN(position) && position > 0) {
            smoothScrollToPosition(position);
            
            // 清理URL参数
            urlParams.delete('scroll_pos');
            const cleanUrl = window.location.pathname + 
                           (urlParams.toString() ? '?' + urlParams.toString() : '');
            if (history.replaceState) {
                history.replaceState(null, '', cleanUrl);
            }
        }
    }
}

/**
 * 判断是否应该恢复滚动位置
 */
function shouldRestoreScroll(currentPath, savedPath) {
    // 移除页码参数进行比较
    const cleanCurrent = currentPath.replace(/[&?]page=\d+/, '');
    const cleanSaved = savedPath.replace(/[&?]page=\d+/, '');
    
    // 如果基础路径相同，说明是在同一个列表的不同页面间切换
    return cleanCurrent === cleanSaved;
}

/**
 * 平滑滚动到指定位置
 */
function smoothScrollToPosition(position) {
    // 延迟执行，确保页面内容已加载
    setTimeout(() => {
        // 首先尝试使用现代浏览器的平滑滚动
        if ('scrollBehavior' in document.documentElement.style) {
            window.scrollTo({
                top: position,
                left: 0,
                behavior: 'smooth'
            });
        } else {
            // 降级处理：直接跳转
            window.scrollTo(0, position);
        }
        
        // 添加视觉反馈
        highlightContentArea();
    }, 100);
}

/**
 * 显示页面加载状态
 */
function showPageLoadingState() {
    // 给分页按钮添加加载状态
    const paginationCard = document.getElementById('paginationCard');
    if (paginationCard) {
        paginationCard.style.opacity = '0.6';
        paginationCard.style.pointerEvents = 'none';
    }
    
    // 给表格添加加载状态
    const tableContainer = document.querySelector('.table-responsive');
    if (tableContainer) {
        tableContainer.style.opacity = '0.8';
    }
}

/**
 * 高亮内容区域，提供视觉反馈
 */
function highlightContentArea() {
    const tableContainer = document.querySelector('.table-responsive');
    if (tableContainer) {
        // 添加高亮效果
        tableContainer.style.transition = 'box-shadow 0.3s ease';
        tableContainer.style.boxShadow = '0 0 20px rgba(59, 130, 246, 0.3)';
        
        // 2秒后移除高亮
        setTimeout(() => {
            tableContainer.style.boxShadow = '';
        }, 2000);
    }
}

/**
 * 清理过期的滚动位置数据
 */
function cleanupScrollData() {
    try {
        const savedData = sessionStorage.getItem('subscriptions_scroll_data');
        if (savedData) {
            const scrollData = JSON.parse(savedData);
            const now = Date.now();
            
            // 如果数据超过5分钟，清理它
            if (now - scrollData.timestamp > 300000) {
                sessionStorage.removeItem('subscriptions_scroll_data');
            }
        }
    } catch (e) {
        sessionStorage.removeItem('subscriptions_scroll_data');
    }
}

// 页面卸载时清理数据
window.addEventListener('beforeunload', cleanupScrollData);

// 定期清理过期数据
setInterval(cleanupScrollData, 60000); // 每分钟检查一次
</script>

<style>
/* 分页样式 */
.pagination {
    display: flex;
    padding-left: 0;
    list-style: none;
    border-radius: 0.375rem;
    margin: 0;
    gap: 0.125rem;
}

.page-item {
    position: relative;
}

.page-link {
    position: relative;
    display: block;
    padding: 0.5rem 0.75rem;
    color: var(--primary-color);
    text-decoration: none;
    background-color: var(--white);
    border: 1px solid var(--gray-300);
    border-radius: 0.375rem;
    transition: var(--transition);
    font-size: 0.875rem;
    line-height: 1.25;
}

.page-link:hover {
    z-index: 2;
    color: var(--primary-dark);
    background-color: var(--gray-100);
    border-color: var(--gray-300);
}

.page-item.active .page-link {
    z-index: 3;
    color: var(--white);
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.page-item.disabled .page-link {
    color: var(--gray-400);
    pointer-events: none;
    background-color: var(--gray-100);
    border-color: var(--gray-300);
}

/* 响应式优化 */
@media (max-width: 768px) {
    .pagination {
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.25rem;
    }

    .page-link {
        padding: 0.375rem 0.5rem;
        font-size: 0.8rem;
    }

    /* 在小屏幕上隐藏部分分页按钮 */
    .pagination .page-item:not(.active):not(:first-child):not(:last-child):not(:nth-child(2)):not(:nth-last-child(2)) {
        display: none;
    }

    .table th,
    .table td {
        padding: 0.5rem 0.25rem;
        font-size: 0.8rem;
    }

    .table-actions {
        flex-direction: column;
        gap: 0.25rem;
    }

    .table-actions .btn {
        width: 100%;
        font-size: 0.75rem;
    }
}

@media (max-width: 480px) {
    .page-link {
        padding: 0.25rem 0.375rem;
        font-size: 0.75rem;
    }

    /* 进一步简化分页显示 */
    .pagination .page-item:not(.active):not(:first-child):not(:last-child) {
        display: none;
    }
}
</style>