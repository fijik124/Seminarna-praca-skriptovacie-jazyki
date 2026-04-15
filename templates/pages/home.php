<main data-bs-theme="dark" class="bg-dark text-light min-vh-100">

    <section class="py-5">
        <div class="container">
            <h1 class="display-4 fw-bold">Section</h1>
            <p class="lead text-secondary">
                A simple container to divide your page into <span class="text-primary fw-semibold">sections</span>, 
                like the one you're currently reading.
            </p>
        </div>
    </section>

    <section class="py-5 bg-body-tertiary border-top border-bottom border-secondary border-opacity-25">
        <div class="container">
            <div class="row text-center g-4">
                <div class="col-6 col-md-3">
                    <small class="text-uppercase tracking-wider text-secondary fw-bold">Database Queries</small>
                    <div class="h2 fw-bold text-primary mt-1">3,456</div>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-uppercase tracking-wider text-secondary fw-bold">Active Users</small>
                    <div class="h2 fw-bold text-primary mt-1">123</div>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-uppercase tracking-wider text-secondary fw-bold">Server Uptime</small>
                    <div class="h2 fw-bold text-primary mt-1">99.9%</div>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-uppercase tracking-wider text-secondary fw-bold">Page Load</small>
                    <div class="h2 fw-bold text-primary mt-1">0.4s</div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold">Core Features</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm bg-body-tertiary">
                        <div class="card-body text-center p-4">
                            <div class="mb-3 text-warning">
                                <i class="fas fa-bolt fa-3x"></i>
                            </div>
                            <h4 class="card-title fw-bold">Lightning Fast</h4>
                            <p class="card-text text-secondary">Optimized PHP backend for millisecond responses.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm bg-body-tertiary">
                        <div class="card-body text-center p-4">
                            <div class="mb-3 text-info">
                                <i class="fas fa-code fa-3x"></i>
                            </div>
                            <h4 class="card-title fw-bold">Clean Code</h4>
                            <p class="card-text text-secondary">Strict PSR compliance and modular architecture.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm bg-body-tertiary">
                        <div class="card-body text-center p-4">
                            <div class="mb-3 text-success">
                                <i class="fas fa-lock fa-3x"></i>
                            </div>
                            <h4 class="card-title fw-bold">Fully Secure</h4>
                            <p class="card-text text-secondary">Automatic CSRF protection and SQL injection prevention.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 mb-5">
        <div class="container">
            <div class="p-5 text-center bg-primary bg-gradient rounded-4 shadow">
                <h3 class="display-6 fw-bold text-white">Ready to start building?</h3>
                <p class="lead text-white opacity-75">Join over 1,000 developers using our framework today.</p>
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="<?= url('signup') ?>" class="btn btn-light btn-lg px-4 fw-bold">Create Account</a>
                    <a href="<?= url('about') ?>" class="btn btn-outline-light btn-lg px-4">Learn More</a>
                </div>
            </div>
        </div>
    </section>

</main>