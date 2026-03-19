<?php 
require './init.php';          // Error handler and debug array setup
require './error_handler.php';            // The PDO $pdo connection

$errors = [];

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Capture and Sanitize
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';

    log_to_dev_panel("Post request received", "info", "User: $email");

    // 2. Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email_validity'] = "Email is not valid!";
        log_to_dev_panel("Validation Failed", "warning", "Invalid email format: $email");
    }

    if (empty($password) || strlen($password) < 6) {
        $errors['password_len'] = "Password must be at least 6 characters.";
        log_to_dev_panel("Validation Failed", "warning", "Password too short.");
    }

    // 3. Database Operation
    if (empty($errors)) {
        try {
            // Hash password safely
            $hashedPasswd = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (first_name, last_name, email, password) 
                    VALUES (:fname, :lname, :email, :pass)";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                'fname' => $first_name,
                'lname' => $last_name,
                'email' => $email,
                'pass'  => $hashedPasswd
            ]);

            log_to_dev_panel("User Registered", "success", "User $email added to revtrack database.");
            
            // Redirect after success
            header('Location: index.php');
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { 
                $errors['email_error'] = "This email already exists!";
                log_to_dev_panel("Database Conflict", "warning", "Duplicate email entry attempted: $email");
            } else {
                // This will also be caught by your global exception handler
                $errors['other_error'] = "A database error occurred.";
                log_to_dev_panel("SQL Error", "error", $e->getMessage());
            }    
        }
    }
}

// Optional: if you still want to see errors printed at the top
if(!empty($errors)) {
    log_to_dev_panel("Form contains " . count($errors) . " errors.", "error", implode(", ", $errors));
}
?>
 <?php if ($devMode) {
      require __DIR__ . '/components/devpanel.php';
    };?>