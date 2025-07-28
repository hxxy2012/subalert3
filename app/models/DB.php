<?php
namespace App\Models;

/**
 * DB class provides a simple singleton PDO connection.
 * It reads configuration from app/config.php and sets
 * appropriate PDO attributes. All database queries
 * throughout the application should use this class.
 */
class DB
{
    /**
     * @var \PDO|null
     */
    private static $pdo = null;

    /**
     * Get PDO instance.
     *
     * @return \PDO
     */
    public static function getConnection(): \PDO
    {
        if (self::$pdo === null) {
            $config = require __DIR__ . '/../config.php';
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',
                $config['db_host'],
                $config['db_name'],
                $config['db_charset']
            );
            try {
                self::$pdo = new \PDO($dsn, $config['db_user'], $config['db_password'], [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (\PDOException $e) {
                // Handle connection error gracefully
                die('Database connection failed: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}