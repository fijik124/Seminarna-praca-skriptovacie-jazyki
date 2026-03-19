<?php
/**
 * INIT.PHP - Global Configuration
 */

define('DEV_MODE', true); // Toggle this for production
$debug_logs = [];

/**
 * Global Log Function
 */
function log_to_dev_panel($message, $type = 'info', $details = '') {
    global $debug_logs;

    $templates = [
        'error'   => ['class' => 'text-bg-danger', 'icon' => '×'],
        'warning' => ['class' => 'text-bg-warning', 'icon' => '!'],
        'info'    => ['class' => 'text-bg-info', 'icon' => 'i'],
        'success' => ['class' => 'text-bg-success', 'icon' => '✓']
    ];

    $style = $templates[$type] ?? $templates['info'];

    $debug_logs[] = [
        'id'        => uniqid('log_'),
        'timestamp' => date('H:i:s'),
        'message'   => $message,
        'details'   => $details ?: 'No additional info.',
        'class'     => $style['class'],
        'icon'      => $style['icon']
    ];
}

/**
 * Render a friendly 500 page and terminate execution.
 */
function render_server_error_page($message = 'Unexpected server error.', $details = '') {
    http_response_code(500);

    // Prevent broken partial HTML from being displayed together with the error page.
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    $errorMessage = $message;
    $errorDetails = DEV_MODE ? $details : '';
    $errorPage = dirname(__DIR__) . '/pages/500.php';

    if (file_exists($errorPage)) {
        require $errorPage;
    } else {
        echo '<h1>500 - Server Error</h1><p>Something went wrong.</p>';
    }

    exit;
}

if (DEV_MODE) {
    // 1. Catch Warnings/Notices
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) return false;
        log_to_dev_panel("PHP Error: $errstr", "error", "File: " . basename($errfile) . " | Line: $errline");
        return false;
    });

    // 2. Catch uncaught exceptions.
    set_exception_handler(function ($e) {
        $details = $e->getMessage() . "\n\n" . $e->getTraceAsString();
        log_to_dev_panel("Uncaught exception: " . $e->getMessage(), "error", $details);
        render_server_error_page('Application crashed with an unhandled exception.', $details);
    });

    // 3. Catch fatal runtime errors.
    register_shutdown_function(function() {
        $error = error_get_last();
        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];

        if ($error && in_array($error['type'], $fatalTypes, true)) {
            $details = sprintf(
                'File: %s | Line: %d | Message: %s',
                $error['file'],
                $error['line'],
                $error['message']
            );

            log_to_dev_panel('Fatal shutdown error occurred.', 'error', $details);
            render_server_error_page('Server encountered a fatal error.', $details);
        }
    });
}