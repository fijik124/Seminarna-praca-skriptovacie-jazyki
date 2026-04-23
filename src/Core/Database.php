<?php

namespace Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            global $pdo, $db_error, $db_connected;

            $dbConfigPath = __DIR__ . '/../../config/db.php';
            if (file_exists($dbConfigPath)) {
                require_once $dbConfigPath;
                if (isset($pdo) && $pdo instanceof PDO) {
                    self::$instance = $pdo;
                } elseif (isset($db_error) && !empty($db_error)) {
                    if (function_exists('app_log')) {
                        app_log('error', 'Database connection error while creating PDO instance', [
                            'db_error' => $db_error,
                            'db_connected' => $db_connected ?? false,
                        ]);
                    }

                    throw new \RuntimeException("Database Connection Error: {$db_error}");
                }
            }

            if (self::$instance === null) {
                if (function_exists('app_log')) {
                    app_log('error', 'DB Connection Failed: PDO instance not initialized after requiring db.php', [
                        'db_config_path' => $dbConfigPath,
                    ]);
                }

                throw new \RuntimeException('Database connection could not be established. Check logs/app.log for details.');
            }
        }
        return self::$instance;
    }
}
