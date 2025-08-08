<?php include __DIR__ . '/../layout/admin_header.php'; ?>

<!-- 系统信息卡片 -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-info-circle"></i>
            系统信息
        </h3>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center gap-4 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-globe text-primary"></i>
                <span><strong>站点名称：</strong><?php echo htmlspecialchars($siteName); ?></span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-clock text-success"></i>
                <span><strong>服务器时间：</strong><?php echo date('Y-m-d H:i:s'); ?></span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-user-shield text-warning"></i>
                <span><strong>当前管理员：</strong><?php echo htmlspecialchars($_SESSION['admin']['username'] ?? '未知'); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- 核心统计数据 -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon primary">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($userCnt); ?></div>
        <div class="stat-label">注册用户总数</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon success">
                <i class="fas fa-list"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($subCnt); ?></div>
        <div class="stat-label">订阅服务总数</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon warning">
                <i class="fas fa-bell"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format(array_sum($remCounts)); ?></div>
        <div class="stat-label">提醒记录总数</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon danger">
                <i class="fas fa-database"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($backupCnt); ?></div>
        <div class="stat-label">数据备份文件</div>
    </div>
</div>

<!-- 提醒状态详情 -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-bell"></i>
            提醒状态统计
        </h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>状态</th>
                        <th>数量</th>
                        <th>占比</th>
                        <th>状态说明</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = array_sum($remCounts);
                    $statusInfo = [
                        'pending' => ['待发送', 'text-warning', '等待系统发送的提醒'],
                        'sent' => ['已发送', 'text-primary', '已成功发送给用户的提醒'],
                        'read' => ['已读', 'text-info', '用户已查看的提醒'],
                        'done' => ['已完成', 'text-success', '用户已处理的提醒'],
                        'cancelled' => ['已取消', 'text-danger', '被用户取消的提醒']
                    ];

                    foreach ($statusInfo as $status => $info):
                        $count = $remCounts[$status] ?? 0;
                        $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                    ?>
                        <tr>
                            <td>
                                <span class="<?php echo $info[1]; ?>">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                    <?php echo $info[0]; ?>
                                </span>
                            </td>
                            <td><strong><?php echo number_format($count); ?></strong></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 100px; height: 8px; background-color: var(--gray-200); border-radius: 4px;">
                                        <div style="width: <?php echo $percentage; ?>%; height: 100%; background-color: var(--primary-color); border-radius: 4px;"></div>
                                    </div>
                                    <span><?php echo $percentage; ?>%</span>
                                </div>
                            </td>
                            <td class="text-muted"><?php echo $info[2]; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 快速操作 -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-tools"></i>
            系统管理
        </h3>
    </div>
    <div class="card-body">
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="/admin.php?r=users" class="btn btn-primary">
                <i class="fas fa-users"></i>
                用户管理
            </a>
            <a href="/admin.php?r=settings" class="btn btn-success">
                <i class="fas fa-cog"></i>
                系统设置
            </a>
            <a href="/admin.php?r=stats" class="btn btn-warning">
                <i class="fas fa-chart-bar"></i>
                数据统计
            </a>
            <a href="/admin.php?r=backups" class="btn btn-danger">
                <i class="fas fa-database"></i>
                数据备份
            </a>
            <a href="/admin.php?r=logs" class="btn btn-outline-primary">
                <i class="fas fa-list"></i>
                操作日志
            </a>
            <a href="/admin.php?r=tasks" class="btn btn-outline-primary">
                <i class="fas fa-tasks"></i>
                任务管理
            </a>
        </div>
    </div>
</div>

<!-- 系统状态 -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-server"></i>
            系统状态
        </h3>
    </div>
    <div class="card-body">
        <div class="d-flex gap-4 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-circle text-success"></i>
                <span><strong>数据库连接：</strong>正常</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-circle text-success"></i>
                <span><strong>用户注册：</strong>开放</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-circle text-success"></i>
                <span><strong>提醒服务：</strong>运行中</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-circle text-warning"></i>
                <span><strong>邮件服务：</strong>需配置</span>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/admin_footer.php'; ?>