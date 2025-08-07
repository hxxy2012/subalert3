<?php
namespace App\Controllers\Admin;

use App\Models\DB;

class AdminSettingsController
{
    public function index(): void
    {
        $pdo = DB::getConnection();
        // Load settings
        $settings = [];
        $stmt = $pdo->query('SELECT `key`, `value` FROM settings');
        while ($row = $stmt->fetch()) {
            $settings[$row['key']] = $row['value'];
        }
        
        $siteName = $settings['site_name'] ?? 'SubAlert';
        $smtpHost = $settings['smtp_host'] ?? '';
        $smtpPort = $settings['smtp_port'] ?? '';
        $smtpUser = $settings['smtp_user'] ?? '';
        $smtpPass = $settings['smtp_pass'] ?? '';
        $defaultFromEmail = $settings['default_from_email'] ?? '';
        $sesFromEmail = $settings['ses_from_email'] ?? '';
        $emailServiceType = $settings['email_service_type'] ?? 'auto';

// 在表单处理部分，添加新字段的保存：
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $siteName = trim($_POST['site_name'] ?? '');
            $smtpHost = trim($_POST['smtp_host'] ?? '');
            $smtpPort = trim($_POST['smtp_port'] ?? '');
            $smtpUser = trim($_POST['smtp_user'] ?? '');
            $smtpPass = trim($_POST['smtp_pass'] ?? '');
            $defaultFromEmail = trim($_POST['default_from_email'] ?? '');
            $sesFromEmail = trim($_POST['ses_from_email'] ?? '');
            $emailServiceType = trim($_POST['email_service_type'] ?? 'auto');
            
            $this->saveSetting($pdo, 'site_name', $siteName);
            $this->saveSetting($pdo, 'smtp_host', $smtpHost);
            $this->saveSetting($pdo, 'smtp_port', $smtpPort);
            $this->saveSetting($pdo, 'smtp_user', $smtpUser);
            $this->saveSetting($pdo, 'smtp_pass', $smtpPass);
            $this->saveSetting($pdo, 'default_from_email', $defaultFromEmail);
            $this->saveSetting($pdo, 'ses_from_email', $sesFromEmail);
            $this->saveSetting($pdo, 'email_service_type', $emailServiceType);
            
            log_admin_action('update_settings', '更新系统设置');
            flash('success', '系统设置已保存');
            redirect('/admin.php?r=settings');
            return;
        }

// 在视图传递部分，添加新变量：
        view('admin/settings', [
            'siteName' => $siteName,
            'smtpHost' => $smtpHost,
            'smtpPort' => $smtpPort,
            'smtpUser' => $smtpUser,
            'smtpPass' => $smtpPass,
            'defaultFromEmail' => $defaultFromEmail,
            'sesFromEmail' => $sesFromEmail,
            'emailServiceType' => $emailServiceType,
        ]);
    }

    private function saveSetting($pdo, string $key, string $value): void
    {
        $stmt = $pdo->prepare('SELECT id FROM settings WHERE `key` = ?');
        $stmt->execute([$key]);
        $exists = $stmt->fetch();
        if ($exists) {
            $stmt = $pdo->prepare('UPDATE settings SET `value`=?, updated_at=NOW() WHERE id=?');
            $stmt->execute([$value, $exists['id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO settings (`key`, `value`, created_at, updated_at) VALUES (?, ?, NOW(), NOW())');
            $stmt->execute([$key, $value]);
        }
    }
}