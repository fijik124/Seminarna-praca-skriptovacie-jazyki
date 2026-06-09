<?php
use Repository\AuthRepository;
use Repository\EventRepository;

$auth = new AuthRepository();
$auth->ensureSession();
$currentUser = $auth->getCurrentUser();
$currentUserId = (int)($currentUser['id'] ?? 0);

$isAdmin = $auth->isAdmin();
$isOrganizer = $auth->isOrganizer();

$canManageMessages = $isAdmin || $isOrganizer || $auth->hasPermission('event_message_reply');

$canReviewRegistrations = $isAdmin || $isOrganizer || $auth->hasPermission('event_registration_request_review');

$eventRepo = new EventRepository();
$slug = trim((string)($_GET['event'] ?? ''));
$event = $slug !== '' ? $eventRepo->findAccessibleBySlug($slug, $currentUserId, $isAdmin) : null;

// Build absolute URL for Nginx redirect compliance
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$currentScript = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
$baseUrl = $protocol . '://' . $host . $currentScript;

// Fetch flash messages from URL parameters
$flash = null;
if (!empty($_GET['msg']) && !empty($_GET['type'])) {
    $flash = [
            'type' => trim((string)$_GET['type']),
            'text' => trim((string)$_GET['msg'])
    ];
}

// Load data for view components
$assignments = $event ? $eventRepo->getEventAssignments((int)$event->id) : [];
$organizers = $eventRepo->getAssignableOrganizers();
$messages = $event ? $eventRepo->getMessagesForEvent((int)$event->id) : [];
$registrations = $event ? $eventRepo->getRegistrationRequestsForEvent((int)$event->id) : [];
$repliesByMessage = [];
foreach ($messages as $message) {
    $repliesByMessage[$message['id']] = $eventRepo->getMessageReplies((int)$message['id']);
}
?>
    <section class="pt-5 pb-4 mt-5">
        <div class="container">
            <?php if (!$event): ?>
                <div class="alert alert-warning">Event not found or you do not have access to it.</div>
                <a href="?event=" class="btn btn-outline-light">Back to events</a>
            <?php else: ?>
                <?php require __DIR__ . '/events_view/event_header.php'; ?>

                <?php if ($flash): ?>
                    <div class="alert alert-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($flash['text'], ENT_QUOTES, 'UTF-8') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <?php
                        require __DIR__ . '/events_view/event_details.php';
                        require __DIR__ . '/events_view/event_registrations.php';
                        require __DIR__ . '/events_view/event_messages.php';
                        ?>
                    </div>
                    <div class="col-lg-4">
                        <?php require __DIR__ . '/events_view/event_assignments.php'; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>