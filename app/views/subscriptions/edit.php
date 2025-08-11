<h1 class="page-title">
    <i class="fas fa-edit"></i>
    编辑订阅
</h1>

<div class="d-flex justify-content-center">
    <div class="card" style="max-width: 600px; width: 100%;">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-alt"></i>
                订阅信息
            </h3>
        </div>
        <div class="card-body">
            <form method="post" action="/?r=subscription-edit&id=<?php echo $subscription['id']; ?>">
                <!-- 订阅名称 -->
                <div class="form-group">
                    <label for="name" class="form-label">
                        <i class="fas fa-tag"></i>
                        订阅名称 <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        value="<?php echo htmlspecialchars($subscription['name']); ?>"
                        required
                    >
                </div>

                <!-- 服务类型 -->
                <div class="form-group">
                    <label for="type" class="form-label">
                        <i class="fas fa-layer-group"></i>
                        服务类型 <span class="text-danger">*</span>
                    </label>
                    <select id="type" name="type" class="form-control" required>
                        <?php
                        $types = ['video' => '视频', 'music' => '音乐', 'software' => '软件', 'communication' => '通讯', 'other' => '其他'];
                        foreach ($types as $key => $label):
                            $selected = $subscription['type'] === $key ? 'selected' : '';
                            echo "<option value='{$key}' {$selected}>{$label}</option>";
                        endforeach;
                        ?>
                    </select>
                </div>

                <!-- 价格 & 周期 -->
                <div class="d-flex gap-3">
                    <div class="form-group" style="flex: 1;">
                        <label for="price" class="form-label">
                            <i class="fas fa-dollar-sign"></i>
                            订阅价格（元） <span class="text-danger">*</span>
                        </label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--gray-500);">¥</span>
                            <input
                                type="number"
                                step="0.01"
                                id="price"
                                name="price"
                                class="form-control"
                                style="padding-left: 2rem;"
                                value="<?php echo $subscription['price']; ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label for="cycle" class="form-label">
                            <i class="fas fa-calendar-alt"></i>
                            订阅周期 <span class="text-danger">*</span>
                        </label>
                        <select id="cycle" name="cycle" class="form-control" required>
                            <?php
                            $cycles = ['monthly' => '月付', 'quarterly' => '季付', 'yearly' => '年付', 'custom' => '自定义'];
                            foreach ($cycles as $key => $label):
                                $selected = $subscription['cycle'] === $key ? 'selected' : '';
                                echo "<option value='{$key}' {$selected}>{$label}</option>";
                            endforeach;
                            ?>
                        </select>
                    </div>
                </div>

                <!-- 到期日期 -->
                <div class="form-group">
                    <label for="expire_at" class="form-label">
                        <i class="fas fa-hourglass-end"></i>
                        到期日期 <span class="text-danger">*</span>
                    </label>
                    <input
                        type="date"
                        id="expire_at"
                        name="expire_at"
                        class="form-control"
                        value="<?php echo $subscription['expire_at']; ?>"
                        required
                    >
                </div>

                <!-- 自动续费 -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-rotate"></i>
                        续费设置
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="auto_renew" value="1" <?php echo $subscription['auto_renew'] ? 'checked' : ''; ?>>
                        自动续费
                    </label>
                </div>

                <!-- 状态 -->
                <div class="form-group">
                    <label for="status" class="form-label">
                        <i class="fas fa-toggle-on"></i>
                        状态
                    </label>
                    <select id="status" name="status" class="form-control">
                        <?php
                        $statuses = ['active' => '正常', 'paused' => '暂停', 'cancelled' => '已取消', 'expired' => '已过期'];
                        foreach ($statuses as $key => $label):
                            $selected = $subscription['status'] === $key ? 'selected' : '';
                            echo "<option value='{$key}' {$selected}>{$label}</option>";
                        endforeach;
                        ?>
                    </select>
                </div>

                <!-- 备注 -->
                <div class="form-group">
                    <label for="note" class="form-label">
                        <i class="fas fa-note-sticky"></i>
                        备注
                    </label>
                    <textarea id="note" name="note" rows="4" class="form-control"><?php echo htmlspecialchars($subscription['note']); ?></textarea>
                </div>

                <!-- 操作按钮 -->
                <div class="d-flex gap-3 justify-content-end">
                    <a href="/?r=subscriptions" class="btn btn-outline-primary">
                        <i class="fas fa-undo"></i>
                        取消
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        保存
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>