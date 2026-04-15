<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card border-0 shadow-lg p-4" style="max-width: 400px; width: 100%; border-radius: 1rem;">
        
        <div class="card-body">
            <?php
            $oldInput = [];
            if (isset($_GET['old'])) {
                $decodedOld = base64_decode($_GET['old']);
                if ($decodedOld) {
                    $oldInput = json_decode($decodedOld, true) ?: [];
                }
            }

            if (isset($_GET['error']) && $_GET['error'] == '1') {
                echo '<div class="alert alert-danger">Nesprávny email alebo heslo.</div>';
            }
            ?>
            <div class="text-center mb-4">
                <h2 class="fw-bold text-light">Vitajte späť</h2>
                <p class="text-muted small">Znovu sa vidíme.</p>
            </div>

            <form action="<?= url('login-process') ?>" method="POST">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="login_email" name="email" placeholder="Email" value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>">
                    <label for="login_email">Email</label>
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="login_password" name="password" placeholder="Password">
                    <label for="login_password">Password</label>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm" style="border-radius: 0.5rem;">
                        Login
                    </button>
                </div>

                <div class="text-center">
                    <p class="small text-muted mb-0">Don't have an account? 
                        <a href="<?= url('signup') ?>" class="text-decoration-none fw-semibold">Sign up</a>
                    </p>
                </div>
            </form>
        </div>
        
    </div>
</div>