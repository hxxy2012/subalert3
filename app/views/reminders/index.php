<?php
// 分页参数
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 5; // 每页显示20条
$offset = ($page - 1) * $perPage;

// 构建查询条件
$whereConditions = ['r.user_id = ?'];
$params = [current_user()['id']];

// 状态筛选
if (!empty($_GET['status'])) {
    $whereConditions[] = 'r.status = ?';
    $params[] = $_GET['status'];
}

// 提醒方式筛选
if (!empty($_GET['type'])) {
    $whereConditions[] = 'r.remind_type = ?';
    $params[] = $_GET['type'];
}

// 构建完整查询
$whereClause = implode(' AND ', $whereConditions);

// 获取总数
$pdo = \App\Models\DB::getConnection();
$countQuery = "SELECT COUNT(*) as total FROM reminders r
               LEFT JOIN subscriptions s ON r.subscription_id = s.id
               WHERE {$whereClause}";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalReminders = $countStmt->fetch()['total'];

// 计算分页信息
$totalPages = ceil($totalReminders / $perPage);
$hasNextPage = $page < $totalPages;
$hasPrevPage = $page > 1;

// 获取当前页数据
$dataQuery = "SELECT r.*, s.name as subscription_name, s.expire_at as subscription_expire, s.price as subscription_price
              FROM reminders r
              LEFT JOIN subscriptions s ON r.subscription_id = s.id
              WHERE {$whereClause}
              ORDER BY r.remind_at ASC
              LIMIT {$perPage} OFFSET {$offset}";
$dataStmt = $pdo->prepare($dataQuery);
$dataStmt->execute($params);
$reminders = $dataStmt->fetchAll();
?>

<!-- 提醒方式筛选器（右上角浮动） -->
<div class="reminder-type-filter">
    <select class="form-control" onchange="filterReminders(this, 'type')">
        <option value="">全部方式</option>
        <option value="email" <?php echo ($_GET['type'] ?? '') === 'email' ? 'selected' : ''; ?>>📧 邮件</option>
        <option value="feishu" <?php echo ($_GET['type'] ?? '') === 'feishu' ? 'selected' : ''; ?>>🔔 飞书</option>
        <option value="wechat" <?php echo ($_GET['type'] ?? '') === 'wechat' ? 'selected' : ''; ?>>💬 企业微信</option>
        <option value="site" <?php echo ($_GET['type'] ?? '') === 'site' ? 'selected' : ''; ?>>🖥️ 站内消息</option>
    </select>
</div>



