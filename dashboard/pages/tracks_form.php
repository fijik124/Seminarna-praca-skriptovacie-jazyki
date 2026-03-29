<?php
$actionUrl = $isEdit ? '/dashboard/tracks-edit?id=' . (int) $id : '/dashboard/tracks-create';
?>

<form method="post" action="<?= htmlspecialchars($actionUrl) ?>" class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Track Name</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars((string) ($formData['name'] ?? '')) ?>" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Region</label>
        <select name="region" class="form-select" required>
            <option value="">Select region</option>
            <?php foreach ($regions as $region): ?>
                <option value="<?= htmlspecialchars($region) ?>" <?= (($formData['region'] ?? '') === $region) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($region) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">City</label>
        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars((string) ($formData['city'] ?? '')) ?>" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Difficulty</label>
        <input type="text" name="difficulty" class="form-control" value="<?= htmlspecialchars((string) ($formData['difficulty'] ?? '')) ?>" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Surface</label>
        <input type="text" name="surface" class="form-control" value="<?= htmlspecialchars((string) ($formData['surface'] ?? '')) ?>" required>
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars((string) ($formData['description'] ?? '')) ?></textarea>
    </div>

    <div class="col-12">
        <label class="form-label d-block">Tags</label>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($tags as $tag): ?>
                <label class="btn btn-outline-light btn-sm">
                    <input class="form-check-input me-1" type="checkbox" name="tags[]" value="<?= htmlspecialchars($tag) ?>"
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
        $openValue = is_array($slot) ? (string) ($slot[0] ?? '') : '';
        $closeValue = is_array($slot) ? (string) ($slot[1] ?? '') : '';
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="border border-secondary border-opacity-25 rounded p-3 h-100">
                <p class="fw-semibold text-capitalize mb-2"><?= htmlspecialchars($day) ?></p>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small">Open</label>
                        <input type="time" name="<?= htmlspecialchars($day) ?>_open" class="form-control" value="<?= htmlspecialchars($openValue) ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Close</label>
                        <input type="time" name="<?= htmlspecialchars($day) ?>_close" class="form-control" value="<?= htmlspecialchars($closeValue) ?>">
                    </div>
                </div>
                <small class="text-secondary">Leave both empty if closed.</small>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="col-12 d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><?= htmlspecialchars($submitLabel) ?></button>
        <a href="/dashboard/tracks" class="btn btn-outline-light">Cancel</a>
    </div>
</form>
