<?php
namespace App\Controllers\Admin;

use App\Models\DB;

class AdminDashboardController
{
    public function index(): void
    {
        $pdo = DB::getConnection();
        // Count users
        $userCnt = $pdo->query('SELECT COUNT(*) as cnt FROM users')->fetch()['cnt'] ?? 0;
        // Count subscriptions
        $subCnt  = $pdo->query('SELECT COUNT(*) as cnt FROM subscriptions')->fetch()['cnt'] ?? 0;
        // Count reminders by status
        $reminderStmt = $pdo->query('SELECT status, COUNT(*) as cnt FROM reminders GROUP BY status');
        $remCounts = [];
        while ($row = $reminderStmt->fetch()) {
            $remCounts[$row['status']] = (int)$row['cnt'];
        }
        // Count templates
        $templateCnt = $pdo->query('SELECT COUNT(*) as cnt FROM templates')->fetch()['cnt'] ?? 0;
        // Count backup files in public/backups
        $backupDir = __DIR__ . '/../../../public/backups';
        $backupCnt = 0;
        if (is_dir($backupDir)) {
            foreach (scandir($backupDir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                if (preg_match('/\.sql$/i', $file)) {
                    $backupCnt++;
                }
            }
        }
        // Site name from settings
        $siteStmt = $pdo->prepare('SELECT value FROM settings WHERE `key` = ?');
        $siteStmt->execute(['site_name']);
        $siteNameRow = $siteStmt->fetch();
        $siteName = $siteNameRow['value'] ?? 'SubAlert';
        view('admin/dashboard', [
            'userCnt'    => $userCnt,
            'subCnt'     => $subCnt,
            'remCounts'  => $remCounts,
            'templateCnt'=> $templateCnt,
            'backupCnt'  => $backupCnt,
            'siteName'   => $siteName,
        ]);
    }
}