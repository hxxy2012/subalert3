<h2>添加订阅</h2>
<form method="post" action="/?r=subscription-create">
    <label for="name">订阅名称</label>
    <input type="text" id="name" name="name" required>
    <label for="type">服务类型</label>
    <select id="type" name="type" required>
        <option value="">请选择</option>
        <option value="video">视频</option>
        <option value="music">音乐</option>
        <option value="software">软件</option>
        <option value="communication">通讯</option>
        <option value="other">其他</option>
    </select>
    <label for="price">订阅价格（元）</label>
    <input type="number" step="0.01" id="price" name="price" required>
    <label for="cycle">订阅周期</label>
    <select id="cycle" name="cycle" required>
        <option value="monthly">月付</option>
        <option value="quarterly">季付</option>
        <option value="yearly">年付</option>
        <option value="custom">自定义</option>
    </select>
    <label for="expire_at">到期日期</label>
    <input type="date" id="expire_at" name="expire_at" required>
    <label>
        <input type="checkbox" name="auto_renew" value="1"> 自动续费
    </label>
    <label for="note">备注</label>
    <textarea id="note" name="note" rows="4"></textarea>
    <button type="submit">保存</button>
    <a href="/?r=subscriptions" class="btn">取消</a>
</form>