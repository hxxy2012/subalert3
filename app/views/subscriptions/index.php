<h1 class="page-title">
    <i class="fas fa-list"></i>
    订阅管理
</h1>

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
                    <option value="video" <?php echo ($_GET['type'] ?? '') === 'video' ? 'selected' : ''; ?>>视频</option>
                    <option value="music" <?php echo ($_GET['type'] ?? '') === 'music' ? 'selected' : ''; ?>>音乐</option>
                    <option value="software" <?php echo ($_GET['type'] ?? '') === 'software' ? 'selected' : ''; ?>>软件</option>
                    <option value="communication" <?php echo ($_GET['type'] ?? '') === 'communication' ? 'selected' : ''; ?>>通讯</option>
                    <option value="other" <?php echo ($_GET['type'] ?? '') === 'other' ? 'selected' : ''; ?>>其他</option>
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
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-table"></i>
            我的订阅列表
        </h3>
    </div>
    <div class="card-body p-0">
        <?php if (empty($subscriptions)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>暂无订阅</h3>
                <p>您还没有添加任何订阅服务</p>
                <a href="/?r=subscription-create" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    添加第一个订阅
                </a>
            </div>
        <?php else: ?>
            <form method="post" action="/index.php?r=subscriptions" id="batchForm">
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
    window.location = url;
}

function filterStatus(select) {
    const url = new URL(window.location);
    if (select.value) {
        url.searchParams.set('status', select.value);
    } else {
        url.searchParams.delete('status');
    }
    window.location = url;
}

// 监听复选框变化
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[name="ids[]"]');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
    updateSelectedCount();
});
</script>