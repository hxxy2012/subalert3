<?php
namespace App\Controllers\Admin;

use App\Models\DB;

class AdminStatsController
{
    public function index(): void
    {
        $pdo = DB::getConnection();
        // User growth: count new users per month for last 6 months
        $userData = [];
        $stmt = $pdo->prepare('SELECT DATE_FORMAT(created_at, "%Y-%m") as ym, COUNT(*) as cnt FROM users WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY ym ORDER BY ym');
        $stmt->execute();
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $userData[$row['ym']] = (int)$row['cnt'];
        }
        // fill missing months
        for ($i = 5; $i >= 0; $i--) {
            $ym = date('Y-m', strtotime('-' . $i . ' month'));
            if (!isset($userData[$ym])) {
                $userData[$ym] = 0;
            }
        }
        // Subscription count per month
        $subData = [];
        $stmt = $pdo->prepare('SELECT DATE_FORMAT(created_at, "%Y-%m") as ym, COUNT(*) as cnt FROM subscriptions WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY ym ORDER BY ym');
        $stmt->execute();
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $subData[$row['ym']] = (int)$row['cnt'];
        }
        for ($i = 5; $i >= 0; $i--) {
            $ym = date('Y-m', strtotime('-' . $i . ' month'));
            if (!isset($subData[$ym])) {
                $subData[$ym] = 0;
            }
        }
        // User login statistics (last 6 months)
        $loginData = [];
        $stmt = $pdo->prepare('SELECT DATE_FORMAT(last_login_at, "%Y-%m") as ym, COUNT(*) as cnt FROM users WHERE last_login_at IS NOT NULL AND last_login_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY ym ORDER BY ym');
        $stmt->execute();
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $loginData[$row['ym']] = (int)$row['cnt'];
        }
        for ($i = 5; $i >= 0; $i--) {
            $ym = date('Y-m', strtotime('-' . $i . ' month'));
            if (!isset($loginData[$ym])) {
                $loginData[$ym] = 0;
            }
        }
        // Subscription type distribution
        $typeData = [];
        $stmt = $pdo->query('SELECT type, COUNT(*) as cnt FROM subscriptions GROUP BY type');
        while ($row = $stmt->fetch()) {
            $typeData[$row['type']] = (int)$row['cnt'];
        }
        // Price statistics
        $stmt = $pdo->query('SELECT MAX(price) as max_price, MIN(price) as min_price, AVG(price) as avg_price FROM subscriptions');
        $priceStats = $stmt->fetch();
        // Reminder status distribution
        $reminderData = [];
        $stmt = $pdo->query('SELECT status, COUNT(*) as cnt FROM reminders GROUP BY status');
        while ($row = $stmt->fetch()) {
            $reminderData[$row['status']] = (int)$row['cnt'];
        }
        view('admin/stats', [
            'userData'      => $userData,
            'subData'       => $subData,
            'loginData'     => $loginData,
            'typeData'      => $typeData,
            'priceStats'    => $priceStats,
            'reminderData'  => $reminderData,
        ]);
    }
}