<!-- 可点击统计概览筛选 -->
<div class="stats-overview mb-4">
    <?php
    // 计算当前页面的统计数据
    $statusCounts = [
        'pending' => 0,
        'sent' => 0,
        'read' => 0,
        'done' => 0,
        'cancelled' => 0
    ];

    // 获取全部数据的统计（不限制分页）
    $allStatsQuery = "SELECT status, COUNT(*) as count FROM reminders r WHERE r.user_id = ? GROUP BY status";
    $allStatsStmt = $pdo->prepare($allStatsQuery);
    $allStatsStmt->execute([current_user()['id']]);
    while ($row = $allStatsStmt->fetch()) {
        if (isset($statusCounts[$row['status']])) {
            $statusCounts[$row['status']] = $row['count'];
        }
    }

    $activeReminders = $statusCounts['pending'] + $statusCounts['sent'];
    $currentFilter = $_GET['status'] ?? '';
    ?>

    <div class="stats-clickable">
        <div class="stats-divided-content">
            <div class="stat-item-divided <?php echo empty($currentFilter) ? 'active' : ''; ?>" 
                 onclick="filterByStatus('')">
                <div class="stat-value-divided"><?php echo number_format($totalReminders); ?></div>
                <div class="stat-label-divided">
                    <i class="fas fa-bell stat-icon-divided text-primary"></i>
                    全部提醒
                </div>
            </div>

            <div class="stat-item-divided <?php echo $currentFilter === 'pending' ? 'active' : ''; ?>" 
                 onclick="filterByStatus('pending')">
                <div class="stat-value-divided"><?php echo $statusCounts['pending']; ?></div>
                <div class="stat-label-divided">
                    <i class="fas fa-clock stat-icon-divided text-warning"></i>
                    待发送
                </div>
            </div>

            <div class="stat-item-divided <?php echo $currentFilter === 'done' ? 'active' : ''; ?>" 
                 onclick="filterByStatus('done')">
                <div class="stat-value-divided"><?php echo $statusCounts['done']; ?></div>
                <div class="stat-label-divided">
                    <i class="fas fa-check-circle stat-icon-divided text-success"></i>
                    已完成
                </div>
            </div>

            <div class="stat-item-divided <?php echo ($currentFilter === 'pending' || $currentFilter === 'sent') && $currentFilter !== '' ? 'active' : ''; ?>" 
                 onclick="filterByStatus('active')">
                <div class="stat-value-divided"><?php echo $activeReminders; ?></div>
                <div class="stat-label-divided">
                    <i class="fas fa-exclamation-triangle stat-icon-divided text-danger"></i>
                    需要关注
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 提醒列表 -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($reminders)): ?>
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                <h3>暂无提醒</h3>
                <?php if ($page > 1): ?>
                    <p>当前页面没有数据，请尝试其他页面</p>
                    <a href="/?r=reminders&page=1" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i>
                        返回第一页
                    </a>
                <?php else: ?>
                    <p>您还没有设置任何订阅提醒</p>
                    <a href="/?r=subscriptions" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        为订阅添加提醒
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <form method="post" action="/index.php?r=reminders" id="batchForm">
                <!-- 保留分页和筛选参数 -->
                <input type="hidden" name="page" value="<?php echo $page; ?>">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($_GET['status'] ?? ''); ?>">
                <input type="hidden" name="type" value="<?php echo htmlspecialchars($_GET['type'] ?? ''); ?>">

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                </th>
                                <th>订阅信息</th>
                                <th>提醒设置</th>
                                <th>提醒时间</th>
                                <th>状态</th>
                                <th>最后发送</th>
                                <th width="200">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reminders as $rem): ?>
                                <?php
                                // 计算时间相关信息
                                $remindTime = new DateTime($rem['remind_at']);
                                $now = new DateTime();
                                $timeDiff = $now->diff($remindTime);

                                // 状态样式和文本
                                $statusInfo = [
                                    'pending' => ['待发送', 'text-warning', 'fas fa-clock'],
                                    'sent' => ['已发送', 'text-primary', 'fas fa-paper-plane'],
                                    'read' => ['已读', 'text-info', 'fas fa-eye'],
                                    'done' => ['已完成', 'text-success', 'fas fa-check-circle'],
                                    'cancelled' => ['已取消', 'text-danger', 'fas fa-times-circle']
                                ];

                                $currentStatus = $statusInfo[$rem['status']] ?? ['未知', 'text-muted', 'fas fa-question-circle'];

                                // 提醒方式图标和文本
                                $typeInfo = [
                                    'email' => ['📧 邮件', '#3b82f6'],
                                    'feishu' => ['🔔 飞书', '#00d9ff'],
                                    'wechat' => ['💬 企业微信', '#07c160'],
                                    'site' => ['🖥️ 站内', '#6b7280']
                                ];

                                $currentType = $typeInfo[$rem['remind_type']] ?? ['❓ 未知', '#6b7280'];

                                // 时间状态
                                $timeStatus = '';
                                $timeClass = '';
                                if ($rem['status'] === 'pending') {
                                    if ($remindTime <= $now) {
                                        $timeStatus = '应立即发送';
                                        $timeClass = 'text-danger';
                                    } else {
                                        $days = $timeDiff->days;
                                        $hours = $timeDiff->h;
                                        if ($days > 0) {
                                            $timeStatus = "{$days}天后发送";
                                        } elseif ($hours > 0) {
                                            $timeStatus = "{$hours}小时后发送";
                                        } else {
                                            $timeStatus = "即将发送";
                                            $timeClass = 'text-warning';
                                        }
                                    }
                                }
                                ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="ids[]" value="<?php echo $rem['id']; ?>">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-bell-o" style="color: #f59e0b; font-size: 1.1rem;"></i>
                                            <div>
                                                <strong><?php echo htmlspecialchars($rem['subscription_name']); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    到期: <?php echo htmlspecialchars($rem['subscription_expire']); ?>
                                                    <?php if (!empty($rem['subscription_price'])): ?>
                                                        | ¥<?php echo number_format($rem['subscription_price'], 2); ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span style="color: <?php echo $currentType[1]; ?>; font-weight: 500;">
                                                <?php echo $currentType[0]; ?>
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            提前 <?php echo $rem['remind_days']; ?> 天
                                        </small>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo date('m-d H:i', strtotime($rem['remind_at'])); ?></strong>
                                            <?php if ($timeStatus): ?>
                                                <br>
                                                <small class="<?php echo $timeClass ?: 'text-muted'; ?>">
                                                    <?php echo $timeStatus; ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="<?php echo $currentStatus[1]; ?>">
                                            <i class="<?php echo $currentStatus[2]; ?>" style="font-size: 0.8rem;"></i>
                                            <?php echo $currentStatus[0]; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($rem['sent_at']): ?>
                                            <div>
                                                <?php echo date('m-d H:i', strtotime($rem['sent_at'])); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">未发送</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <?php if ($rem['status'] === 'pending' || $rem['status'] === 'sent'): ?>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                                        <i class="fas fa-cog"></i> 操作
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="/?r=reminder-action&id=<?php echo $rem['id']; ?>&op=renew">
                                                            <i class="fas fa-redo text-success"></i> 已续费
                                                        </a>
                                                        <a class="dropdown-item" href="/?r=reminder-action&id=<?php echo $rem['id']; ?>&op=delay">
                                                            <i class="fas fa-clock text-warning"></i> 延迟3天
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="/?r=reminder-action&id=<?php echo $rem['id']; ?>&op=read">
                                                            <i class="fas fa-eye text-info"></i> 标记已读
                                                        </a>
                                                        <a class="dropdown-item" href="/?r=reminder-action&id=<?php echo $rem['id']; ?>&op=cancel"
                                                           onclick="return confirm('确定取消此提醒吗？')">
                                                            <i class="fas fa-times text-danger"></i> 取消提醒
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php elseif ($rem['status'] === 'done'): ?>
                                                <span class="btn btn-outline-success btn-sm disabled">
                                                    <i class="fas fa-check"></i> 已处理
                                                </span>
                                            <?php elseif ($rem['status'] === 'cancelled'): ?>
                                                <span class="btn btn-outline-danger btn-sm disabled">
                                                    <i class="fas fa-ban"></i> 已取消
                                                </span>
                                            <?php else: ?>
                                                <a href="/?r=reminder-action&id=<?php echo $rem['id']; ?>&op=read"
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i> 查看
                                                </a>
                                            <?php endif; ?>
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
                            <option value="mark_read">标记为已读</option>
                            <option value="cancel">取消提醒</option>
                            <option value="delay">延迟3天</option>
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
<div class="card mt-4">
    <div class="card-body">
        <nav aria-label="提醒分页">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <!-- 页面信息 -->
                <div class="text-muted">
                    显示第 <strong><?php echo $offset + 1; ?></strong> 到
                    <strong><?php echo min($offset + $perPage, $totalReminders); ?></strong> 条，
                    共 <strong><?php echo number_format($totalReminders); ?></strong> 条记录
                </div>

                <!-- 分页按钮 -->
                <ul class="pagination mb-0">
                    <!-- 首页 -->
                    <?php if ($page > 2): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo buildPaginationUrl(1); ?>">
                                <i class="fas fa-angle-double-left"></i>
                                首页
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- 上一页 -->
                    <?php if ($hasPrevPage): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo buildPaginationUrl($page - 1); ?>">
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
                            <a class="page-link" href="<?php echo buildPaginationUrl($i); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- 下一页 -->
                    <?php if ($hasNextPage): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo buildPaginationUrl($page + 1); ?>">
                                下一页
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- 末页 -->
                    <?php if ($page < $totalPages - 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo buildPaginationUrl($totalPages); ?>">
                                末页
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- 快速跳转 -->
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted">跳转到:</span>
                    <input type="number"
                           class="form-control"
                           style="width: 80px;"
                           min="1"
                           max="<?php echo $totalPages; ?>"
                           value="<?php echo $page; ?>"
                           onkeypress="if(event.key==='Enter') jumpToPage(this.value)">
                    <button class="btn btn-outline-primary btn-sm" onclick="jumpToPage(document.querySelector('input[type=number]').value)">
                        跳转
                    </button>
                </div>
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
    return '/?r=reminders&' . http_build_query($params);
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
        countElement.textContent = `已选择 ${selected.length} 个提醒`;
        countElement.className = 'text-primary';
    }
}

