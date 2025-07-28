<?php
namespace App\Controllers\Admin;

use App\Models\DB;

class AdminLogController
{
    public function index(): void
    {
        $pdo = DB::getConnection();
        // Fetch list of admins for filter dropdown
        $adminStmt = $pdo->query('SELECT id, username FROM admin_users');
        $admins = $adminStmt->fetchAll();
        // Read filter parameters
        $actionFilter = trim($_GET['action'] ?? '');
        $adminFilter  = trim($_GET['admin_id'] ?? '');
        $dateFrom     = trim($_GET['date_from'] ?? '');
        $dateTo       = trim($_GET['date_to'] ?? '');
        $where  = '1=1';
        $params = [];
        if ($actionFilter !== '') {
            $where .= ' AND l.action LIKE ?';
            $params[] = '%' . $actionFilter . '%';
        }
        if ($adminFilter !== '') {
            $where .= ' AND l.admin_id = ?';
            $params[] = intval($adminFilter);
        }
        if ($dateFrom !== '') {
            $where .= ' AND l.created_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo !== '') {
            $where .= ' AND l.created_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }
        $query = 'SELECT l.*, a.username FROM admin_logs l LEFT JOIN admin_users a ON l.admin_id = a.id WHERE ' . $where . ' ORDER BY l.created_at DESC LIMIT 200';
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();
        view('admin/logs', [
            'logs'    => $logs,
            'admins'  => $admins,
            'filters' => [
                'action'   => $actionFilter,
                'admin_id' => $adminFilter,
                'date_from'=> $dateFrom,
                'date_to'  => $dateTo,
            ],
        ]);
    }
}