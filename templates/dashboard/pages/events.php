<?php

use Repository\AuthRepository;
use Repository\EventRepository;

$auth = new AuthRepository();
$auth->ensureSession();

$currentUser = $auth->getCurrentUser();
$isAdmin = $auth->isAdmin();
$isOrganizer = $auth->isOrganizer();
$eventRepo = new EventRepository();
$events = $eventRepo->getAccessibleEventsForUser($currentUser['id'] ?? null, $isAdmin);

$eventStats = [];
foreach ($events as $event) {
    $messages = $eventRepo->getMessagesForEvent((int) $event->id);
    $requests = $eventRepo->getRegistrationRequestsForEvent((int) $event->id);
    $pendingRequests = array_filter($requests, static fn (array $request): bool => ($request['status'] ?? '') === 'pending');
    $eventStats[$event->id] = [
        'messages' => count($messages),
        'requests' => count($requests),
        'pending' => count($pendingRequests),
        'assignments' => count($eventRepo->getEventAssignments((int) $event->id)),
    ];
}
?>

<section class="pt-5 pb-4 mt-5">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <p class="text-secondary text-uppercase small mb-1">Dashboard Events</p>
                <h1 class="h3 fw-bold mb-0">Event Management</h1>
                <p class="text-secondary mb-0"><?= $isAdmin ? 'Admin can view and manage all events.' : 'Organizer can view assigned events and manage their inbox.' ?></p>
            </div>
            <a href="<?= url('dashboard/home') ?>" class="btn btn-outline-light">Back to Overview</a>
        </div>
    </div>
</section>

<section class="pb-5">
    <div class="container">
        <?php if (!$events): ?>
            <div class="alert alert-info">No events are assigned to your account yet.</div>
        <?php endif; ?>

        <div class="row g-4">
            <?php foreach ($events as $event): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card bg-body-tertiary border-secondary border-opacity-25 h-100">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between gap-2 mb-2">
                                <span class="badge <?= htmlspecialchars($event->typeClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($event->type, ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="badge text-bg-secondary"><?= htmlspecialchars($event->status, ENT_QUOTES, 'UTF-8') ?></span>
                            </div>

                            <h2 class="h5 fw-bold mb-2"><?= htmlspecialchars($event->title, ENT_QUOTES, 'UTF-8') ?></h2>
                            <p class="text-secondary small mb-3"><?= htmlspecialchars($event->description, ENT_QUOTES, 'UTF-8') ?></p>

                            <div class="small text-secondary mb-3">
                                <div><strong class="text-light">Date:</strong> <?= htmlspecialchars($event->date, ENT_QUOTES, 'UTF-8') ?></div>
                                <div><strong class="text-light">Location:</strong> <?= htmlspecialchars($event->location, ENT_QUOTES, 'UTF-8') ?></div>
                                <div><strong class="text-light">Organizer:</strong> <?= htmlspecialchars($event->organizer, ENT_QUOTES, 'UTF-8') ?></div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge text-bg-info">Messages <?= (int) ($eventStats[$event->id]['messages'] ?? 0) ?></span>
                                <span class="badge text-bg-warning">Pending registrations <?= (int) ($eventStats[$event->id]['pending'] ?? 0) ?></span>
                                <span class="badge text-bg-secondary">Assignments <?= (int) ($eventStats[$event->id]['assignments'] ?? 0) ?></span>
                            </div>

                            <div class="mt-auto d-flex gap-2">
                                <a href="<?= htmlspecialchars(url('dashboard/events-view?event=' . urlencode($event->slug)), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm">Manage</a>
                                <a href="<?= htmlspecialchars(url('event?event=' . urlencode($event->slug)), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-light btn-sm">Public View</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

