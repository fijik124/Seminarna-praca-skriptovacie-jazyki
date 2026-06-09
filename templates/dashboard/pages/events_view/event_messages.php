<?php
/**
 * Event Messages Component
 * Displays messages and allows managing replies
 *
 * @var object|null $event
 * @var \Repository\EventRepository $eventRepo
 * @var array $messages
 * @var array $repliesByMessage
 * @var bool $canManageMessages
 * @var array $currentUser
 */
?>

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

