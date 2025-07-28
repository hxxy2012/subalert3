<?php
namespace App\Controllers;

use App\Models\DB;

/**
 * Manages CRUD operations for user subscriptions.
 */
class SubscriptionController
{
    /**
     * Display list of subscriptions.
     */
    public function index(): void
    {
        $user = current_user();
        $pdo = DB::getConnection();
        // Handle batch actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ids'])) {
            $ids = array_map('intval', (array)$_POST['ids']);
            $action = $_POST['batch_action'] ?? '';
            if (!empty($ids)) {
                if ($action === 'delete') {
                    $in = str_repeat('?,', count($ids) - 1) . '?';
                    $sql = "UPDATE subscriptions SET status='deleted' WHERE user_id=? AND id IN ($in)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(array_merge([$user['id']], $ids));
                    flash('success', '已批量删除选中的订阅');
                } elseif ($action === 'restore') {
                    $in = str_repeat('?,', count($ids) - 1) . '?';
                    $sql = "UPDATE subscriptions SET status='active' WHERE user_id=? AND id IN ($in)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(array_merge([$user['id']], $ids));
                    flash('success', '已恢复选中的订阅');
                }
            }
            redirect('/?r=subscriptions');
            return;
        }
        // Filtering
        $type = $_GET['type'] ?? '';
        $status = $_GET['status'] ?? '';
        $query = 'SELECT * FROM subscriptions WHERE user_id = ?';
        $params = [$user['id']];
        if ($type) {
            $query .= ' AND type = ?';
            $params[] = $type;
        }
        if ($status) {
            $query .= ' AND status = ?';
            $params[] = $status;
        } else {
            $query .= ' AND status != ?';
            $params[] = 'deleted';
        }
        $query .= ' ORDER BY expire_at ASC';
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $list = $stmt->fetchAll();
        view('subscriptions/index', ['subscriptions' => $list]);
    }

    /**
     * Add new subscription.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = current_user();
            $name   = trim($_POST['name'] ?? '');
            $type   = trim($_POST['type'] ?? '');
            $price  = floatval($_POST['price'] ?? 0);
            $cycle  = trim($_POST['cycle'] ?? '');
            $expire = trim($_POST['expire_at'] ?? '');
            $auto   = isset($_POST['auto_renew']) ? 1 : 0;
            $note   = trim($_POST['note'] ?? '');
            // Validation
            if ($name === '' || $type === '' || $price <= 0 || $cycle === '' || $expire === '') {
                flash('error', '请填写所有必填字段并保证价格大于0');
                view('subscriptions/create');
                return;
            }
            // Convert expire date
            $expireDate = date('Y-m-d', strtotime($expire));
            // Insert
            $pdo = DB::getConnection();
            $stmt = $pdo->prepare('INSERT INTO subscriptions (user_id, name, type, price, cycle, expire_at, auto_renew, status, note, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
            $status = 'active';
            $stmt->execute([$user['id'], $name, $type, $price, $cycle, $expireDate, $auto, $status, $note]);
            flash('success', '订阅添加成功');
            redirect('/?r=subscriptions');
        } else {
            view('subscriptions/create');
        }
    }

    /**
     * Edit existing subscription.
     */
    public function edit(): void
    {
        $id = intval($_GET['id'] ?? 0);
        $user = current_user();
        $pdo = DB::getConnection();
        // Fetch subscription
        $stmt = $pdo->prepare('SELECT * FROM subscriptions WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $user['id']]);
        $subscription = $stmt->fetch();
        if (!$subscription) {
            flash('error', '订阅不存在');
            redirect('/?r=subscriptions');
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name   = trim($_POST['name'] ?? '');
            $type   = trim($_POST['type'] ?? '');
            $price  = floatval($_POST['price'] ?? 0);
            $cycle  = trim($_POST['cycle'] ?? '');
            $expire = trim($_POST['expire_at'] ?? '');
            $auto   = isset($_POST['auto_renew']) ? 1 : 0;
            $status = trim($_POST['status'] ?? 'active');
            $note   = trim($_POST['note'] ?? '');
            if ($name === '' || $type === '' || $price <= 0 || $cycle === '' || $expire === '') {
                flash('error', '请填写所有必填字段并保证价格大于0');
                view('subscriptions/edit', ['subscription' => $subscription]);
                return;
            }
            $expireDate = date('Y-m-d', strtotime($expire));
            $stmt = $pdo->prepare('UPDATE subscriptions SET name=?, type=?, price=?, cycle=?, expire_at=?, auto_renew=?, status=?, note=?, updated_at=NOW() WHERE id=? AND user_id=?');
            $stmt->execute([$name, $type, $price, $cycle, $expireDate, $auto, $status, $note, $id, $user['id']]);
            flash('success', '订阅更新成功');
            redirect('/?r=subscriptions');
        } else {
            view('subscriptions/edit', ['subscription' => $subscription]);
        }
    }

    /**
     * Soft delete subscription.
     */
    public function delete(): void
    {
        $id = intval($_GET['id'] ?? 0);
        $user = current_user();
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('UPDATE subscriptions SET status = ? WHERE id = ? AND user_id = ?');
        $stmt->execute(['deleted', $id, $user['id']]);
        flash('success', '订阅已删除');
        redirect('/?r=subscriptions');
    }

    /**
     * List deleted subscriptions and allow restore.
     */
    public function deleted(): void
    {
        $user = current_user();
        $pdo = DB::getConnection();
        // Handle batch restore
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ids'])) {
            $ids = array_map('intval', (array)$_POST['ids']);
            $action = $_POST['batch_action'] ?? '';
            if ($action === 'restore' && !empty($ids)) {
                $in = str_repeat('?,', count($ids) - 1) . '?';
                $sql = "UPDATE subscriptions SET status='active' WHERE user_id=? AND id IN ($in)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array_merge([$user['id']], $ids));
                flash('success', '已恢复选中的订阅');
                redirect('/?r=subscriptions-deleted');
                return;
            }
        }
        // Fetch deleted subscriptions
        $stmt = $pdo->prepare('SELECT * FROM subscriptions WHERE user_id = ? AND status = ? ORDER BY expire_at ASC');
        $stmt->execute([$user['id'], 'deleted']);
        $list = $stmt->fetchAll();
        view('subscriptions/deleted', ['subscriptions' => $list]);
    }
}