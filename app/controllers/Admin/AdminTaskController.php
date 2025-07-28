<?php
namespace App\Controllers\Admin;

use App\Models\DB;

class AdminTaskController
{
    /**
     * List scheduled tasks and their last run times.
     */
    public function index(): void
    {
        $pdo = DB::getConnection();
        // Get last run times from settings
        $stmt = $pdo->prepare('SELECT `key`, `value` FROM settings WHERE `key` IN ("task_send_reminders_last_run")');
        $stmt->execute();
        $results = $stmt->fetchAll();
        $times = [];
        foreach ($results as $r) {
            $times[$r['key']] = $r['value'];
        }
        $tasks = [
            'send_reminders' => [
                'name' => '发送提醒任务',
                'description' => '扫描提醒表并发送通知',
                'last_run' => $times['task_send_reminders_last_run'] ?? '从未运行',
            ],
        ];
        view('admin/tasks', ['tasks' => $tasks]);
    }

    /**
     * Run a specific task.
     */
    public function run(): void
    {
        $task = $_GET['task'] ?? '';
        $pdo = DB::getConnection();
        if ($task === 'send_reminders') {
            // Run the CLI script (simulation)
            $output = shell_exec('php ' . escapeshellarg(__DIR__ . '/../../cron/send_reminders.php'));
            // Update last run time
            $this->saveSetting($pdo, 'task_send_reminders_last_run', date('Y-m-d H:i:s'));
            // Log admin action
            log_admin_action('run_task', '手动执行 send_reminders 任务');
            flash('success', '提醒任务已执行。输出: ' . nl2br(htmlspecialchars($output)));
        } else {
            flash('error', '未知任务');
        }
        redirect('/admin.php?r=tasks');
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