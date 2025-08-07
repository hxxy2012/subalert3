<?php 
// 更新 app/views/admin/settings.php - 在SMTP配置部分添加新字段
include __DIR__ . '/../layout/admin_header.php'; 
?>
<h2>系统设置</h2>

<form method="post" action="/admin.php?r=settings">
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">基础设置</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="site_name">站点名称</label>
                <input type="text" id="site_name" name="site_name" class="form-control" value="<?php echo htmlspecialchars($siteName); ?>">
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">邮件服务配置</h3>
        </div>
        <div class="card-body">
            <!-- 邮件服务类型选择 -->
            <div class="form-group">
                <label for="email_service_type">邮件服务类型</label>
                <select id="email_service_type" name="email_service_type" class="form-control" onchange="toggleEmailSettings()">
                    <option value="auto" <?php echo $emailServiceType === 'auto' ? 'selected' : ''; ?>>自动检测</option>
                    <option value="ses" <?php echo $emailServiceType === 'ses' ? 'selected' : ''; ?>>Amazon SES</option>
                    <option value="smtp" <?php echo $emailServiceType === 'smtp' ? 'selected' : ''; ?>>传统SMTP</option>
                </select>
                <small class="text-muted">自动检测：根据SMTP主机名自动判断服务类型</small>
            </div>
            
            <h4>SMTP基础配置</h4>
            <div class="form-group">
                <label for="smtp_host">SMTP Host</label>
                <input type="text" id="smtp_host" name="smtp_host" class="form-control" value="<?php echo htmlspecialchars($smtpHost); ?>" placeholder="例如：email-smtp.ap-southeast-1.amazonaws.com">
                <small class="text-muted">Amazon SES: email-smtp.区域.amazonaws.com</small>
            </div>
            
            <div class="form-group">
                <label for="smtp_port">SMTP Port</label>
                <select id="smtp_port" name="smtp_port" class="form-control">
                    <option value="587" <?php echo $smtpPort === '587' ? 'selected' : ''; ?>>587 (STARTTLS - 推荐)</option>
                    <option value="465" <?php echo $smtpPort === '465' ? 'selected' : ''; ?>>465 (SSL)</option>
                    <option value="25" <?php echo $smtpPort === '25' ? 'selected' : ''; ?>>25 (不加密)</option>
                </select>
                <small class="text-muted">Amazon SES推荐使用587端口</small>
            </div>
            
            <div class="form-group">
                <label for="smtp_user">SMTP 用户名</label>
                <input type="text" id="smtp_user" name="smtp_user" class="form-control" value="<?php echo htmlspecialchars($smtpUser); ?>" placeholder="SMTP凭证用户名">
                <small class="text-muted">Amazon SES: 使用IAM生成的SMTP凭证用户名</small>
            </div>
            
            <div class="form-group">
                <label for="smtp_pass">SMTP 密码</label>
                <input type="password" id="smtp_pass" name="smtp_pass" class="form-control" value="<?php echo htmlspecialchars($smtpPass); ?>" placeholder="SMTP凭证密码">
                <small class="text-muted">Amazon SES: 使用IAM生成的SMTP凭证密码</small>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">发件人设置</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="default_from_email">默认发件人邮箱</label>
                <input type="email" id="default_from_email" name="default_from_email" class="form-control" value="<?php echo htmlspecialchars($defaultFromEmail); ?>" placeholder="noreply@yourdomain.com">
                <small class="text-muted">用于一般邮件发送的默认发件人地址</small>
            </div>
            
            <div class="form-group" id="ses_from_group">
                <label for="ses_from_email">Amazon SES 发件人邮箱</label>
                <input type="email" id="ses_from_email" name="ses_from_email" class="form-control" value="<?php echo htmlspecialchars($sesFromEmail); ?>" placeholder="verified@yourdomain.com">
                <small class="text-muted">⚠️ 此邮箱必须在Amazon SES中验证过才能使用</small>
            </div>
            
            <div class="alert alert-info" id="ses_notice" style="display:none;">
                <h5>Amazon SES 配置说明：</h5>
                <ul class="mb-0">
                    <li>发件人邮箱必须在SES控制台中验证</li>
                    <li>如果账户仍在沙盒模式，收件人邮箱也需要验证</li>
                    <li>推荐申请移出沙盒模式以发送到任意邮箱</li>
                    <li>SMTP凭证需要通过IAM用户生成，不是AWS访问密钥</li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body text-center">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                保存设置
            </button>
            <a href="/smtp_test.php" class="btn btn-outline-primary ml-3" target="_blank">
                <i class="fas fa-paper-plane"></i>
                测试邮件发送
            </a>
        </div>
    </div>
</form>

<script>
function toggleEmailSettings() {
    const serviceType = document.getElementById('email_service_type').value;
    const sesFromGroup = document.getElementById('ses_from_group');
    const sesNotice = document.getElementById('ses_notice');
    
    if (serviceType === 'ses') {
        sesFromGroup.style.display = 'block';
        sesNotice.style.display = 'block';
        
        // 设置SES推荐配置
        document.getElementById('smtp_port').value = '587';
        
        // 显示SES相关提示
        const hostInput = document.getElementById('smtp_host');
        if (!hostInput.value.includes('amazonaws.com')) {
            hostInput.placeholder = 'email-smtp.ap-southeast-1.amazonaws.com';
        }
    } else if (serviceType === 'auto') {
        sesFromGroup.style.display = 'block';
        sesNotice.style.display = 'none';
    } else {
        sesFromGroup.style.display = 'none';
        sesNotice.style.display = 'none';
    }
}

// 页面加载时执行一次
document.addEventListener('DOMContentLoaded', function() {
    toggleEmailSettings();
    
    // 监听SMTP主机变化，自动检测服务类型
    document.getElementById('smtp_host').addEventListener('input', function() {
        const host = this.value;
        const serviceTypeSelect = document.getElementById('email_service_type');
        
        if (serviceTypeSelect.value === 'auto') {
            if (host.includes('amazonaws.com')) {
                document.getElementById('smtp_port').value = '587';
                document.getElementById('ses_notice').style.display = 'block';
            } else {
                document.getElementById('ses_notice').style.display = 'none';
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../layout/admin_footer.php'; ?>