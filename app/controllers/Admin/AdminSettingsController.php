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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $siteName = trim($_POST['site_name'] ?? '');
            $smtpHost = trim($_POST['smtp_host'] ?? '');
            $smtpPort = trim($_POST['smtp_port'] ?? '');
            $smtpUser = trim($_POST['smtp_user'] ?? '');
            $smtpPass = trim($_POST['smtp_pass'] ?? '');
            $this->saveSetting($pdo, 'site_name', $siteName);
            $this->saveSetting($pdo, 'smtp_host', $smtpHost);
            $this->saveSetting($pdo, 'smtp_port', $smtpPort);
            $this->saveSetting($pdo, 'smtp_user', $smtpUser);
            $this->saveSetting($pdo, 'smtp_pass', $smtpPass);
            log_admin_action('update_settings', '更新系统设置');
            flash('success', '系统设置已保存');
            redirect('/admin.php?r=settings');
            return;
        }
        view('admin/settings', [
            'siteName' => $siteName,
            'smtpHost' => $smtpHost,
            'smtpPort' => $smtpPort,
            'smtpUser' => $smtpUser,
            'smtpPass' => $smtpPass,
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