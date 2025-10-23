-- Migration script to add Weibo support to existing databases
-- Run this script if you already have a database set up before this update

-- Add 'weibo' to reminders.remind_type enum
ALTER TABLE `reminders` 
MODIFY COLUMN `remind_type` ENUM('email','feishu','wechat','site','weibo') NOT NULL;

-- Add 'weibo' to templates.type enum
ALTER TABLE `templates` 
MODIFY COLUMN `type` ENUM('email','feishu','wechat','site','weibo') NOT NULL;

-- No need to modify user_settings as it's a key-value store and can accept new keys dynamically
-- The following settings will be used for Weibo:
-- - weibo_access_token: OAuth access token from Weibo Open Platform
-- - weibo_uid: Weibo user ID (optional, for verification)
