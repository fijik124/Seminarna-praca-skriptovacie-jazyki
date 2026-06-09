<?php
/**
 * Event Details Component
 * Displays core event information: description, date, location, details
 *
 * Required variables:
 * - $event: Event object
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

<div class="card bg-body-tertiary border-secondary border-opacity-25 mb-4">
    <div class="card-body p-4">
        <p class="text-secondary mb-4"><?= htmlspecialchars($event->description, ENT_QUOTES, 'UTF-8') ?></p>
        <div class="row g-3">
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
                </div>
            </div>
        </div>
        <div class="p-3 rounded border border-secondary mt-4">
            <h2 class="h5 fw-bold">Details</h2>
            <p class="text-secondary mb-0"><?= nl2br(htmlspecialchars($event->details, ENT_QUOTES, 'UTF-8')) ?></p>
        </div>
    </div>
</div>

