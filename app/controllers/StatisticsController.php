<?php
namespace App\Controllers;

use App\Models\DB;

class StatisticsController
{
    public function index(): void
    {
        $user = current_user();
        $pdo = DB::getConnection();

        // Monthly expense for last 6 months
        $monthly = [];
        $stmt = $pdo->prepare('SELECT DATE_FORMAT(expire_at, "%Y-%m") as ym, SUM(price) as total FROM subscriptions WHERE user_id = ? AND expire_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY ym ORDER BY ym ASC');
        $stmt->execute([$user['id']]);
        $monthlyData = $stmt->fetchAll();
        foreach ($monthlyData as $row) {
            $monthly[$row['ym']] = floatval($row['total']);
        }
        // Fill missing months
        for ($i = 5; $i >= 0; $i--) {
            $ym = date('Y-m', strtotime('-' . $i . ' month'));
            if (!isset($monthly[$ym])) {
                $monthly[$ym] = 0;
            }
        }

        // Type distribution (sum price)
        $stmt = $pdo->prepare('SELECT type, SUM(price) as total FROM subscriptions WHERE user_id = ? GROUP BY type');
        $stmt->execute([$user['id']]);
        $typeData = $stmt->fetchAll();
        $types = [];
        foreach ($typeData as $row) {
            $types[$row['type']] = floatval($row['total']);
        }

        // Cycle distribution (count)
        $stmt = $pdo->prepare('SELECT cycle, COUNT(*) as cnt FROM subscriptions WHERE user_id = ? GROUP BY cycle');
        $stmt->execute([$user['id']]);
        $cycleData = $stmt->fetchAll();
        $cycles = [];
        foreach ($cycleData as $row) {
            $cycles[$row['cycle']] = intval($row['cnt']);
        }
        view('stats/index', [
            'monthly' => $monthly,
            'types'   => $types,
            'cycles'  => $cycles,
        ]);
    }

    /**
     * Export statistics as CSV.
     */
    public function export(): void
    {
        $user = current_user();
        $pdo = DB::getConnection();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="stats_' . date('YmdHis') . '.csv"');
        $output = fopen('php://output', 'w');
        // Monthly data
        fputcsv($output, ['类别','标签','数值']);
        $stmt = $pdo->prepare('SELECT DATE_FORMAT(expire_at, "%Y-%m") as ym, SUM(price) as total FROM subscriptions WHERE user_id = ? GROUP BY ym');
        $stmt->execute([$user['id']]);
        while ($row = $stmt->fetch()) {
            fputcsv($output, ['月份', $row['ym'], $row['total']]);
        }
        // Type data
        $stmt = $pdo->prepare('SELECT type, SUM(price) as total FROM subscriptions WHERE user_id = ? GROUP BY type');
        $stmt->execute([$user['id']]);
        while ($row = $stmt->fetch()) {
            fputcsv($output, ['类型', $row['type'], $row['total']]);
        }
        // Cycle data
        $stmt = $pdo->prepare('SELECT cycle, COUNT(*) as cnt FROM subscriptions WHERE user_id = ? GROUP BY cycle');
        $stmt->execute([$user['id']]);
        while ($row = $stmt->fetch()) {
            fputcsv($output, ['周期', $row['cycle'], $row['cnt']]);
        }
        fclose($output);
        exit;
    }
}