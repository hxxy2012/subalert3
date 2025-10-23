# Weibo 自动发布功能

## 概述

本次更新为 SubAlert 订阅管理系统添加了微博自动发布功能。现在用户可以选择通过微博发布订阅到期提醒。

## 功能特性

- ✅ 支持通过微博 API 自动发布提醒消息
- ✅ 与现有提醒系统（邮件、飞书、企业微信、站内消息）无缝集成
- ✅ 支持 OAuth2.0 安全认证
- ✅ 提供详细的配置文档
- ✅ 包含数据库迁移脚本

## 更改的文件

### 数据库相关
- `schema.sql` - 在 `reminders.remind_type` 和 `templates.type` 枚举中添加 'weibo' 选项
- `migrations/add_weibo_support.sql` - 用于更新现有数据库的迁移脚本

### 后端代码
- `app/cron/send_reminders.php` - 添加 `sendWeibo()` 函数和微博发送逻辑
- `app/controllers/SettingsController.php` - 添加微博配置字段处理

### 前端视图
- `app/views/settings/index.php` - 添加微博配置表单和帮助文档
- `app/views/reminders/form.php` - 在提醒方式下拉菜单中添加微博选项
- `app/views/subscriptions/create.php` - 在订阅创建表单中添加微博选项
- `app/views/subscriptions/edit.php` - 在订阅编辑表单中添加微博选项

### 文档
- `docs/WEIBO_SETUP.md` - 详细的微博配置指南
- `README_WEIBO_FEATURE.md` - 本文件，功能说明

### 测试
- `tests/test_weibo_integration.php` - 微博集成测试脚本

## 安装/升级说明

### 新安装

如果您正在进行全新安装，只需使用更新后的 `schema.sql` 创建数据库即可。

```bash
mysql -u your_user -p your_database < schema.sql
```

### 从旧版本升级

如果您已经有一个运行中的 SubAlert 实例，请运行迁移脚本：

```bash
mysql -u your_user -p your_database < migrations/add_weibo_support.sql
```

## 配置指南

详细的配置步骤请参阅：[docs/WEIBO_SETUP.md](docs/WEIBO_SETUP.md)

### 快速开始

1. **在微博开放平台创建应用**
   - 访问 https://open.weibo.com
   - 注册并创建应用
   - 获取 App Key 和 App Secret

2. **获取 Access Token**
   - 通过 OAuth2.0 授权流程获取 access_token
   - 详细步骤见配置文档

3. **在 SubAlert 中配置**
   - 登录 SubAlert
   - 进入"个人偏好设置"
   - 在"通知渠道配置"中填入 Access Token
   - 选择"微博发布"作为提醒方式

## 使用方法

### 设置默认提醒方式

1. 进入"个人偏好设置"
2. 在"提醒设置"部分选择"微博发布"
3. 保存设置

### 为单个订阅设置微博提醒

1. 创建或编辑订阅时
2. 在提醒设置部分选择"微博发布"
3. 保存订阅

### 微博消息格式

```
🔔 订阅到期提醒

📋 服务名称：[订阅名称]
⏰ 到期时间：[到期日期]
⏳ 剩余时间：[天数] 天

💡 请及时续费以免影响使用 #订阅管理 #SubAlert
```

## 技术实现

### API 调用

使用微博开放平台的 `statuses/share.json` API 接口：

```php
POST https://api.weibo.com/2/statuses/share.json
Parameters:
  - access_token: OAuth2.0 访问令牌
  - status: 要发布的微博内容
```

### 数据存储

微博相关配置存储在 `user_settings` 表中：
- `weibo_access_token`: OAuth access token
- `weibo_uid`: 微博用户 ID（可选）

### 安全考虑

- Access Token 以明文存储在数据库中（建议后续加密）
- 使用 HTTPS 与微博 API 通信
- 验证 SSL 证书
- 错误信息记录到日志，不暴露给前端

## 测试

运行测试脚本验证功能：

```bash
php tests/test_weibo_integration.php
```

测试内容包括：
- ✅ 有效的消息和 token
- ✅ 空 token 处理
- ✅ 空消息处理
- ✅ 消息长度验证

## 注意事项

1. **Token 有效期**
   - Access Token 会过期，需要定期更新
   - 建议实现自动刷新机制

2. **API 限制**
   - 微博 API 有频率限制（默认每小时 150 次）
   - 请合理设置提醒频率

3. **内容审核**
   - 微博内容需遵守平台规则
   - 违规内容可能导致发布失败或账号受限

4. **隐私安全**
   - Access Token 具有账号操作权限
   - 请妥善保管，不要分享给他人

## 故障排查

### 问题：微博发送失败

**检查项：**
1. Access Token 是否有效
2. 网络连接是否正常
3. 微博 API 是否可访问
4. 查看日志文件中的错误信息

### 问题：看不到微博设置选项

**解决方案：**
1. 确认已运行数据库迁移脚本
2. 清除浏览器缓存
3. 检查文件是否正确部署

## 未来改进

- [ ] 实现 Token 自动刷新机制
- [ ] 支持发布带图片的微博
- [ ] 添加微博发布历史记录
- [ ] 实现 Token 加密存储
- [ ] 支持多账号发布
- [ ] 添加微博发布预览功能

## 贡献

欢迎提交 Issue 和 Pull Request 来改进此功能！

## 许可

与 SubAlert 主项目保持一致

## 支持

如有问题，请：
1. 查看 [docs/WEIBO_SETUP.md](docs/WEIBO_SETUP.md)
2. 在 GitHub 上提交 Issue
3. 联系项目维护者

---

**版本：** 1.0.0  
**更新日期：** 2025-10-23  
**作者：** SubAlert 开发团队