function confirmBatchAction() {
    const selected = document.querySelectorAll('input[name="ids[]"]:checked');
    const action = document.querySelector('select[name="batch_action"]').value;

    if (selected.length === 0) {
        alert('请先选择要操作的提醒');
        return false;
    }

    if (!action) {
        alert('请选择要执行的操作');
        return false;
    }

    const actionTexts = {
        'mark_read': '标记为已读',
        'cancel': '取消',
        'delay': '延迟'
    };

    const actionText = actionTexts[action] || '操作';
    return confirm(`确定要${actionText}选中的 ${selected.length} 个提醒吗？`);
}

function filterReminders(select, filterType) {
    const url = new URL(window.location);
    if (select.value) {
        url.searchParams.set(filterType, select.value);
    } else {
        url.searchParams.delete(filterType);
    }
    url.searchParams.delete('page'); // 筛选时重置到第一页
    
    // 记录当前滚动位置
    const currentScrollY = window.scrollY;
    url.searchParams.set('scroll', currentScrollY);
    
    window.location = url;
}

// 按状态筛选的函数
function filterByStatus(status) {
    const url = new URL(window.location);
    
    if (status === '' || status === 'active') {
        // 特殊处理：如果是'active'，转换为pending,sent的组合筛选
        if (status === 'active') {
            // 这里可以根据需要调整逻辑，暂时设为pending
            url.searchParams.set('status', 'pending');
        } else {
            url.searchParams.delete('status');
        }
    } else {
        url.searchParams.set('status', status);
    }
    
    url.searchParams.delete('page'); // 筛选时重置到第一页
    
    // 记录当前滚动位置
    const currentScrollY = window.scrollY;
    url.searchParams.set('scroll', currentScrollY);
    
    window.location = url;
}

