<section class="py-5 mt-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="badge text-bg-primary mb-3">About RevTrack</span>
                <h1 class="display-4 fw-bold text-white mb-3">
                    Motorsport event management made simple.
                </h1>
                <p class="lead text-secondary mb-4">
                    RevTrack helps organizers manage events, communicate with participants,
                    and coordinate track marshal registrations from one modern platform.
                </p>
                <a href="<?= htmlspecialchars(url('events'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-lg">
                    Browse Events
                </a>
            </div>

            <div class="col-lg-5">
                <div class="about-card p-4 rounded-4 border border-secondary border-opacity-25">
                    <h2 class="h5 fw-bold mb-3">What RevTrack does</h2>
                    <ul class="text-secondary mb-0">
                        <li>Event creation and administration</li>
                        <li>Track marshal registration handling</li>
                        <li>Organizer-participant messaging</li>
                        <li>Role-based dashboard access</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card h-100 p-4 rounded-4">
                    <h2 class="h5 fw-bold">Event Management</h2>
                    <p class="text-secondary mb-0">
                        Create, organize, and manage motorsport events with clear details,
                        dates, locations, statuses, and organizer information.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card h-100 p-4 rounded-4">
                    <h2 class="h5 fw-bold">Marshal Coordination</h2>
                    <p class="text-secondary mb-0">
                        Participants can request marshal registration while organizers review,
                        approve, reject, and provide notes for every request.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card h-100 p-4 rounded-4">
                    <h2 class="h5 fw-bold">Communication</h2>
                    <p class="text-secondary mb-0">
                        Built-in messaging keeps conversations between organizers and users
                        connected directly to the selected event.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 border-top border-secondary border-opacity-25">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h2 class="fw-bold mb-3">Built for clarity and control</h2>
                <p class="text-secondary">
                    RevTrack uses role-based access control so administrators, organizers,
                    and participants only see the tools relevant to their responsibilities.
                </p>
                <p class="text-secondary">
                    The system supports event workflows such as assignments, registration
                    reviews, message replies, and public event browsing.
                </p>
            </div>

            <div class="col-lg-6">
                <div class="stats-grid">
                    <div class="stat-box">
                        <strong>01</strong>
                        <span>Events</span>
                    </div>
                    <div class="stat-box">
                        <strong>02</strong>
                        <span>Registrations</span>
                    </div>
                    <div class="stat-box">
                        <strong>03</strong>
                        <span>Messages</span>
                    </div>
                    <div class="stat-box">
                        <strong>04</strong>
                        <span>Dashboard</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>

    .about-card,
    .feature-card {
        background: rgba(33, 37, 41, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
    }

    .feature-card {
        transition: transform 0.2s ease, border-color 0.2s ease;
    }

    .feature-card:hover {
        transform: translateY(-4px);
        border-color: rgba(13, 110, 253, 0.6);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .stat-box {
        padding: 1.5rem;
        border-radius: 1rem;
        background: rgba(33, 37, 41, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .stat-box strong {
        display: block;
        font-size: 2rem;
        color: #0d6efd;
    }

    .stat-box span {
        color: #adb5bd;
    }
</style>