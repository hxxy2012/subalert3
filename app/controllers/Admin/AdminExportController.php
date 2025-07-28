<?php
namespace App\Controllers\Admin;

use App\Models\DB;

/**
 * Controller providing data export functionality for admin.
 */
class AdminExportController
{
    /**
     * Export subscriptions data as CSV.
     */
    public function exportSubscriptions(): void
    {
        $pdo = DB::getConnection();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="subscriptions_' . date('YmdHis') . '.csv"');
        $output = fopen('php://output', 'w');
        // Header
        fputcsv($output, ['ID','用户ID','订阅名称','类型','价格','周期','到期时间','自动续费','状态','创建时间','更新时间']);
        $stmt = $pdo->query('SELECT id, user_id, name, type, price, cycle, expire_at, auto_renew, status, created_at, updated_at FROM subscriptions');
        while ($row = $stmt->fetch()) {
            fputcsv($output, [
                $row['id'],
                $row['user_id'],
                $row['name'],
                $row['type'],
                $row['price'],
                $row['cycle'],
                $row['expire_at'],
                $row['auto_renew'] ? '是' : '否',
                $row['status'],
                $row['created_at'],
                $row['updated_at'],
            ]);
        }
        fclose($output);
        log_admin_action('export_subscriptions', '导出订阅数据');
        exit;
    }

    /**
     * Export reminders data as CSV.
     */
    public function exportReminders(): void
    {
        $pdo = DB::getConnection();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="reminders_' . date('YmdHis') . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID','用户ID','订阅ID','订阅名称','提前天数','提醒方式','提醒时间','状态','发送时间','创建时间','更新时间']);
        $stmt = $pdo->query('SELECT r.id, r.user_id, r.subscription_id, s.name as subscription_name, r.remind_days, r.remind_type, r.remind_at, r.status, r.sent_at, r.created_at, r.updated_at FROM reminders r LEFT JOIN subscriptions s ON r.subscription_id = s.id');
        while ($row = $stmt->fetch()) {
            fputcsv($output, [
                $row['id'],
                $row['user_id'],
                $row['subscription_id'],
                $row['subscription_name'],
                $row['remind_days'],
                $row['remind_type'],
                $row['remind_at'],
                $row['status'],
                $row['sent_at'],
                $row['created_at'],
                $row['updated_at'],
            ]);
        }
        fclose($output);
        log_admin_action('export_reminders', '导出提醒数据');
        exit;
    }
}