<?php
require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/db.php';


$errors = [];

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../home');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_SESSION['bad_actions'])) {
        $errors['bad_actions'] = "You have performed suspicious actions.";
    } else {
    if (empty($_SESSION['user'])) {
        header('Location: ../login');
        exit;
    } else {
        // Clear session data
        $_SESSION = [];
        session_destroy();

        log_to_dev_panel("User Logged Out", "success", "User logged out successfully.");

        // Redirect to login page after logout
        header('Location: ../login');
        exit;
    }
    }

}

// Optional: if you still want to see errors printed at the top
if (!empty($errors)) {
    log_to_dev_panel("Form contains " . count($errors) . " errors.", "error", implode(", ", $errors));
    $_SESSION['form_errors'] = $errors;
    $_SESSION['old_input'] = [
        'first_name' => $first_name ?? '',
        'last_name' => $last_name ?? '',
        'email' => $email ?? '',
    ];

    header('Location: ../signup');
    exit;
}

?>