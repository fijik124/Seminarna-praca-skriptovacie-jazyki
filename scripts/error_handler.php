<?php
/**
 * ERROR_HANDLER.PHP - Register global error/exception/fatal handlers.
 */

require_once __DIR__ . '/init.php';

if (!defined('ERROR_HANDLERS_REGISTERED')) {
    define('ERROR_HANDLERS_REGISTERED', true);

    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        log_to_dev_panel(
            'PHP Error: ' . $errstr,
            'error',
            'File: ' . basename($errfile) . ' | Line: ' . $errline
        );

        return false;
    });

    set_exception_handler(function ($e) {
        $details = $e->getMessage() . "\n\n" . $e->getTraceAsString();
        log_to_dev_panel('Uncaught exception: ' . $e->getMessage(), 'error', $details);
        render_server_error_page('Application crashed with an unhandled exception.', $details);
    });

    register_shutdown_function(function () {
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
