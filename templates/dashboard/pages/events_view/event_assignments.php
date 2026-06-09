<?php
/**
 * Event Assignments Component
 * Admin-only component for managing event organizer assignments
 *
 * Required variables:
 * - $event: Event object
 * - $assignments: array Current assignments
 * - $organizers: array Available organizers to assign
 */
/**
 * Event Assignments Component
 *
 * @var bool $isAdmin
 * @var object $event
 * @var array $assignments
 * @var array $organizers
 */
?>

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

