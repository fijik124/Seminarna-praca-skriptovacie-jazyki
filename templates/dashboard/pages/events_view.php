<?php

use Repository\AuthRepository;
use Repository\EventRepository;

$auth = new AuthRepository();
$auth->ensureSession();
$currentUser = $auth->getCurrentUser();
$isAdmin = $auth->isAdmin();
$isOrganizer = $auth->isOrganizer();
$canManageMessages = $isAdmin || $isOrganizer || $auth->hasPermission('event_message_reply');
$canReviewRegistrations = $isAdmin || $isOrganizer || $auth->hasPermission('event_registration_request_review');

$eventRepo = new EventRepository();
$slug = trim((string) ($_GET['event'] ?? ''));
$event = $slug !== '' ? $eventRepo->findAccessibleBySlug($slug, (int) ($currentUser['id'] ?? 0), $isAdmin) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $event) {
    $action = trim((string) ($_POST['action'] ?? ''));

    try {
        if ($action === 'assign_organizer' && $isAdmin) {
            $assignedUserId = (int) ($_POST['assigned_user_id'] ?? 0);
            $assignmentType = trim((string) ($_POST['assignment_type'] ?? 'assigned'));
            $notes = trim((string) ($_POST['notes'] ?? ''));

            if ($assignedUserId > 0) {
                $eventRepo->assignUserToEvent((int) $event->id, $assignedUserId, (int) ($currentUser['id'] ?? 0), $assignmentType, $notes);
            }

            header('Location: ' . url('dashboard/events-view?event=' . urlencode($event->slug) . '&msg=' . urlencode(base64_encode(json_encode(['type' => 'success', 'text' => 'Organizer assignment updated.'])))));
            exit;
        }

        if ($action === 'mark_message_read' && $canManageMessages) {
            $messageId = (int) ($_POST['message_id'] ?? 0);
            if ($messageId > 0) {
                $eventRepo->markMessageRead($messageId, (int) ($currentUser['id'] ?? 0));
            }

            header('Location: ' . url('dashboard/events-view?event=' . urlencode($event->slug) . '&msg=' . urlencode(base64_encode(json_encode(['type' => 'info', 'text' => 'Message marked as read.'])))));
            exit;
        }

        if ($action === 'reply_message' && $canManageMessages) {
            $messageId = (int) ($_POST['message_id'] ?? 0);
            $replyBody = trim((string) ($_POST['reply_body'] ?? ''));
            if ($messageId > 0 && $replyBody !== '') {
                $eventRepo->addMessageReply($messageId, (int) ($currentUser['id'] ?? 0), $replyBody);
                $eventRepo->markMessageRead($messageId, (int) ($currentUser['id'] ?? 0));
            }

            header('Location: ' . url('dashboard/events-view?event=' . urlencode($event->slug) . '&msg=' . urlencode(base64_encode(json_encode(['type' => 'success', 'text' => 'Reply sent.'])))));
            exit;
        }

        if ($action === 'review_registration' && $canReviewRegistrations) {
            $requestId = (int) ($_POST['request_id'] ?? 0);
            $decision = trim((string) ($_POST['decision'] ?? ''));
            $reviewNote = trim((string) ($_POST['review_note'] ?? ''));
            if ($requestId > 0 && in_array($decision, ['approved', 'rejected'], true)) {
                $eventRepo->reviewRegistrationRequest($requestId, (int) ($currentUser['id'] ?? 0), $decision, $reviewNote);
            }

            header('Location: ' . url('dashboard/events-view?event=' . urlencode($event->slug) . '&msg=' . urlencode(base64_encode(json_encode(['type' => 'success', 'text' => 'Registration request updated.'])))));
            exit;
        }
    } catch (Throwable $e) {
        if (function_exists('app_log')) {
            app_log('error', 'Dashboard event action failed', [
                'event_slug' => $event->slug,
                'action' => $action,
                'exception_class' => get_class($e),
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }
}

$flash = null;
if (!empty($_GET['msg'])) {
    $decoded = base64_decode((string) $_GET['msg'], true);
    if ($decoded !== false) {
        $flash = json_decode($decoded, true);
    }
}

$assignments = $event ? $eventRepo->getEventAssignments((int) $event->id) : [];
$organizers = $eventRepo->getAssignableOrganizers();
$messages = $event ? $eventRepo->getMessagesForEvent((int) $event->id) : [];
$registrations = $event ? $eventRepo->getRegistrationRequestsForEvent((int) $event->id) : [];
$repliesByMessage = [];
foreach ($messages as $message) {
    $repliesByMessage[$message['id']] = $eventRepo->getMessageReplies((int) $message['id']);
}
?>

<section class="pt-5 pb-4 mt-5">
    <div class="container">
        <?php if (!$event): ?>
            <div class="alert alert-warning">
                Event not found or you do not have access to it.
            </div>
            <a href="<?= url('dashboard/events') ?>" class="btn btn-outline-light">Back to events</a>
        <?php else: ?>
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <p class="text-secondary text-uppercase small mb-1">Dashboard Event Management</p>
                    <h1 class="h3 fw-bold mb-2"><?= htmlspecialchars($event->title, ENT_QUOTES, 'UTF-8') ?></h1>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge <?= htmlspecialchars($event->typeClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($event->type, ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="badge text-bg-secondary"><?= htmlspecialchars($event->status, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= url('dashboard/events') ?>" class="btn btn-outline-light">Back to list</a>
                    <a href="<?= url('event?event=' . urlencode($event->slug)) ?>" class="btn btn-outline-info">Public page</a>
                </div>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'info', ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($flash['text'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card bg-body-tertiary border-secondary border-opacity-25 mb-4">
                        <div class="card-body p-4">
                            <p class="text-secondary mb-4"><?= htmlspecialchars($event->description, ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="row g-3">
                                <div class="col-md-4"><div class="p-3 rounded border border-secondary h-100"><small class="text-secondary d-block">Date</small><strong><?= htmlspecialchars($event->date, ENT_QUOTES, 'UTF-8') ?></strong></div></div>
                                <div class="col-md-4"><div class="p-3 rounded border border-secondary h-100"><small class="text-secondary d-block">Location</small><strong><?= htmlspecialchars($event->location, ENT_QUOTES, 'UTF-8') ?></strong></div></div>
                                <div class="col-md-4"><div class="p-3 rounded border border-secondary h-100"><small class="text-secondary d-block">Organizer</small><strong><?= htmlspecialchars($event->organizer, ENT_QUOTES, 'UTF-8') ?></strong></div></div>
                            </div>
                            <div class="p-3 rounded border border-secondary mt-4">
                                <h2 class="h5 fw-bold">Details</h2>
                                <p class="text-secondary mb-0"><?= nl2br(htmlspecialchars($event->details, ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-body-tertiary border-secondary border-opacity-25 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h5 fw-bold mb-0">Registrations</h2>
                                <span class="badge text-bg-warning">Pending <?= count(array_filter($registrations, static fn(array $r): bool => ($r['status'] ?? '') === 'pending')) ?></span>
                            </div>
                            <?php if (!$registrations): ?>
                                <div class="text-secondary">No registration requests yet.</div>
                            <?php else: ?>
                                <div class="d-grid gap-3">
                                    <?php foreach ($registrations as $request): ?>
                                        <div class="p-3 rounded border border-secondary">
                                            <div class="d-flex justify-content-between gap-2 mb-2">
                                                <strong><?= htmlspecialchars(trim($request['first_name'] . ' ' . $request['last_name']), ENT_QUOTES, 'UTF-8') ?></strong>
                                                <span class="badge <?= $request['status'] === 'approved' ? 'text-bg-success' : ($request['status'] === 'rejected' ? 'text-bg-danger' : 'text-bg-warning') ?>"><?= htmlspecialchars($request['status'], ENT_QUOTES, 'UTF-8') ?></span>
                                            </div>
                                            <div class="small text-secondary mb-2"><?= htmlspecialchars($request['email'], ENT_QUOTES, 'UTF-8') ?></div>
                                            <?php if (!empty($request['note'])): ?>
                                                <p class="small text-secondary mb-2"><?= nl2br(htmlspecialchars($request['note'], ENT_QUOTES, 'UTF-8')) ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($request['review_note'])): ?>
                                                <div class="small text-info mb-2">Review note: <?= nl2br(htmlspecialchars($request['review_note'], ENT_QUOTES, 'UTF-8')) ?></div>
                                            <?php endif; ?>
                                            <?php if ($canReviewRegistrations && $request['status'] === 'pending'): ?>
                                                <form method="post" class="d-flex flex-wrap gap-2 align-items-end">
                                                    <input type="hidden" name="action" value="review_registration">
                                                    <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                                                    <div class="flex-grow-1">
                                                        <label class="form-label small mb-1" for="review_note_<?= (int) $request['id'] ?>">Review note</label>
                                                        <input id="review_note_<?= (int) $request['id'] ?>" type="text" name="review_note" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="Optional note">
                                                    </div>
                                                    <button type="submit" name="decision" value="approved" class="btn btn-sm btn-success">Approve</button>
                                                    <button type="submit" name="decision" value="rejected" class="btn btn-sm btn-outline-danger">Reject</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card bg-body-tertiary border-secondary border-opacity-25">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h5 fw-bold mb-0">Messages</h2>
                                <span class="badge text-bg-info">Inbox <?= count($messages) ?></span>
                            </div>

                            <?php if (!$messages): ?>
                                <div class="text-secondary">No messages for this event yet.</div>
                            <?php else: ?>
                                <div class="d-grid gap-3">
                                    <?php foreach ($messages as $message): ?>
                                        <?php $isRead = $eventRepo->isMessageReadByUser((int) $message['id'], (int) ($currentUser['id'] ?? 0)); ?>
                                        <div class="p-3 rounded border border-secondary">
                                            <div class="d-flex justify-content-between gap-2 mb-2">
                                                <strong><?= htmlspecialchars($message['subject'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                <span class="badge <?= $isRead ? 'text-bg-success' : 'text-bg-warning' ?>"><?= $isRead ? 'Read' : 'Unread' ?></span>
                                            </div>
                                            <div class="small text-secondary mb-2">
                                                From <?= htmlspecialchars($message['sender_name'], ENT_QUOTES, 'UTF-8') ?> &lt;<?= htmlspecialchars($message['sender_email'], ENT_QUOTES, 'UTF-8') ?>&gt; · <?= htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <p class="text-secondary mb-3"><?= nl2br(htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8')) ?></p>

                                            <?php if (!empty($repliesByMessage[$message['id']])): ?>
                                                <div class="mb-3 ps-3 border-start border-secondary">
                                                    <?php foreach ($repliesByMessage[$message['id']] as $reply): ?>
                                                        <div class="mb-2">
                                                            <div class="small text-info"><?= htmlspecialchars(trim(($reply['first_name'] ?? '') . ' ' . ($reply['last_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($reply['created_at'], ENT_QUOTES, 'UTF-8') ?></div>
                                                            <div class="small text-secondary"><?= nl2br(htmlspecialchars($reply['reply_body'], ENT_QUOTES, 'UTF-8')) ?></div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($canManageMessages): ?>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <?php if (!$isRead): ?>
                                                        <form method="post">
                                                            <input type="hidden" name="action" value="mark_message_read">
                                                            <input type="hidden" name="message_id" value="<?= (int) $message['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-info">Mark Read</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                                <form method="post" class="mt-3">
                                                    <input type="hidden" name="action" value="reply_message">
                                                    <input type="hidden" name="message_id" value="<?= (int) $message['id'] ?>">
                                                    <label class="form-label small mb-1" for="reply_<?= (int) $message['id'] ?>">Reply</label>
                                                    <textarea id="reply_<?= (int) $message['id'] ?>" name="reply_body" rows="3" class="form-control bg-dark text-light border-secondary" placeholder="Write reply to this user"></textarea>
                                                    <button type="submit" class="btn btn-sm btn-primary mt-2">Send Reply</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <?php if ($isAdmin): ?>
                        <div class="card bg-body-tertiary border-secondary border-opacity-25 mb-4">
                            <div class="card-body p-4">
                                <h2 class="h5 fw-bold mb-3">Assignments</h2>
                                <?php if ($organizers): ?>
                                    <div class="list-group list-group-flush mb-3">
                                        <?php foreach ($assignments as $assignment): ?>
                                            <div class="list-group-item bg-dark text-light border-secondary">
                                                <strong><?= htmlspecialchars(trim($assignment['first_name'] . ' ' . $assignment['last_name']), ENT_QUOTES, 'UTF-8') ?></strong>
                                                <div class="small text-secondary"><?= htmlspecialchars($assignment['assignment_type'], ENT_QUOTES, 'UTF-8') ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <form method="post">
                                    <input type="hidden" name="action" value="assign_organizer">
                                    <div class="mb-3">
                                        <label for="assigned_user_id" class="form-label">Assign organizer</label>
                                        <select id="assigned_user_id" name="assigned_user_id" class="form-select bg-dark text-light border-secondary">
                                            <?php foreach ($organizers as $organizer): ?>
                                                <option value="<?= (int) $organizer['id'] ?>"><?= htmlspecialchars($organizer['first_name'] . ' ' . $organizer['last_name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($organizer['email'], ENT_QUOTES, 'UTF-8') ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="assignment_type" class="form-label">Assignment type</label>
                                        <select id="assignment_type" name="assignment_type" class="form-select bg-dark text-light border-secondary">
                                            <option value="assigned">Assigned</option>
                                            <option value="owner">Owner</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <input id="notes" type="text" name="notes" class="form-control bg-dark text-light border-secondary" placeholder="Optional note">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Assignment</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="card bg-body-tertiary border-secondary border-opacity-25 mb-4">
                        <div class="card-body p-4">
                            <h2 class="h5 fw-bold mb-3">Assigned people</h2>
                            <?php if (!$assignments): ?>
                                <div class="text-secondary">No one assigned yet.</div>
                            <?php else: ?>
                                <div class="d-grid gap-2">
                                    <?php foreach ($assignments as $assignment): ?>
                                        <div class="p-2 rounded border border-secondary">
                                            <strong><?= htmlspecialchars(trim($assignment['first_name'] . ' ' . $assignment['last_name']), ENT_QUOTES, 'UTF-8') ?></strong>
                                            <div class="small text-secondary"><?= htmlspecialchars($assignment['assignment_type'], ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

