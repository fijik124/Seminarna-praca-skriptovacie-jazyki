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
                    if (function_exists('log_to_file')) {
                        log_to_file("DB Connection Error: {$db_error}", 'ERROR');
                    }
                    if (function_exists('render_server_error_page')) {
                        render_server_error_page("Database Connection Error: {$db_error}");
                    } else {
                        die("Database Connection Error: {$db_error}");
                    }
                }
            }

            if (self::$instance === null) {
                if (function_exists('log_to_file')) {
                    log_to_file('DB Connection Failed: PDO instance not initialized after requiring db.php', 'ERROR');
                }
                if (function_exists('render_server_error_page')) {
                    render_server_error_page('Database connection could not be established. Check logs/app.log for details.');
                } else {
                    die('DB Connection Failed');
                }
            }
        }
        return self::$instance;
    }
}
