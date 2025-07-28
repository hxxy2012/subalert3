<?php
namespace App\Controllers;

use App\Models\DB;

/**
 * DashboardController renders the user dashboard with a summary of subscriptions.
 */
class DashboardController
{
    /**
     * Show dashboard.
     */
    public function index(): void
    {
        $user = current_user();
        $pdo = DB::getConnection();
        // Total subscription count
        $stmt = $pdo->prepare('SELECT COUNT(*) as cnt FROM subscriptions WHERE user_id = ? AND status != ?');
        $stmt->execute([$user['id'], 'deleted']);
        $total = $stmt->fetch()['cnt'] ?? 0;
        // Upcoming expiry within 7 days
        $stmt = $pdo->prepare('SELECT * FROM subscriptions WHERE user_id = ? AND expire_at IS NOT NULL AND expire_at <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND status = ? ORDER BY expire_at ASC LIMIT 5');
        $stmt->execute([$user['id'], 'active']);
        $upcoming = $stmt->fetchAll();
        view('dashboard/index', [
            'total'    => $total,
            'upcoming' => $upcoming,
        ]);
    }
}