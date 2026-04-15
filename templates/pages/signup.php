<div class="container d-flex justify-content-center align-items-center min-vh-100 py-5">
    <div class="card border-0 shadow-lg p-4" style="max-width: 500px; width: 100%; border-radius: 1.25rem;">
        
        <div class="card-body">
            <?php
            $oldInput = [];
            if (isset($_GET['old'])) {
                $decodedOld = base64_decode($_GET['old']);
                if ($decodedOld) {
                    $oldInput = json_decode($decodedOld, true) ?: [];
                }
            }

            if (isset($_GET['errors'])) {
                $decodedErrors = base64_decode($_GET['errors']);
                if ($decodedErrors) {
                    $formErrors = json_decode($decodedErrors, true) ?: [];
                    echo '<div class="alert alert-danger">';
                    foreach ($formErrors as $error) {
                        echo htmlspecialchars($error) . '<br>';
                    }
                    echo '</div>';
                }
            }
            ?>
            <div class="text-center mb-4">
                <h2 class="fw-bold text-light">Vytvoriť účet</h2>
                <p class="text-muted small">Pridajte sa k našej komunite.</p>
            </div>

            <form action="<?= url('register-process') ?>" method="POST">
                <div class="row g-2 mb-3">
                    <div class="col-md">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" value="<?= htmlspecialchars($oldInput['first_name'] ?? '') ?>">
                            <label for="first_name">Meno</label>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" value="<?= htmlspecialchars($oldInput['last_name'] ?? '') ?>">
                            <label for="last_name">Priezvisko</label>
                        </div>
                    </div>
                </div>

                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>">
                    <label for="email">Emailová adresa</label>
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                    <label for="password">Password</label>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm fw-bold" style="border-radius: 0.75rem;">
                        Sign Up
                    </button>
                </div>

                <div class="text-center">
                    <p class="small text-muted mb-0">Already have an account? 
                        <a href="<?= url('login') ?>" class="text-decoration-none fw-semibold">Log in</a>
                    </p>
                </div>
            </form>
        </div>

    </div>
</div>