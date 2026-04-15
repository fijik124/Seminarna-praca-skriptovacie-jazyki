<?php
$isEdit = $isEdit ?? false;
$id = $id ?? 0;
$regions = $regions ?? [];
$tags = $tags ?? [];
$days = $days ?? [];
$submitLabel = $submitLabel ?? 'Save';
$formData = $formData ?? [];

$actionUrl = $isEdit ? url('dashboard/tracks-edit?id=' . (int) $id) : url('dashboard/tracks-create');
?>

<form method="post" action="<?= htmlspecialchars($actionUrl) ?>" class="row g-3">
    <div class="col-md-6">
        <label for="track_name" class="form-label">Track Name</label>
        <input type="text" id="track_name" name="name" class="form-control" value="<?= htmlspecialchars($formData['name'] ?? '') ?>" required>
    </div>

    <div class="col-md-3">
        <label for="track_region" class="form-label">Region</label>
        <select id="track_region" name="region" class="form-select" required>
            <option value="">Select region</option>
            <?php foreach ($regions as $region): ?>
                <option value="<?= htmlspecialchars($region) ?>" <?= (($formData['region'] ?? '') === $region) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($region) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-3">
        <label for="track_city" class="form-label">City</label>
        <input type="text" id="track_city" name="city" class="form-control" value="<?= htmlspecialchars($formData['city'] ?? '') ?>" required>
    </div>

    <div class="col-md-6">
        <label for="track_difficulty" class="form-label">Difficulty</label>
        <input type="text" id="track_difficulty" name="difficulty" class="form-control" value="<?= htmlspecialchars($formData['difficulty'] ?? '') ?>" required>
    </div>

    <div class="col-md-6">
        <label for="track_surface" class="form-label">Surface</label>
        <input type="text" id="track_surface" name="surface" class="form-control" value="<?= htmlspecialchars($formData['surface'] ?? '') ?>" required>
    </div>

    <div class="col-12">
        <label for="track_description" class="form-label">Description</label>
        <textarea id="track_description" name="description" class="form-control" rows="3" required><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
    </div>

    <div class="col-12">
        <label class="form-label d-block">Tags</label>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($tags as $tag): ?>
                <?php $tagId = 'tag_' . preg_replace('/[^a-z0-9]/', '_', strtolower($tag)); ?>
                <label for="<?= $tagId ?>" class="btn btn-outline-light btn-sm">
                    <input id="<?= $tagId ?>" class="form-check-input me-1" type="checkbox" name="tags[]" value="<?= htmlspecialchars($tag) ?>"
                        <?= in_array($tag, $formData['tags'] ?? [], true) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($tag) ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="col-12 mt-4">
        <h2 class="h5">Open Days and Hours</h2>
    </div>

    <?php foreach ($days as $day): ?>
        <?php
        $slot = $formData['schedule'][$day] ?? null;
        $openValue = is_array($slot) ? ($slot[0] ?? '') : '';
        $closeValue = is_array($slot) ? ($slot[1] ?? '') : '';
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="border border-secondary border-opacity-25 rounded p-3 h-100">
                <p class="fw-semibold text-capitalize mb-2"><?= htmlspecialchars($day) ?></p>
                <div class="row g-2">
                    <div class="col-6">
                        <label for="<?= $day ?>_open" class="form-label small">Open</label>
                        <input type="time" id="<?= $day ?>_open" name="<?= htmlspecialchars($day) ?>_open" class="form-control" value="<?= htmlspecialchars($openValue) ?>">
                    </div>
                    <div class="col-6">
                        <label for="<?= $day ?>_close" class="form-label small">Close</label>
                        <input type="time" id="<?= $day ?>_close" name="<?= htmlspecialchars($day) ?>_close" class="form-control" value="<?= htmlspecialchars($closeValue) ?>">
                    </div>
                </div>
                <small class="text-secondary">Leave both empty if closed.</small>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="col-12 d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><?= htmlspecialchars($submitLabel) ?></button>
        <a href="<?= url('dashboard/tracks') ?>" class="btn btn-outline-light">Cancel</a>
    </div>
</form>
