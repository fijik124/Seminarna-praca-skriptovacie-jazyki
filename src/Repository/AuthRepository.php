<?php

namespace Repository;

use Entity\User;
use Repository\UserRepository;
use Repository\GroupRepository;

class AuthRepository {
    private UserRepository $userRepo;
    private GroupRepository $groupRepo;

    public function __construct() {
        $this->userRepo = new UserRepository();
        $this->groupRepo = new GroupRepository();
    }

    /**
     * Start session if not already started.
     */
    public function ensureSession(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Authenticate user and store in session.
     */
    public function login(string $email, string $password): bool {
        $user = $this->userRepo->authenticate($email, $password);
        if ($user) {
            $this->ensureSession();
            
            $permissions = [];
            if ($user->groupId) {
                $permissions = $this->groupRepo->getGroupPermissions($user->groupId);
            }

            $_SESSION['user'] = [
                'id' => $user->id,
                'first_name' => $user->firstName,
                'last_name' => $user->lastName,
                'email' => $user->email,
                'group_id' => $user->groupId,
                'permissions' => $permissions
            ];
            return true;
        }
        return false;
    }

    /**
     * Handle login process from request.
     */
    public function handleLogin(): void {
        $this->ensureSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (function_exists('log_to_file')) {
                log_to_file("Neoprávnený prístup k login: " . $_SERVER['REQUEST_METHOD'], 'WARNING');
            }
            header('Location: ' . url('login'));
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($this->login($email, $password)) {
            // Determine redirect target based on permission
            if ($this->hasPermission('dashboard_view')) {
                header('Location: ' . url('dashboard'));
            } else {
                header('Location: ' . url('user'));
            }
            exit;
        } else {
            header('Location: ' . url('login?error=1'));
            exit;
        }
    }

    /**
     * Handle registration process from request.
     */
    public function handleRegister(): void {
        $this->ensureSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (function_exists('log_to_file')) {
                log_to_file("Neoprávnený prístup k register: " . $_SERVER['REQUEST_METHOD'], 'WARNING');
            }
            header('Location: ' . url('signup'));
            exit;
        }

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = [];

        if (empty($firstName)) {
            $errors['first_name'] = "Meno je povinné.";
        }

        if (empty($lastName)) {
            $errors['last_name'] = "Priezvisko je povinné.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Email nie je platný.";
        }

        if (empty($password) || strlen($password) < 6) {
            $errors['password'] = "Heslo musí mať aspoň 6 znakov.";
        }

        if (empty($errors)) {
            try {
                if ($this->userRepo->getByEmail($email)) {
                    $errors['email_exists'] = "Tento email už existuje.";
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Assign default 'User' group
                    $userGroup = $this->groupRepo->findByName('User');
                    $groupId = $userGroup ? $userGroup->id : null;

                    $user = new \Entity\User([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'password' => $hashedPassword,
                        'group_id' => $groupId
                    ]);
                    $this->userRepo->create($user);
                    header('Location: ' . url('login'));
                    exit;
                }
            } catch (\Throwable $e) {
                if (function_exists('log_to_file')) {
                    log_to_file("Registration error: " . $e->getMessage(), 'ERROR');
                }
                if (function_exists('render_server_error_page')) {
                    render_server_error_page('Chyba pri registrácii.');
                } else {
                    throw $e;
                }
            }
        }

        if (!empty($errors)) {
            $encodedErrors = base64_encode(json_encode($errors));
            $encodedOldInput = base64_encode(json_encode([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
            ]));
            header('Location: ' . url('signup?errors=' . urlencode($encodedErrors) . '&old=' . urlencode($encodedOldInput)));
            exit;
        }
    }

