<h2>编辑订阅</h2>
<form method="post" action="/?r=subscription-edit&id=<?php echo $subscription['id']; ?>">
    <label for="name">订阅名称</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($subscription['name']); ?>" required>
    <label for="type">服务类型</label>
    <select id="type" name="type" required>
        <?php
        $types = ['video' => '视频', 'music' => '音乐', 'software' => '软件', 'communication' => '通讯', 'other' => '其他'];
        foreach ($types as $key => $label):
            $selected = $subscription['type'] === $key ? 'selected' : '';
            echo "<option value='{$key}' {$selected}>{$label}</option>";
        endforeach;
        ?>
    </select>
    <label for="price">订阅价格（元）</label>
    <input type="number" step="0.01" id="price" name="price" value="<?php echo $subscription['price']; ?>" required>
    <label for="cycle">订阅周期</label>
    <select id="cycle" name="cycle" required>
        <?php
        $cycles = ['monthly' => '月付', 'quarterly' => '季付', 'yearly' => '年付', 'custom' => '自定义'];
        foreach ($cycles as $key => $label):
            $selected = $subscription['cycle'] === $key ? 'selected' : '';
            echo "<option value='{$key}' {$selected}>{$label}</option>";
        endforeach;
        ?>
    </select>
    <label for="expire_at">到期日期</label>
    <input type="date" id="expire_at" name="expire_at" value="<?php echo $subscription['expire_at']; ?>" required>
    <label>
        <input type="checkbox" name="auto_renew" value="1" <?php echo $subscription['auto_renew'] ? 'checked' : ''; ?>> 自动续费
    </label>
    <label for="status">状态</label>
    <select id="status" name="status">
        <?php
        $statuses = ['active' => '正常', 'paused' => '暂停', 'cancelled' => '已取消', 'expired' => '已过期'];
        foreach ($statuses as $key => $label):
            $selected = $subscription['status'] === $key ? 'selected' : '';
            echo "<option value='{$key}' {$selected}>{$label}</option>";
        endforeach;
        ?>
    </select>
    <label for="note">备注</label>
    <textarea id="note" name="note" rows="4"><?php echo htmlspecialchars($subscription['note']); ?></textarea>
    <button type="submit">保存</button>
    <a href="/?r=subscriptions" class="btn">取消</a>
</form>