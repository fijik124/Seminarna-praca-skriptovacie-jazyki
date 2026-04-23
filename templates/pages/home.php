<main data-bs-theme="dark" class="bg-dark text-light min-vh-100">

    <section class="py-5">
        <div class="container">
            <p class="text-uppercase small text-secondary mb-2">RevTrack Platform</p>
            <h1 class="display-4 fw-bold mb-3">Motocross track and race management in one place.</h1>
            <p class="lead text-secondary mb-4">
                This page is for riders, organizers, and admins who need a clear overview of tracks, race events,
                and user activity. Use RevTrack to browse tracks, manage registrations, and coordinate race operations.
            </p>
            <div class="d-flex flex-wrap gap-3">
                <a href="<?= url('tracks') ?>" class="btn btn-primary btn-lg px-4">Browse Tracks</a>
                <a href="<?= url('signup') ?>" class="btn btn-outline-light btn-lg px-4">Create Account</a>
                <a href="<?= url('login') ?>" class="btn btn-outline-info btn-lg px-4">Admin / Organizer Login</a>
            </div>
        </div>
    </section>

    <section class="py-5 bg-body-tertiary border-top border-bottom border-secondary border-opacity-25">
        <div class="container">
            <div class="row text-center g-4">
                <div class="col-6 col-md-3">
                    <small class="text-uppercase text-secondary fw-bold">Public Track List</small>
                    <div class="h4 fw-bold text-primary mt-1">Discover open tracks</div>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-uppercase text-secondary fw-bold">Race Coordination</small>
                    <div class="h4 fw-bold text-primary mt-1">Plan events faster</div>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-uppercase text-secondary fw-bold">Commissioners</small>
                    <div class="h4 fw-bold text-primary mt-1">Attendance overview</div>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-uppercase text-secondary fw-bold">User Management</small>
                    <div class="h4 fw-bold text-primary mt-1">Admin and organizer roles</div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold">What this page is for</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm bg-body-tertiary">
                        <div class="card-body text-center p-4">
                            <div class="mb-3 text-warning">
                                <i class="fas fa-map-location-dot fa-3x"></i>
                            </div>
                            <h4 class="card-title fw-bold">For Riders</h4>
                            <p class="card-text text-secondary">Check available tracks, see details, and quickly decide where to ride.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm bg-body-tertiary">
                        <div class="card-body text-center p-4">
                            <div class="mb-3 text-info">
                                <i class="fas fa-flag-checkered fa-3x"></i>
                            </div>
                            <h4 class="card-title fw-bold">For Organizers</h4>
                            <p class="card-text text-secondary">Create and manage race events, assign staff, and keep event data organized.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm bg-body-tertiary">
                        <div class="card-body text-center p-4">
                            <div class="mb-3 text-success">
                                <i class="fas fa-users-gear fa-3x"></i>
                            </div>
                            <h4 class="card-title fw-bold">For Admins</h4>
                            <p class="card-text text-secondary">Control permissions, maintain track records, and monitor platform activity.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 mb-5">
        <div class="container">
            <div class="p-5 text-center bg-primary bg-gradient rounded-4 shadow">
                <h3 class="display-6 fw-bold text-white">Start using RevTrack today</h3>
                <p class="lead text-white opacity-75">Explore tracks as a visitor, or sign in to manage races and operations.</p>
                <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
                    <a href="<?= url('tracks') ?>" class="btn btn-light btn-lg px-4 fw-bold">View Tracks</a>
                    <a href="<?= url('signup') ?>" class="btn btn-outline-light btn-lg px-4">Create Account</a>
                    <a href="<?= url('contact') ?>" class="btn btn-outline-light btn-lg px-4">Contact Us</a>
                </div>
            </div>
        </div>
    </section>

</main>