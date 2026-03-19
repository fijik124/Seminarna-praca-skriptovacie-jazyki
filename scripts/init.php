<?php

// 3. Database Connection Logic
$db_host = '127.0.0.1';
$db_name = 'skola';
$db_user = 'server';
$db_pass = 'server124Pas';

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE
    ]);

    // Log success to the panel
    log_to_dev_panel("Database '$db_name' connected.", "success");
    $db_connected = true;
    // Inside your init.php, after $pdo = new PDO(...)
    if ($db_connected) {
        $db_info = [
            'status' => 'Connected',
            'host' => $db_host,
            'name' => $db_name,
            'user' => $db_user,
            'driver' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'server' => $pdo->getAttribute(PDO::ATTR_SERVER_INFO),
            'version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
            'protocol' => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
        ];
    } else {
        $db_info = ['status' => 'Disconnected'];
    }

} catch (PDOException $e) {
    log_to_dev_panel("DB Connection Failed: " . $e->getMessage(), "error");
    $db_connected = false;
}