function jumpToPage(pageNum) {
    const page = parseInt(pageNum);
    const totalPages = <?php echo $totalPages; ?>;

    if (isNaN(page) || page < 1 || page > totalPages) {
        alert(`请输入1到${totalPages}之间的页码`);
        return;
    }

    const url = new URL(window.location);
    url.searchParams.set('page', page);
    
    // 记录当前滚动位置
    const currentScrollY = window.scrollY;
    url.searchParams.set('scroll', currentScrollY);
    
    window.location = url;
}

// 简单的下拉菜单实现
document.addEventListener('DOMContentLoaded', function() {
    // 处理下拉菜单
    document.querySelectorAll('.dropdown-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // 关闭其他下拉菜单
            document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
                if (menu !== toggle.nextElementSibling) {
                    menu.style.display = 'none';
                }
            });

            // 切换当前下拉菜单
            const menu = toggle.nextElementSibling;
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });
    });

    // 点击外部关闭下拉菜单
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
            menu.style.display = 'none';
        });
    });

    // 监听复选框变化
    const checkboxes = document.querySelectorAll('input[name="ids[]"]');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
    updateSelectedCount();

    // 键盘导航支持
    document.addEventListener('keydown', function(e) {
        // 支持左右箭头键翻页
        if (e.target.tagName.toLowerCase() !== 'input') {
            if (e.key === 'ArrowLeft' && <?php echo $hasPrevPage ? 'true' : 'false'; ?>) {
                navigateToPage('<?php echo buildPaginationUrl($page - 1); ?>');
            } else if (e.key === 'ArrowRight' && <?php echo $hasNextPage ? 'true' : 'false'; ?>) {
                navigateToPage('<?php echo buildPaginationUrl($page + 1); ?>');
            }
        }
    });

    // 优化分页导航 - 保持当前滚动位置
    function navigateToPage(url) {
        // 记录当前滚动位置
        const currentScrollY = window.scrollY;
        
        // 添加滚动位置到URL
        const urlObj = new URL(url, window.location.origin);
        urlObj.searchParams.set('scroll', currentScrollY);
        
        window.location.href = urlObj.toString();
    }

    // 优化所有分页链接
    document.querySelectorAll('.pagination .page-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            navigateToPage(this.href);
        });
    });

    // 页面加载后恢复滚动位置
    const urlParams = new URLSearchParams(window.location.search);
    const scrollPosition = urlParams.get('scroll');
    if (scrollPosition) {
        // 延迟恢复滚动位置，确保页面完全加载
        setTimeout(function() {
            window.scrollTo({
                top: parseInt(scrollPosition),
                behavior: 'smooth'
            });
            
            // 清理URL中的scroll参数（可选）
            const cleanUrl = new URL(window.location);
            cleanUrl.searchParams.delete('scroll');
            window.history.replaceState({}, document.title, cleanUrl.toString());
        }, 100);
    }

    // 自动刷新待发送提醒的倒计时
    setInterval(function() {
        const pendingRows = document.querySelectorAll('tbody tr');
        pendingRows.forEach(function(row) {
            const statusCell = row.cells[4];
            if (statusCell && statusCell.textContent.includes('待发送')) {
                // 可以在这里添加实时倒计时更新逻辑
            }
        });
    }, 60000); // 每分钟更新一次
});
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

