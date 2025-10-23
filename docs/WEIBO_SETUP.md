# 微博自动发布功能配置指南

## 概述

SubAlert 现在支持通过微博自动发布订阅到期提醒。当您的订阅即将到期时，系统可以自动在您的微博账号上发布提醒消息。

## 功能特点

- 🔄 自动发布提醒到微博
- 📝 自定义提醒内容
- 🔐 使用 OAuth2.0 安全认证
- ⏰ 支持免打扰时间设置
- 🎯 与其他提醒方式（邮件、飞书、企业微信）并存

## 配置步骤

### 1. 注册微博开放平台

1. 访问 [微博开放平台](https://open.weibo.com)
2. 使用您的微博账号登录
3. 完成开发者认证（如果尚未认证）

### 2. 创建应用

1. 进入"微连接" → "移动应用"
2. 点击"创建应用"
3. 填写应用信息：
   - 应用名称：SubAlert 或您自己的名称
   - 应用类型：选择适合的类型
   - 应用介绍：填写应用说明
4. 提交审核并等待通过

### 3. 获取应用凭证

在应用管理页面，您可以看到：
- **App Key**：应用的唯一标识
- **App Secret**：应用密钥（请妥善保管）

### 4. OAuth2.0 授权流程

#### 方法一：使用授权工具（推荐）

访问微博开放平台提供的授权工具：
```
https://open.weibo.com/tools/console
```

#### 方法二：手动授权

1. **构建授权 URL**
```
https://api.weibo.com/oauth2/authorize?client_id=YOUR_APP_KEY&response_type=code&redirect_uri=YOUR_CALLBACK_URL
```

替换以下参数：
- `YOUR_APP_KEY`: 您的 App Key
- `YOUR_CALLBACK_URL`: 回调地址（需在应用设置中配置）

2. **访问授权 URL**

在浏览器中访问上述 URL，登录微博账号并授权

3. **获取授权码**

授权后，浏览器会跳转到回调地址，URL 中包含 code 参数：
```
http://your-callback-url?code=AUTHORIZATION_CODE
```

4. **换取 Access Token**

使用以下 API 换取 access_token：

```bash
curl -X POST "https://api.weibo.com/oauth2/access_token" \
  -d "client_id=YOUR_APP_KEY" \
  -d "client_secret=YOUR_APP_SECRET" \
  -d "grant_type=authorization_code" \
  -d "code=AUTHORIZATION_CODE" \
  -d "redirect_uri=YOUR_CALLBACK_URL"
```

响应示例：
```json
{
  "access_token": "2.00xxx...",
  "expires_in": 157679999,
  "remind_in": "157679999",
  "uid": "1234567890"
}
```

### 5. 在 SubAlert 中配置

1. 登录 SubAlert
2. 进入"个人偏好设置"页面
3. 在"通知渠道配置"部分找到"微博 Access Token"
4. 填入获取到的 `access_token`
5. （可选）填入您的微博 `uid`
6. 保存设置

### 6. 设置默认提醒方式

在"提醒设置"部分：
1. 选择"默认提醒方式"为"📱 微博发布"
2. 或在创建/编辑订阅时单独设置

## 使用说明

### 提醒消息格式

微博提醒消息格式如下：
```
🔔 订阅到期提醒

📋 服务名称：[订阅名称]
⏰ 到期时间：[到期日期]
⏳ 剩余时间：[天数] 天

💡 请及时续费以免影响使用 #订阅管理 #SubAlert
```

### 注意事项

1. **Token 有效期**
   - Access Token 有一定的有效期（通常为几个月到几年）
   - Token 过期后需要重新授权获取
   - 建议保存 refresh_token 以便自动续期

2. **发布频率限制**
   - 微博 API 有频率限制（默认每小时 150 次）
   - 请合理设置提醒频率，避免超出限制

3. **内容审核**
   - 微博内容需遵守平台规则
   - 避免发布敏感或违规内容

4. **隐私保护**
   - Access Token 具有账号操作权限，请妥善保管
   - 不要将 Token 分享给他人
   - 定期检查授权应用列表

## 故障排查

### 问题：发布失败，提示 Token 无效

**解决方案：**
1. 检查 Access Token 是否正确
2. 确认 Token 是否已过期
3. 重新进行 OAuth 授权获取新 Token

### 问题：提示 API 调用次数超限

**解决方案：**
1. 减少提醒频率
2. 等待限制重置（通常为一小时）
3. 考虑使用其他提醒方式作为补充

### 问题：消息发布成功但在微博上看不到

**解决方案：**
1. 检查微博账号是否被限制
2. 确认消息内容是否违规
3. 查看微博的"草稿箱"或"审核中"

## API 参考

### 微博 API 文档

- [微博开放平台](https://open.weibo.com)
- [API 文档](https://open.weibo.com/wiki/API)
- [OAuth2.0 授权](https://open.weibo.com/wiki/Oauth2)

### 使用的 API 接口

- **statuses/share** - 发布微博
  - 端点：`https://api.weibo.com/2/statuses/share.json`
  - 方法：POST
  - 参数：
    - `access_token`: OAuth2.0 访问令牌
    - `status`: 要发布的微博内容

## 高级功能

### 自动刷新 Token

为避免 Token 过期，可以实现自动刷新机制：

```php
function refreshWeiboToken($refreshToken, $appKey, $appSecret) {
    $url = 'https://api.weibo.com/oauth2/access_token';
    $params = [
        'client_id' => $appKey,
        'client_secret' => $appSecret,
        'grant_type' => 'refresh_token',
        'refresh_token' => $refreshToken
    ];
    
    // 发送请求并返回新的 access_token
}
```

### 自定义发布内容

可以在代码中自定义微博消息模板（修改 `app/cron/send_reminders.php`）：

```php
$message = sprintf(
    "您的自定义消息格式\n订阅：%s\n到期：%s",
    $subscriptionName,
    $expireDate
);
```

## 安全建议

1. 使用 HTTPS 传输所有敏感信息
2. 在服务器端安全存储 Token（考虑加密）
3. 定期轮换 Access Token
4. 监控异常的 API 调用
5. 及时撤销不再使用的应用授权

## 支持

如有问题，请：
1. 查看微博开放平台官方文档
2. 在 SubAlert GitHub 仓库提交 Issue
3. 联系技术支持

---

**更新时间：** 2025-10-23
**版本：** 1.0.0
