<?php
require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../scripts/common_functions.php';


$errors = [];

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Capture and Sanitize
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';

    log_to_dev_panel("Post request received", "info", "User: $email");

    // 2. Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email_validity'] = "Email is not valid!";
        log_to_dev_panel("Validation Failed", "warning", "Invalid email format: $email");
        create_session_atempts("login");
        header('Location: /login');
        exit;
    }

    if (empty($password) || strlen($password) < 6) {
        $errors['password_len'] = "Password must be at least 6 characters.";
        log_to_dev_panel("Validation Failed", "warning", "Password too short.");
        create_session_atempts("login");
        header('Location: /login');
        exit;
    }

    if (check_session_attempts("login") > 5) {
        $errors['too_many_attempts'] = "Too many failed login attempts. Please try again later.";
        log_to_dev_panel("Brute Force Detected", "error", "User $email has exceeded login attempts.");
        header('Location: /login');
        exit;
    }
    // 3. Database Operation
    if (empty($errors)) {
        try {
            $sql = "SELECT first_name, last_name, email, password FROM users WHERE email = :email";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                'email' => $email
            ]);

            log_to_dev_panel("User Found", "success", "User $email found in revtrack database.");

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user['password'])) {
                $errors['credentials'] = "Invalid email or password.";
                log_to_dev_panel("Authentication Failed", "warning", "Invalid credentials for email: $email");
            } else {
                // Set session variables
                $_SESSION['user'] = [
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email']
                ];

                log_to_dev_panel("User Logged In", "success", "User $email logged in successfully.");
            }
            
            // Redirect after success
            header('Location: /dashboard/');
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { 
                $errors['email_error'] = "This email already exists!";
                log_to_dev_panel("Database Conflict", "warning", "Duplicate email entry attempted: $email");
            } else {
                log_to_dev_panel("SQL Error", "error", $e->getMessage());
                render_server_error_page('Database error during registration.', $e->getMessage());
            }    
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