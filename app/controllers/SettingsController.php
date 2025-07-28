<?php
namespace App\Controllers;

use App\Models\DB;

/**
 * Handles user-specific settings such as default reminder preferences.
 */
class SettingsController
{
    /**
     * Display and update settings.
     */
    public function index(): void
    {
        $user = current_user();
        $pdo = DB::getConnection();
        // Fetch existing settings for user
        $settings = [];
        $stmt = $pdo->prepare('SELECT setting_key, setting_value FROM user_settings WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        // Initialize default values
        $defaultDays  = $settings['default_remind_days'] ?? '3';
        $defaultType  = $settings['default_remind_type'] ?? 'email';
        $feishuWebhook = $settings['feishu_webhook'] ?? '';
        $wechatWebhook = $settings['wechat_webhook'] ?? '';
        $muteStart    = $settings['mute_start'] ?? '';
        $muteEnd      = $settings['mute_end'] ?? '';
        $remindersEnabled = $settings['reminders_enabled'] ?? '1';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $defaultDays  = intval($_POST['default_remind_days'] ?? 3);
            $defaultType  = trim($_POST['default_remind_type'] ?? 'email');
            $feishuWebhook = trim($_POST['feishu_webhook'] ?? '');
            $wechatWebhook = trim($_POST['wechat_webhook'] ?? '');
            $muteStart    = trim($_POST['mute_start'] ?? '');
            $muteEnd      = trim($_POST['mute_end'] ?? '');
            $remindersEnabled = isset($_POST['reminders_enabled']) ? '1' : '0';
            // Upsert settings
            $this->saveSetting($pdo, $user['id'], 'default_remind_days', $defaultDays);
            $this->saveSetting($pdo, $user['id'], 'default_remind_type', $defaultType);
            $this->saveSetting($pdo, $user['id'], 'feishu_webhook', $feishuWebhook);
            $this->saveSetting($pdo, $user['id'], 'wechat_webhook', $wechatWebhook);
            $this->saveSetting($pdo, $user['id'], 'mute_start', $muteStart);
            $this->saveSetting($pdo, $user['id'], 'mute_end', $muteEnd);
            $this->saveSetting($pdo, $user['id'], 'reminders_enabled', $remindersEnabled);
            flash('success', '设置已更新');
            redirect('/?r=settings');
        } else {
            view('settings/index', [
                'defaultDays' => $defaultDays,
                'defaultType' => $defaultType,
                'feishuWebhook' => $feishuWebhook,
                'wechatWebhook' => $wechatWebhook,
                'muteStart' => $muteStart,
                'muteEnd' => $muteEnd,
                'remindersEnabled' => $remindersEnabled
            ]);
        }
    }

    /**
     * Save or update a setting.
     */
    private function saveSetting($pdo, int $userId, string $key, $value): void
    {
        // Check existing
        $stmt = $pdo->prepare('SELECT id FROM user_settings WHERE user_id = ? AND setting_key = ?');
        $stmt->execute([$userId, $key]);
        $exists = $stmt->fetch();
        if ($exists) {
            $stmt = $pdo->prepare('UPDATE user_settings SET setting_value=?, updated_at=NOW() WHERE id=?');
            $stmt->execute([strval($value), $exists['id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO user_settings (user_id, setting_key, setting_value, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())');
            $stmt->execute([$userId, $key, strval($value)]);
        }
    }
}