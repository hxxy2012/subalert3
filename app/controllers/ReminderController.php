<?php
namespace App\Controllers;

use App\Models\DB;

class ReminderController
{
    /**
     * List all reminders for current user.
     */
    public function index(): void
    {
        $user = current_user();
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('SELECT r.*, s.name as subscription_name, s.expire_at as subscription_expire, s.price as subscription_price FROM reminders r JOIN subscriptions s ON r.subscription_id = s.id WHERE r.user_id = ? ORDER BY r.remind_at ASC');
        $stmt->execute([$user['id']]);
        $list = $stmt->fetchAll();
        view('reminders/index', ['reminders' => $list]);
    }

    /**
     * Create or update reminder for a subscription.
     */
    public function create(): void
    {
        $user = current_user();
        $subscriptionId = intval($_GET['id'] ?? 0);
        $pdo = DB::getConnection();
        // Verify subscription belongs to user
        $stmt = $pdo->prepare('SELECT * FROM subscriptions WHERE id = ? AND user_id = ?');
        $stmt->execute([$subscriptionId, $user['id']]);
        $subscription = $stmt->fetch();
        if (!$subscription) {
            flash('error', '订阅不存在');
            redirect('/?r=subscriptions');
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $remind_days = intval($_POST['remind_days'] ?? 1);
            $remind_type = trim($_POST['remind_type'] ?? 'email');
            // Calculate remind_at
            $expireAt = $subscription['expire_at'];
            $remindAt = date('Y-m-d H:i:s', strtotime($expireAt . ' -' . $remind_days . ' day'));
            // Check if reminder already exists
            $stmt = $pdo->prepare('SELECT id FROM reminders WHERE subscription_id = ?');
            $stmt->execute([$subscriptionId]);
            $existing = $stmt->fetch();
            if ($existing) {
                // Update existing reminder
                $stmt = $pdo->prepare('UPDATE reminders SET remind_days=?, remind_type=?, remind_at=?, updated_at=NOW(), status=? WHERE id=?');
                $stmt->execute([$remind_days, $remind_type, $remindAt, 'pending', $existing['id']]);
            } else {
                // Insert new reminder
                $stmt = $pdo->prepare('INSERT INTO reminders (user_id, subscription_id, remind_days, remind_type, remind_at, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())');
                $stmt->execute([$user['id'], $subscriptionId, $remind_days, $remind_type, $remindAt, 'pending']);
            }
            flash('success', '提醒设置成功');
            redirect('/?r=reminders');
        } else {
            // Load user default settings
            $settings = [];
            $setStmt = $pdo->prepare('SELECT setting_key, setting_value FROM user_settings WHERE user_id = ?');
            $setStmt->execute([$user['id']]);
            while ($row = $setStmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            $defaultDays = $settings['default_remind_days'] ?? 3;
            $defaultType = $settings['default_remind_type'] ?? 'email';
            view('reminders/form', ['subscription' => $subscription, 'defaultDays' => $defaultDays, 'defaultType' => $defaultType]);
        }
    }

    /**
     * Process reminder actions: renew, delay, cancel, read.
     */
    public function action(): void
    {
        $user = current_user();
        $id = intval($_GET['id'] ?? 0);
        $op = $_GET['op'] ?? '';
        $pdo = DB::getConnection();
        // Fetch reminder and subscription
        $stmt = $pdo->prepare('SELECT r.*, s.* FROM reminders r JOIN subscriptions s ON r.subscription_id = s.id WHERE r.id = ? AND r.user_id = ?');
        $stmt->execute([$id, $user['id']]);
        $data = $stmt->fetch();
        if (!$data) {
            flash('error', '提醒不存在');
            redirect('/?r=reminders');
            return;
        }
        switch ($op) {
            case 'renew':
                // Extend expire_at by subscription cycle
                $expireAt = $data['expire_at'];
                switch ($data['cycle']) {
                    case 'monthly':
                        $newExpire = date('Y-m-d', strtotime($expireAt . ' +1 month'));
                        break;
                    case 'quarterly':
                        $newExpire = date('Y-m-d', strtotime($expireAt . ' +3 month'));
                        break;
                    case 'yearly':
                        $newExpire = date('Y-m-d', strtotime($expireAt . ' +1 year'));
                        break;
                    default:
                        // custom cycle: default to 1 month
                        $newExpire = date('Y-m-d', strtotime($expireAt . ' +1 month'));
                        break;
                }
                // Update subscription expire_at
                $stmt = $pdo->prepare('UPDATE subscriptions SET expire_at=?, status=? WHERE id=?');
                $stmt->execute([$newExpire, 'active', $data['subscription_id']]);
                // Update reminder status to done
                $stmt = $pdo->prepare('UPDATE reminders SET status=?, updated_at=NOW() WHERE id=?');
                $stmt->execute(['done', $id]);
                flash('success', '已续费并更新订阅到期日');
                break;
            case 'delay':
                // Delay by 3 days
                $newRemindAt = date('Y-m-d H:i:s', strtotime($data['remind_at'] . ' +3 day'));
                $stmt = $pdo->prepare('UPDATE reminders SET remind_at=?, status=?, updated_at=NOW() WHERE id=?');
                $stmt->execute([$newRemindAt, 'pending', $id]);
                flash('success', '已延迟提醒');
                break;
            case 'cancel':
                $stmt = $pdo->prepare('UPDATE reminders SET status=?, updated_at=NOW() WHERE id=?');
                $stmt->execute(['cancelled', $id]);
                flash('success', '已取消提醒');
                break;
            case 'read':
                $stmt = $pdo->prepare('UPDATE reminders SET status=?, updated_at=NOW() WHERE id=?');
                $stmt->execute(['read', $id]);
                flash('success', '已标记为已读');
                break;
            default:
                flash('error', '未知操作');
                break;
        }
        redirect('/?r=reminders');
    }
}