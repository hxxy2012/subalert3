<h1 class="page-title">
    <i class="fas fa-tachometer-alt"></i>
    订阅概览
</h1>

<!-- 统计卡片 -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon primary">
                <i class="fas fa-list"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo $total; ?></div>
        <div class="stat-label">总订阅数</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon danger">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo count($upcoming); ?></div>
        <div class="stat-label">即将到期</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon success">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
        <div class="stat-value">¥<?php echo number_format(array_sum(array_column($upcoming, 'price')), 0); ?></div>
        <div class="stat-label">即将支出</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon warning">
                <i class="fas fa-bell"></i>
            </div>
        </div>
        <div class="stat-value">-</div>
        <div class="stat-label">活跃提醒</div>
    </div>
</div>

<!-- 即将到期订阅 -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-clock text-danger"></i>
            即将到期订阅（7天内）
        </h3>
    </div>
    <div class="card-body">
        <?php if (empty($upcoming)): ?>
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <h3>暂无即将到期的订阅</h3>
                <p>您的所有订阅都还有充足的时间</p>
                <a href="/?r=subscriptions" class="btn btn-primary">
                    <i class="fas fa-list"></i>
                    查看所有订阅
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>订阅名称</th>
                            <th>到期时间</th>
                            <th>价格</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcoming as $item): ?>
                            <?php
                            // 计算剩余天数
                            $expireDate = new DateTime($item['expire_at']);
                            $today = new DateTime();
                            $diff = $today->diff($expireDate);
                            $daysLeft = $diff->days;

                            // 根据剩余天数设置样式
                            $urgencyClass = '';
                            if ($daysLeft <= 1) {
                                $urgencyClass = 'text-danger';
                            } elseif ($daysLeft <= 3) {
                                $urgencyClass = 'text-warning';
                            } else {
                                $urgencyClass = 'text-primary';
                            }
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php
                                        // 根据订阅名称显示不同图标
                                        $icon = 'fas fa-play-circle';
                                        $iconColor = '#3b82f6';

                                        if (stripos($item['name'], 'netflix') !== false) {
                                            $icon = 'fab fa-netflix';
                                            $iconColor = '#e50914';
                                        } elseif (stripos($item['name'], 'spotify') !== false) {
                                            $icon = 'fab fa-spotify';
                                            $iconColor = '#1db954';
                                        } elseif (stripos($item['name'], 'youtube') !== false) {
                                            $icon = 'fab fa-youtube';
                                            $iconColor = '#ff0000';
                                        } elseif (stripos($item['name'], 'adobe') !== false) {
                                            $icon = 'fab fa-adobe';
                                            $iconColor = '#ff0000';
                                        } elseif (stripos($item['name'], 'office') !== false || stripos($item['name'], 'microsoft') !== false) {
                                            $icon = 'fab fa-microsoft';
                                            $iconColor = '#0078d4';
                                        }
                                        ?>
                                        <i class="<?php echo $icon; ?>" style="color: <?php echo $iconColor; ?>; font-size: 1.25rem;"></i>
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="<?php echo $urgencyClass; ?>">
                                        <?php echo htmlspecialchars($item['expire_at']); ?>
                                        <br>
                                        <small>(还剩 <?php echo $daysLeft; ?> 天)</small>
                                    </span>
                                </td>
                                <td>
                                    <strong>¥<?php echo number_format($item['price'], 2); ?></strong>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="/?r=subscription-edit&id=<?php echo $item['id']; ?>"
                                           class="btn btn-success btn-sm">
                                            <i class="fas fa-redo"></i>
                                            续费
                                        </a>
                                        <a href="/?r=reminder-create&id=<?php echo $item['id']; ?>"
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-bell"></i>
                                            提醒
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- 快速操作 -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-rocket"></i>
            快速操作
        </h3>
    </div>
    <div class="card-body">
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="/?r=subscription-create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                添加订阅
            </a>
            <a href="/?r=reminders" class="btn btn-warning">
                <i class="fas fa-bell"></i>
                管理提醒
            </a>
            <a href="/?r=stats" class="btn btn-success">
                <i class="fas fa-chart-line"></i>
                查看统计
            </a>
            <a href="/?r=settings" class="btn btn-outline-primary">
                <i class="fas fa-cog"></i>
                系统设置
            </a>
        </div>
    </div>
</div>