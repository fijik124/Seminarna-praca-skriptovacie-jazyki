<?php
/**
 * Event Registrations Component
 * Displays and manages event registration requests
 *
 * @var object|null $event
 * @var array $registrations
 * @var bool $canReviewRegistrations
 * @var array $currentUser
 */
?>

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

