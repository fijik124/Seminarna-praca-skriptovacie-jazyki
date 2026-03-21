<?php
require_once __DIR__ . '/error_handler.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

// Clear all session data and regenerate id to prevent fixation.
$_SESSION = [];
session_regenerate_id(true);

log_to_dev_panel('Session reset from dev panel.', 'warning', 'Session was cleared manually.');

$refererPath = parse_url($_SERVER['HTTP_REFERER'] ?? '/', PHP_URL_PATH) ?: '/';
$refererQuery = parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_QUERY);
$redirectTo = $refererPath . ($refererQuery ? '?' . $refererQuery : '');

header('Location: ' . $redirectTo);
exit;
