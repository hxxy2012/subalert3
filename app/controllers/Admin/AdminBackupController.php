<?php
namespace App\Controllers\Admin;

use App\Models\DB;

/**
 * Admin controller for managing database backups.
 * Provides listing of existing backup files and creation of new backups.
 */
class AdminBackupController
{
    /**
     * Display a list of existing backup files.
     */
    public function index(): void
    {
        // Directory where backup files are stored (under public for easy download)
        $backupDir = __DIR__ . '/../../../public/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }
        $files = [];
        // Scan directory for .sql files
        foreach (scandir($backupDir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            if (preg_match('/\.sql$/i', $file)) {
                $files[] = $file;
            }
        }
        // Sort by file modification time descending
        usort($files, function ($a, $b) use ($backupDir) {
            return filemtime($backupDir . '/' . $b) <=> filemtime($backupDir . '/' . $a);
        });
        view('admin/backups', ['files' => $files]);
    }

    /**
     * Create a new backup of the database.
     * Generates a SQL file containing schema and data for all tables.
     */
    public function create(): void
    {
        // Determine backup directory under public/backups
        $backupDir = __DIR__ . '/../../../public/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }
        $fileName = 'backup_' . date('Ymd_His') . '.sql';
        $filePath = $backupDir . '/' . $fileName;

        $pdo = DB::getConnection();
        // Tables to back up
        $tables = [
            'users',
            'subscriptions',
            'reminders',
            'settings',
            'admin_users',
            'user_settings',
            'password_resets',
            'templates',
            'admin_logs'
        ];
        $sqlContent = '';
        $sqlContent .= '-- Backup created at ' . date('Y-m-d H:i:s') . "\n\n";
        foreach ($tables as $table) {
            // Capture CREATE TABLE statement
            try {
                $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
                $row = $stmt->fetch();
                if ($row && isset($row['Create Table'])) {
                    $sqlContent .= $row['Create Table'] . ";\n\n";
                }
            } catch (\Throwable $e) {
                // ignore if command not supported (e.g., SQLite)
            }
            // Insert data
            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $data) {
                $columns = array_keys($data);
                $values = [];
                foreach ($data as $value) {
                    if ($value === null) {
                        $values[] = 'NULL';
                    } else {
                        // quote values using PDO's quote to prevent injection
                        $values[] = $pdo->quote($value);
                    }
                }
                $sqlContent .= 'INSERT INTO `' . $table . '` (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', $values) . ");\n";
            }
            $sqlContent .= "\n";
        }
        // Write to file
        file_put_contents($filePath, $sqlContent);

        // Log action and notify admin
        log_admin_action('create_backup', '创建备份文件: ' . $fileName);
        flash('success', '备份创建成功：' . $fileName);
        redirect('/admin.php?r=backups');
    }
}