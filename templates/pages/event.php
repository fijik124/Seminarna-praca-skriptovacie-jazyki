<?php

use Repository\AuthRepository;
use Repository\EventRepository;

if (!function_exists('event_detail_redirect')) {
    function event_detail_redirect(string $slug, array $payload = [], string $type = 'msg'): void {
        $query = ['event' => $slug];
        if (!empty($payload)) {
            $query[$type] = urlencode(base64_encode(json_encode($payload)));
        }

        header('Location: ' . url('event?' . http_build_query($query)));
        exit;
    }
}

if (!function_exists('event_detail_flash')) {
    function event_detail_flash(string $param): ?array {
        $raw = $_GET[$param] ?? '';
        if ($raw === '') {
            return null;
        }

        $decoded = base64_decode((string) $raw, true);
        if ($decoded === false) {
            return null;
        }

        $json = json_decode($decoded, true);
        return is_array($json) ? $json : null;
    }
}

if (!function_exists('event_detail_token_field')) {
    function event_detail_token_field(string $slug): string {
        $token = app_issue_idempotency_token('event_detail_' . $slug);
        return '<input type="hidden" name="idempotency_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}

$auth = new AuthRepository();
$events = new EventRepository();
$auth->ensureSession();

$slug = trim((string) ($_GET['event'] ?? ''));
$event = $slug !== '' ? $events->findBySlug($slug) : null;
$currentUser = $auth->getCurrentUser();
$isLoggedIn = $auth->isLoggedIn();
$canMarshalRegister = $isLoggedIn && $auth->hasPermission('event_registration_request_create');
$canReviewRequests = $isLoggedIn && $auth->hasPermission('event_registration_request_review');
$flashMsg = event_detail_flash('msg');
$flashError = event_detail_flash('error');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $event) {
    $action = trim((string) ($_POST['action'] ?? ''));
    $token = trim((string) ($_POST['idempotency_token'] ?? ''));

    if (!app_consume_idempotency_token('event_detail_' . $event->slug, $token)) {
        event_detail_redirect($event->slug, ['type' => 'info', 'text' => 'Action was already processed or expired. Please try again.']);
    }

    try {
        $handled = false;

        if ($action === 'send_message') {
            $handled = true;
            $auth->requireLogin();
            $subject = trim((string) ($_POST['subject'] ?? ''));
            $message = trim((string) ($_POST['message'] ?? ''));

            if ($message === '') {
                event_detail_redirect($event->slug, ['type' => 'error', 'text' => 'Message text is required.'], 'error');
            }

            if ($subject === '') {
                $subject = 'Message about ' . $event->title;
            }

            $events->addMessage(
                (int) $event->id,
                (int) $currentUser['id'],
                (string) ($currentUser['first_name'] . ' ' . $currentUser['last_name']),
                (string) ($currentUser['email'] ?? ''),
                $subject,
                $message
            );
            event_detail_redirect($event->slug, ['type' => 'success', 'text' => 'Your message was sent to the event organizer.']);
        }

        if ($action === 'request_registration') {
            $handled = true;
            $auth->requirePermission('event_registration_request_create');
            $note = trim((string) ($_POST['note'] ?? ''));
            $result = $events->requestRegistration(
                (int) $event->id,
                (int) $currentUser['id'],
                (string) ($currentUser['first_name'] ?? ''),
                (string) ($currentUser['last_name'] ?? ''),
                (string) ($currentUser['email'] ?? ''),
                $note
            );

            if (!empty($result['created'])) {
                event_detail_redirect($event->slug, ['type' => 'success', 'text' => 'Your marshal registration request is pending organizer approval.']);
            }

            event_detail_redirect($event->slug, ['type' => 'info', 'text' => 'You already have a registration request on this event.']);
        }

        if ($action === 'review_registration') {
            $handled = true;
            $auth->requirePermission('event_registration_request_review');
            $requestId = (int) ($_POST['request_id'] ?? 0);
            $decision = trim((string) ($_POST['decision'] ?? ''));
            $reviewNote = trim((string) ($_POST['review_note'] ?? ''));

            if ($requestId <= 0 || !in_array($decision, ['approved', 'rejected'], true)) {
                event_detail_redirect($event->slug, ['type' => 'error', 'text' => 'Invalid review request.'], 'error');
            }

            if ($events->reviewRegistrationRequest($requestId, (int) $currentUser['id'], $decision, $reviewNote)) {
                event_detail_redirect($event->slug, ['type' => 'success', 'text' => 'Registration request updated.']);
            }

            event_detail_redirect($event->slug, ['type' => 'error', 'text' => 'Unable to update registration request.'], 'error');
        }

        if (!$handled) {
            event_detail_redirect($event->slug, ['type' => 'error', 'text' => 'Unknown action.'], 'error');
        }
    } catch (Throwable $e) {
        if (function_exists('app_log')) {
            app_log('error', 'Event detail action failed', [
                'event_slug' => $event->slug,
                'action' => $action,
                'exception_class' => get_class($e),
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        event_detail_redirect($event->slug, ['type' => 'error', 'text' => 'Something went wrong while processing your request.'], 'error');
    }
}

$userMessages = [];
$userRegistration = null;
$pendingRequests = [];

if ($event && $isLoggedIn && $currentUser) {
    $userMessages = $events->getMessagesForEvent((int) $event->id, (int) $currentUser['id']);
    $userRegistration = $events->getRegistrationRequestForUser((int) $event->id, (int) $currentUser['id']);
}

if ($event && $canReviewRequests) {
    $pendingRequests = $events->getPendingRegistrationRequests((int) $event->id);
}
?>
<main data-bs-theme="dark" class="bg-dark text-light min-vh-100">
    <section class="py-5">
        <div class="container">
            <?php if ($event === null): ?>
                <div class="alert alert-warning">
                    Event not found.
                    <div class="mt-3">
                        <a href="<?= htmlspecialchars(url('events'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-light">Back to Events</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
                    <div>
                        <p class="text-uppercase small text-secondary mb-2">RevTrack Event Detail</p>
                        <h1 class="display-5 fw-bold mb-3"><?= htmlspecialchars($event->title, ENT_QUOTES, 'UTF-8') ?></h1>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge <?= htmlspecialchars($event->typeClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($event->type, ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="badge text-bg-secondary"><?= htmlspecialchars($event->status, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </div>
                    <a href="<?= htmlspecialchars(url('events'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-light">Back to Events</a>
                </div>

                <?php if ($flashMsg): ?>
                    <div class="alert alert-<?= htmlspecialchars($flashMsg['type'] ?? 'info', ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($flashMsg['text'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <?php if ($flashError): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($flashError['text'] ?? 'Something went wrong.', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card bg-body-tertiary border-secondary border-opacity-25 mb-4">
                            <div class="card-body p-4 p-md-5">
                                <p class="lead text-secondary mb-4"><?= htmlspecialchars($event->description, ENT_QUOTES, 'UTF-8') ?></p>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <div class="p-3 rounded border border-secondary h-100">
                                            <small class="text-secondary d-block">Date</small>
                                            <strong><?= htmlspecialchars($event->date, ENT_QUOTES, 'UTF-8') ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded border border-secondary h-100">
                                            <small class="text-secondary d-block">Location</small>
                                            <strong><?= htmlspecialchars($event->location, ENT_QUOTES, 'UTF-8') ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded border border-secondary h-100">
                                            <small class="text-secondary d-block">Organizer</small>
                                            <strong><?= htmlspecialchars($event->organizer, ENT_QUOTES, 'UTF-8') ?></strong>
                                            <div class="small text-secondary mt-1"><?= htmlspecialchars($event->organizerEmail, ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-3 rounded border border-secondary bg-dark mb-4">
                                    <h2 class="h5 fw-bold">About this event</h2>
                                    <p class="text-secondary mb-0"><?= nl2br(htmlspecialchars($event->details, ENT_QUOTES, 'UTF-8')) ?></p>
                                </div>

                                <h2 class="h5 fw-bold mb-3">Message the organizer</h2>
                                <?php if ($isLoggedIn): ?>
                                    <form method="post" class="card bg-dark border-secondary border-opacity-25 mb-4">
                                        <div class="card-body p-4">
                                            <input type="hidden" name="action" value="send_message">
                                            <?= event_detail_token_field($event->slug) ?>
                                            <div class="mb-3">
                                                <label for="subject" class="form-label">Subject</label>
                                                <input id="subject" name="subject" type="text" class="form-control bg-dark text-light border-secondary" placeholder="Question about the event">
                                            </div>
                                            <div class="mb-3">
                                                <label for="message" class="form-label">Message</label>
                                                <textarea id="message" name="message" rows="5" class="form-control bg-dark text-light border-secondary" placeholder="Write your message to the organizer"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Send Message</button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        Please <a href="<?= htmlspecialchars(url('login'), ENT_QUOTES, 'UTF-8') ?>" class="alert-link">log in</a> to send a message to the organizer.
                                    </div>
                                <?php endif; ?>

                                <?php if ($isLoggedIn && $canMarshalRegister): ?>
                                    <h2 class="h5 fw-bold mb-3 mt-4">Track marshal registration</h2>
                                    <?php if ($userRegistration && in_array($userRegistration['status'], ['pending', 'approved'], true)): ?>
                                        <div class="alert alert-warning">
                                            Your registration request is currently <strong><?= htmlspecialchars($userRegistration['status'], ENT_QUOTES, 'UTF-8') ?></strong>.
                                        </div>
                                    <?php endif; ?>

                                    <form method="post" class="card bg-dark border-secondary border-opacity-25 mb-4">
                                        <div class="card-body p-4">
                                            <input type="hidden" name="action" value="request_registration">
                                            <?= event_detail_token_field($event->slug) ?>
                                            <div class="mb-3">
                                                <label for="note" class="form-label">Note to organizer</label>
                                                <textarea id="note" name="note" rows="4" class="form-control bg-dark text-light border-secondary" placeholder="Why do you want to join as a track marshal?"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-warning">Request Registration</button>
                                        </div>
                                    </form>
                                <?php elseif ($isLoggedIn): ?>
                                    <div class="alert alert-secondary">
                                        Track marshal registration is available only for users with the Track Marshal role.
                                    </div>
                                <?php endif; ?>

                                <?php if ($isLoggedIn && $userMessages): ?>
                                    <h2 class="h5 fw-bold mb-3 mt-4">Your messages</h2>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($userMessages as $message): ?>
                                            <div class="list-group-item bg-dark text-light border-secondary">
                                                <div class="d-flex justify-content-between gap-3">
                                                    <strong><?= htmlspecialchars($message['subject'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                    <small class="text-secondary"><?= htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8') ?></small>
                                                </div>
                                                <p class="text-secondary mb-0 mt-2"><?= nl2br(htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8')) ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card bg-body-tertiary border-secondary border-opacity-25 mb-4">
                            <div class="card-body p-4">
                                <h2 class="h5 fw-bold mb-3">Contact summary</h2>
                                <p class="text-secondary small mb-0">Organizer email: <strong class="text-light"><?= htmlspecialchars($event->organizerEmail, ENT_QUOTES, 'UTF-8') ?></strong></p>
                            </div>
                        </div>

                        <?php if ($canReviewRequests): ?>
                            <div class="card bg-body-tertiary border-secondary border-opacity-25">
                                <div class="card-body p-4">
                                    <h2 class="h5 fw-bold mb-3">Pending marshal requests</h2>
                                    <?php if (!$pendingRequests): ?>
                                        <div class="text-secondary">No pending requests right now.</div>
                                    <?php else: ?>
                                        <div class="d-grid gap-3">
                                            <?php foreach ($pendingRequests as $request): ?>
                                                <div class="p-3 rounded border border-secondary">
                                                    <div class="d-flex justify-content-between gap-2 mb-2">
                                                        <strong><?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                        <span class="badge text-bg-warning">Pending</span>
                                                    </div>
                                                    <div class="small text-secondary mb-2"><?= htmlspecialchars($request['email'], ENT_QUOTES, 'UTF-8') ?></div>
                                                    <?php if (!empty($request['note'])): ?>
                                                        <p class="small text-secondary mb-3"><?= nl2br(htmlspecialchars($request['note'], ENT_QUOTES, 'UTF-8')) ?></p>
                                                    <?php endif; ?>
                                                    <form method="post" class="d-flex flex-column gap-2">
                                                        <input type="hidden" name="action" value="review_registration">
                                                        <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                                                        <input type="hidden" name="decision" value="approved">
                                                        <?= event_detail_token_field($event->slug) ?>
                                                        <label class="visually-hidden" for="review_note_approve_<?= (int) $request['id'] ?>">Approval note</label>
                                                        <input id="review_note_approve_<?= (int) $request['id'] ?>" type="text" name="review_note" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="Optional approval note">
                                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                    </form>
                                                    <form method="post" class="d-flex flex-column gap-2 mt-2">
                                                        <input type="hidden" name="action" value="review_registration">
                                                        <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                                                        <input type="hidden" name="decision" value="rejected">
                                                        <?= event_detail_token_field($event->slug) ?>
                                                        <label class="visually-hidden" for="review_note_reject_<?= (int) $request['id'] ?>">Rejection note</label>
                                                        <input id="review_note_reject_<?= (int) $request['id'] ?>" type="text" name="review_note" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="Optional rejection note">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                                    </form>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>



