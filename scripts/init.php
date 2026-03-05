<?php
/**
 * INIT.PHP - Global Configuration & Dev Tools
 * Include this at the very top of every page.
 */

// 1. Global storage for your Bulma dev panel
$debug_logs = [];

/**
 * Helper function to push messages to the Bulma Dev Panel
 * Usage: log_to_dev_panel("Query Executed", "success");
 */
function log_to_dev_panel($message, $type = 'info', $details = '')
{
    global $debug_logs;

    $templates = [
        'error' => ['class' => 'is-danger', 'icon' => '×'],
        'warning' => ['class' => 'is-warning', 'icon' => '!'],
        'info' => ['class' => 'is-info', 'icon' => 'i'],
        'success' => ['class' => 'is-success', 'icon' => '✓']
    ];

    $style = $templates[$type] ?? $templates['info'];

    $debug_logs[] = [
        'id' => uniqid('log_'), // Unique ID for the click event
        'timestamp' => date('H:i:s'),
        'message' => $message,
        'details' => $details ?: 'No additional debug information available.',
        'class' => $style['class'],
        'icon' => $style['icon']
    ];
}

// 2. The Global Error Catcher
// This automatically captures PHP errors and sends them to your panel
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // We ignore "Notice" level errors to keep the console clean, 
    // but you can remove the IF check to see everything.
    if (!(error_reporting() & $errno))
        return;

    $fileName = basename($errfile);
    log_to_dev_panel("[$fileName:$errline] $errstr", "error");

    // Return false to let the standard PHP error handler continue if needed
    return false;
});

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