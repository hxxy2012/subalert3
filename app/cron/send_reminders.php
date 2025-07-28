<?php
// Script to send reminders (simulate). Should be run via CLI or scheduled cron.

require __DIR__ . '/../../app/models/DB.php';

use App\Models\DB;

/**
 * Send a text message to a Feishu (Lark) webhook.
 *
 * @param string $webhook Webhook URL
 * @param string $content Message content
 * @return bool True on success, false on failure
 */
function sendFeishu(string $webhook, string $content): bool
{
    if (empty($webhook)) {
        return false;
    }
    $payload = json_encode([
        'msg_type' => 'text',
        'content'  => ['text' => $content],
    ], JSON_UNESCAPED_UNICODE);
    $ch = curl_init($webhook);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $status >= 200 && $status < 300;
}

/**
 * Send a text message to an Enterprise WeChat webhook.
 *
 * @param string $webhook Webhook URL
 * @param string $content Message content
 * @return bool True on success, false on failure
 */
function sendWeChat(string $webhook, string $content): bool
{
    if (empty($webhook)) {
        return false;
    }
    $payload = json_encode([
        'msgtype' => 'text',
        'text'    => ['content' => $content],
    ], JSON_UNESCAPED_UNICODE);
    $ch = curl_init($webhook);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $status >= 200 && $status < 300;
}

// Only run via CLI
if (php_sapi_name() !== 'cli') {
    exit("This script must be run from command line\n");
}

$pdo = DB::getConnection();
$now = date('Y-m-d H:i:s');
// Fetch pending reminders along with user ID
$stmt = $pdo->prepare('SELECT r.*, u.id AS user_id, u.email, s.name as subscription_name FROM reminders r JOIN users u ON r.user_id = u.id JOIN subscriptions s ON r.subscription_id = s.id WHERE r.status = ? AND r.remind_at <= ?');
$stmt->execute(['pending', $now]);
$reminders = $stmt->fetchAll();
$processed = 0;

foreach ($reminders as $rem) {
    // Load user settings once per user
    static $settingsCache = [];
    $uid = $rem['user_id'];
    if (!isset($settingsCache[$uid])) {
        $settings = [];
        $userStmt = $pdo->prepare('SELECT setting_key, setting_value FROM user_settings WHERE user_id = ?');
        $userStmt->execute([$uid]);
        while ($row = $userStmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        $settingsCache[$uid] = $settings;
    } else {
        $settings = $settingsCache[$uid];
    }

    // Check global reminders enabled
    if (isset($settings['reminders_enabled']) && $settings['reminders_enabled'] === '0') {
        continue;
    }

    // Check mute period (HH:MM format)
    $muteStart = $settings['mute_start'] ?? '';
    $muteEnd   = $settings['mute_end'] ?? '';
    if ($muteStart && $muteEnd) {
        $currentTime = date('H:i');
        if ($muteStart <= $muteEnd) {
            // Normal range
            if ($currentTime >= $muteStart && $currentTime < $muteEnd) {
                continue; // Skip sending within mute window
            }
        } else {
            // Cross-midnight, e.g., 22:00-06:00
            if ($currentTime >= $muteStart || $currentTime < $muteEnd) {
                continue;
            }
        }
    }

    // Compose message
    $message = sprintf('[Reminder] %s will expire on %s. Please renew in time.', $rem['subscription_name'], $rem['remind_at']);

    switch ($rem['remind_type']) {
        case 'email':
            // Attempt to send email using PHP's mail function; fallback to console log
            $to      = $rem['email'];
            $subject = 'Subscription Reminder';
            $body    = $message;
            // Use @ to suppress errors in environments where mail() is not configured
            $mailSent = @mail($to, $subject, $body);
            if ($mailSent) {
                echo "[EMAIL] Sent to {$to}\n";
            } else {
                echo "[EMAIL] Failed to send to {$to}. Message: {$body}\n";
            }
            break;
        case 'feishu':
            $webhook = $settings['feishu_webhook'] ?? '';
            if (sendFeishu($webhook, $message)) {
                echo "[Feishu] Sent via {$webhook}\n";
            } else {
                echo "[Feishu] Failed to send via {$webhook}\n";
            }
            break;
        case 'wechat':
            $webhook = $settings['wechat_webhook'] ?? '';
            if (sendWeChat($webhook, $message)) {
                echo "[WeChat] Sent via {$webhook}\n";
            } else {
                echo "[WeChat] Failed to send via {$webhook}\n";
            }
            break;
        case 'site':
            // In a real application, store site notifications in database for user to read
            echo "[Site] Notification created for {$rem['subscription_name']}\n";
            break;
    }

    // Repeating reminder: increment remind_at and keep status = 'pending'
    // so that next cron run will pick it up again if still pending.
    $repeatIntervalDays = 1; // 每隔多少天重复提醒，可按需调整为 3 等
    $upStmt = $pdo->prepare('UPDATE reminders SET remind_at = DATE_ADD(remind_at, INTERVAL :days DAY), sent_at = NOW(), updated_at = NOW(), status = :status WHERE id = :id');
    $upStmt->execute([
        ':days'   => $repeatIntervalDays,
        ':status' => 'pending',
        ':id'     => $rem['id'],
    ]);

    $processed++;
}

echo "Reminders processed: {$processed}\n";
