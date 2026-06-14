<?php
declare(strict_types=1);

/**
 * ERROR_HANDLER.PHP - Log absolutely every error, warning, notice, and exception to a log file.
 */

require_once __DIR__ . '/../config/init.php';

if (!defined('ERROR_HANDLERS_REGISTERED')) {
    define('ERROR_HANDLERS_REGISTERED', true);

    // Keep display on or off depending on your preference, but force logging active
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
    error_reporting(E_ALL); // Log absolutely everything (Notices, Warnings, Deprecations, Errors)

    if (!function_exists('force_log_to_file')) {
        /**
         * Low-level file writer that forces an immediate OS disk flush.
         */
        function force_log_to_file(string $type, string $message, string $file, int $line, string $trace = ''): void {
            $logFile = __DIR__ . '/../logs/app.log';

            // Automatically create the logs directory if it doesn't exist
            if (!file_exists(dirname($logFile))) {
                @mkdir(dirname($logFile), 0755, true);
            }

            // Build a highly detailed, clean log block
            $logEntry = sprintf(
                "[%s] [%s]\nMESSAGE: %s\nFILE: %s (Line: %d)\n",
                date('Y-m-d H:i:s'),
                strtoupper($type),
                $message,
                $file,
                $line
            );

            if (!empty($trace)) {
                $logEntry .= "STACK TRACE:\n" . $trace . "\n";
            }

            $logEntry .= "-----------------------------------------------------------------------\n";

            // Open file handle, write, push directly to disk storage, and close
            $fp = @fopen($logFile, 'a');
            if ($fp) {
                @fwrite($fp, $logEntry);
                @fflush($fp);
                @fclose($fp);
            }

            // Also mirror it to the main server/FPM error log just in case
            @error_log(sprintf('[%s] %s in %s on line %d', $type, $message, $file, $line));
        }
    }

    // 1. GLOBAL ERROR HANDLER (Captures Warnings, Notices, Deprecations, etc.)
    set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
        if (!(error_reporting() & $errno)) {
            return false; // Skip if error is suppressed with an @ symbol
        }

        $type = match ($errno) {
            E_ERROR             => 'Fatal Error',
            E_WARNING           => 'Warning',
            E_PARSE             => 'Parse Error',
            E_NOTICE            => 'Notice',
            E_CORE_ERROR        => 'Core Error',
            E_CORE_WARNING      => 'Core Warning',
            E_COMPILE_ERROR     => 'Compile Error',
            E_COMPILE_WARNING   => 'Compile Warning',
            E_USER_ERROR        => 'User Error',
            E_USER_WARNING      => 'User Warning',
            E_USER_NOTICE       => 'User Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED        => 'Deprecated',
            E_USER_DEPRECATED   => 'User Deprecated',
            2048                => 'Strict Notice',
            default             => 'Unknown Error',
        };

        force_log_to_file("PHP $type", $errstr, $errfile, $errline);

        // Return false so PHP can still display the warning on screen normally
        return false;
    });

    // 2. GLOBAL EXCEPTION HANDLER (Captures all uncaught class Throwables)
    set_exception_handler(function (Throwable $e): void {
        force_log_to_file(
            "Uncaught Exception (" . $e::class . ")",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        // Re-throw or output so the engine still knows the application stopped
        echo "<br><b>Fatal error:</b> Uncaught Exception: " . htmlspecialchars($e->getMessage()) . " in <b>" . htmlspecialchars($e->getFile()) . "</b> on line <b>" . $e->getLine() . "</b><br>";
        exit;
    });

    // 3. SHUTDOWN FUNCTION (Catches compile errors and unrecoverable syntax crashes)
    register_shutdown_function(function (): void {
        $error = error_get_last();
        if ($error !== null) {
            $isFatal = match ($error['type']) {
                E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR => true,
                default => false,
            };

            if ($isFatal) {
                force_log_to_file(
                    "Fatal Crash Shutdown",
                    $error['message'],
                    $error['file'],
                    $error['line']
                );
            }
        }
    });
}