    /**
     * Log out current user and clear session.
     */
    public function logout(): void {
        $this->ensureSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Handle logout process from request.
     */
    public function handleLogout(): void {
        $this->logout();
        header('Location: ' . url('login'));
        exit;
    }

    /**
     * Reset session (dev tool).
     */
    public function handleResetSession(): void {
        $this->ensureSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/'));
            exit;
        }

        // Clear all session data and regenerate id to prevent fixation.
        $_SESSION = [];
        session_regenerate_id(true);

        if (function_exists('log_to_dev_panel')) {
            log_to_dev_panel('Session reset from dev panel.', 'warning', 'Session was cleared manually.');
        }

        $refererPath = parse_url($_SERVER['HTTP_REFERER'] ?? '/', PHP_URL_PATH) ?: '/';
        $refererQuery = parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_QUERY);
        $redirectTo = $refererPath . ($refererQuery ? '?' . $refererQuery : '');

        header('Location: ' . $redirectTo);
        exit;
    }

    /**
     * Check if user is logged in.
     */
    public function isLoggedIn(): bool {
        $this->ensureSession();
        return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
    }

    /**
     * Get current logged in user as an array (or entity).
     */
    public function getCurrentUser(): ?array {
        $this->ensureSession();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Redirect if not logged in.
     */
    public function requireLogin(): void {
        if (!$this->isLoggedIn()) {
            header('Location: ' . url('login'));
            exit;
        }
    }

    /**
     * Check if current user has a specific permission.
     */
    public function hasPermission(string $slug): bool {
        $user = $this->getCurrentUser();
        if (!$user || !isset($user['permissions'])) {
            return false;
        }
        return in_array($slug, $user['permissions'], true);
    }

    /**
     * Redirect if user doesn't have permission.
     */
    public function requirePermission(string $slug): void {
        $this->requireLogin();
        if (!$this->hasPermission($slug)) {
            if (function_exists('log_to_file')) {
                $user = $this->getCurrentUser();
                log_to_file("Neoprávnený prístup k akcii '$slug' používateľom " . ($user['email'] ?? 'unknown'), 'WARNING');
            }
            // For now just redirect to dashboard or show 403 (if we had one)
            header('Location: ' . url('dashboard?error=noperm'));
            exit;
        }
    }

    /**
     * Handle profile update process from request.
     */
    public function handleProfileUpdate(): void {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('user/profile'));
            exit;
        }

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        $errors = [];
        if (empty($firstName)) $errors['first_name'] = "Meno je povinné.";
        if (empty($lastName)) $errors['last_name'] = "Priezvisko je povinné.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Email nie je platný.";

        if (empty($errors)) {
            $currentUserId = $_SESSION['user']['id'];
            $existingUser = $this->userRepo->getByEmail($email);
            
            try {
                if ($existingUser && $existingUser->id !== $currentUserId) {
                    $errors['email_exists'] = "Tento email už používa iný používateľ.";
                } else {
                    $user = new \Entity\User([
                        'id' => $currentUserId,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'group_id' => $_SESSION['user']['group_id'],
                        'password' => '' // Password not updated here
                    ]);

                    if ($this->userRepo->update($user)) {
                        // Update session
                        $_SESSION['user']['first_name'] = $firstName;
                        $_SESSION['user']['last_name'] = $lastName;
                        $_SESSION['user']['email'] = $email;
                        
                        header('Location: ' . url('user/profile?msg=' . urlencode(base64_encode(json_encode(['type' => 'success', 'text' => 'Profil bol úspešne aktualizovaný.'])))));
                        exit;
                    } else {
                        $errors['general'] = "Chyba pri aktualizácii profilu.";
                    }
                }
            } catch (\Throwable $e) {
                if (function_exists('log_to_file')) {
                    log_to_file("Profile update error: " . $e->getMessage(), 'ERROR');
                }
                $errors['general'] = "Interná chyba pri aktualizácii profilu.";
            }
        }

        if (!empty($errors)) {
            $encodedErrors = base64_encode(json_encode($errors));
            header('Location: ' . url('user/profile?errors=' . urlencode($encodedErrors)));
            exit;
        }
    }
}
