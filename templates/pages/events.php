<?php

use Repository\EventRepository;

$events = [];
$eventsLoadError = '';

try {
    $events = (new EventRepository())->getAllUpcoming();
} catch (Throwable $e) {
    $eventsLoadError = 'Unable to load events right now. Please try again later.';
    if (function_exists('app_log')) {
        app_log('error', 'Events page load failed', [
            'exception_class' => get_class($e),
            'exception' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
}
?>
<main data-bs-theme="dark" class="bg-dark text-light min-vh-100">

    <section class="py-5">
        <div class="container">
            <p class="text-uppercase small text-secondary mb-2">RevTrack Events</p>
            <h1 class="display-5 fw-bold mb-3">Upcoming motocross events and race organization</h1>
            <p class="lead text-secondary mb-4">
                This page helps riders and organizers see what events are planned,
                where they happen, and what kind of participation is expected.
            </p>
            <div class="d-flex flex-wrap gap-3">
                <a href="<?= url('signup') ?>" class="btn btn-primary btn-lg px-4">Join as Participant</a>
                <a href="<?= url('contact') ?>" class="btn btn-outline-light btn-lg px-4">Contact Organizer</a>
            </div>
        </div>
    </section>

    <section class="py-5 bg-body-tertiary border-top border-bottom border-secondary border-opacity-25">
        <div class="container">
            <?php if ($eventsLoadError !== ''): ?>
                <div class="alert alert-warning" role="alert"><?= htmlspecialchars($eventsLoadError, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <div class="row g-4">
                <?php if (!$events): ?>
                    <div class="col-12">
                        <div class="alert alert-info mb-0">No events are available yet.</div>
                    </div>
                <?php endif; ?>
                <?php foreach ($events as $event): ?>
                    <div class="col-md-4">
                        <a href="<?= htmlspecialchars(url('event?event=' . urlencode($event->slug)), ENT_QUOTES, 'UTF-8') ?>" class="event-card-trigger card h-100 border-0 bg-dark-subtle text-start w-100 text-decoration-none">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <span class="badge <?= htmlspecialchars($event->typeClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($event->type, ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="small text-info">Open Details</span>
                                </div>
                                <h3 class="h5 fw-bold mb-2 text-light"><?= htmlspecialchars($event->title, ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="text-secondary mb-3"><?= htmlspecialchars($event->description, ENT_QUOTES, 'UTF-8') ?></p>
                                <ul class="list-unstyled small text-secondary mb-0">
                                    <li><strong class="text-light">Date:</strong> <?= htmlspecialchars($event->date, ENT_QUOTES, 'UTF-8') ?></li>
                                    <li><strong class="text-light">Location:</strong> <?= htmlspecialchars($event->location, ENT_QUOTES, 'UTF-8') ?></li>
                                    <li><strong class="text-light">Status:</strong> <?= htmlspecialchars($event->status, ENT_QUOTES, 'UTF-8') ?></li>
                                </ul>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

</main>


<style>
.event-card-trigger {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.event-card-trigger:hover,
.event-card-trigger:focus-visible {
    transform: translateY(-4px);
    box-shadow: 0 0.8rem 1.8rem rgba(0, 0, 0, 0.35);
    outline: 1px solid rgba(255, 255, 255, 0.25);
}
</style>