/* 下拉菜单样式 */
.btn-group {
    position: relative;
    display: inline-block;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1000;
    min-width: 160px;
    padding: 0.5rem 0;
    margin: 0.125rem 0 0;
    background-color: #fff;
    border: 1px solid rgba(0,0,0,.15);
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,.175);
}

.dropdown-item {
    display: block;
    width: 100%;
    padding: 0.5rem 1rem;
    clear: both;
    font-weight: 400;
    color: #212529;
    text-align: inherit;
    text-decoration: none;
    white-space: nowrap;
    background-color: transparent;
    border: 0;
}

.dropdown-item:hover {
    color: #1e2125;
    background-color: #e9ecef;
}

.dropdown-divider {
    height: 0;
    margin: 0.5rem 0;
    overflow: hidden;
    border-top: 1px solid #e9ecef;
}

/* 表格行悬停效果增强 */
.table tbody tr:hover {
    background-color: var(--gray-50);
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

/* 状态标签样式 */
.table td span[class*="text-"] {
    font-weight: 500;
    font-size: 0.875rem;
}

/* 操作按钮组样式 */
.table-actions .btn-group .btn {
    border-radius: 0.375rem;
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
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

    .table-actions .btn-group {
        width: 100%;
    }

    .table-actions .btn {
        width: 100%;
        font-size: 0.75rem;
    }

    .dropdown-menu {
        position: fixed;
        top: auto !important;
        left: 1rem !important;
        right: 1rem !important;
        width: auto !important;
        min-width: auto !important;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    /* 分页控制在移动端的优化 */
    .card-body nav > div {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .card-body nav .pagination {
        justify-content: center;
    }

    .card-body nav .d-flex:last-child {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .page-link {
        padding: 0.25rem 0.375rem;
        font-size: 0.75rem;
    }

    /* 进一步简化分页显示 */
    .pagination .page-item:not(.active):not(:first-child):not(:last-child) {
        display: none;
    }

    /* 只显示：首页、上一页、当前页、下一页、末页 */
    .pagination .page-item.active ~ .page-item:not(:last-child) {
        display: none;
    }
}

/* 提醒方式筛选器（右上角浮动） */
.reminder-type-filter {
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 10;
}

.reminder-type-filter .form-control {
    min-width: 120px;
    font-size: 0.8rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    background: var(--white);
    box-shadow: var(--shadow);
}

/* 可点击统计卡片样式 */
.stats-clickable {
    background: var(--white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-200);
    overflow: hidden;
    position: relative;
}

.stats-clickable .stats-divided-content {
    display: flex;
}

.stats-clickable .stat-item-divided {
    flex: 1;
    padding: 1rem;
    text-align: center;
    border-right: 1px solid var(--gray-200);
    transition: var(--transition);
    cursor: pointer;
    position: relative;
}

.stats-clickable .stat-item-divided:last-child {
    border-right: none;
}

.stats-clickable .stat-item-divided:hover {
    background: var(--gray-50);
    transform: translateY(-1px);
}

.stats-clickable .stat-item-divided.active {
    background: var(--primary-color);
    color: var(--white);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
}

.stats-clickable .stat-item-divided.active .stat-value-divided,
.stats-clickable .stat-item-divided.active .stat-label-divided {
    color: var(--white);
}

.stats-clickable .stat-item-divided.active .stat-icon-divided {
    color: var(--white) !important;
}

.stats-clickable .stat-item-divided::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 3px;
    background: var(--primary-color);
    transition: var(--transition);
}

.stats-clickable .stat-item-divided.active::after {
    width: 80%;
    background: var(--white);
}

/* 方案一：带分隔线的极简统计卡片 */
.stats-overview {
    margin-bottom: 1rem;
}

.stats-divided {
    background: var(--white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-200);
    overflow: hidden;
}

.stats-divided-content {
    display: flex;
}

.stat-item-divided {
    flex: 1;
    padding: 1rem;
    text-align: center;
    border-right: 1px solid var(--gray-200);
    transition: var(--transition);
}

.stat-item-divided:last-child {
    border-right: none;
}

.stat-item-divided:hover {
    background: var(--gray-50);
}

.stat-value-divided {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 0.25rem;
}

.stat-label-divided {
    color: var(--gray-500);
    font-size: 0.7rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
}

.stat-icon-divided {
    font-size: 0.75rem;
}
</style>