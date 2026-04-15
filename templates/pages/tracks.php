<?php

use Repository\TrackRepository;
use Entity\Track;

$trackRepo = new TrackRepository();

function tracks_page_data(TrackRepository $repo): array {
    $availableTags = TrackRepository::getDefaultTags();
    $regions = TrackRepository::getRegions();
    $tracks = $repo->getAll();
    $filters = tracks_query_filters($availableTags, $regions);
    $tracks = tracks_filter_tracks($tracks, $filters);
    tracks_sort_tracks($tracks, $filters['sort']);

    return [
        'availableTags' => $availableTags,
        'regions' => $regions,
        'tracks' => $tracks,
        'filters' => $filters,
    ];
}

function tracks_query_filters(array $availableTags, array $regions): array {
	$search = trim((string) ($_GET['search'] ?? ''));
	$region = trim((string) ($_GET['region'] ?? ''));
	$day = trim((string) ($_GET['day'] ?? ''));
	$hour = trim((string) ($_GET['hour'] ?? ''));
	$sort = trim((string) ($_GET['sort'] ?? 'name'));
	$tagsRaw = $_GET['tags'] ?? [];

	if (!is_array($tagsRaw)) {
		$tagsRaw = [];
	}

	$tags = [];
	foreach ($tagsRaw as $tag) {
		$tag = trim((string) $tag);
		if ($tag !== '' && in_array($tag, $availableTags, true)) {
			$tags[] = $tag;
		}
	}

	if (!in_array($region, $regions, true)) {
		$region = '';
	}

	if (!in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], true)) {
		$day = '';
	}

	if (!preg_match('/^\d{2}:\d{2}$/', $hour)) {
		$hour = '';
	}

	$allowedSort = ['name', 'openDays', 'earliest', 'latest'];
	if (!in_array($sort, $allowedSort, true)) {
		$sort = 'name';
	}

	return [
		'search' => $search,
		'region' => $region,
		'day' => $day,
		'hour' => $hour,
		'sort' => $sort,
		'tags' => array_values(array_unique($tags)),
	];
}

function tracks_time_to_minutes(string $hhmm): int {
	[$h, $m] = array_map('intval', explode(':', $hhmm));
	return ($h * 60) + $m;
}

function tracks_open_days_count(Track $track): int {
	$count = 0;
	$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
	foreach ($days as $day) {
		if (!empty($track->schedule[$day])) {
			$count++;
		}
	}
	return $count;
}

function tracks_earliest_opening(Track $track): int {
	$min = 9999;
	$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
	foreach ($days as $day) {
		$slot = $track->schedule[$day] ?? null;
		if (!$slot) {
			continue;
		}

		$start = tracks_time_to_minutes((string) $slot[0]);
		$min = min($min, $start);
	}
	return $min;
}

function tracks_latest_closing(Track $track): int {
	$max = -1;
	$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
	foreach ($days as $day) {
		$slot = $track->schedule[$day] ?? null;
		if (!$slot) {
			continue;
		}

		$end = tracks_time_to_minutes((string) $slot[1]);
		$max = max($max, $end);
	}
	return $max;
}

function tracks_is_open_at(Track $track, string $day, string $hour): bool {
	if ($day === '' || $hour === '') {
		return true;
	}

	$slot = $track->schedule[$day] ?? null;
	if (!$slot) {
		return false;
	}

	$current = tracks_time_to_minutes($hour);
	$start = tracks_time_to_minutes((string) $slot[0]);
	$end = tracks_time_to_minutes((string) $slot[1]);

	return $current >= $start && $current <= $end;
}

function tracks_strtolower(string $value): string {
	if (function_exists('mb_strtolower')) {
		return mb_strtolower($value, 'UTF-8');
	}

	return strtolower($value);
}

function tracks_contains(string $haystack, string $needle): bool {
	if ($needle === '') {
		return true;
	}

	if (function_exists('mb_strpos')) {
		return mb_strpos($haystack, $needle, 0, 'UTF-8') !== false;
	}

	return strpos($haystack, $needle) !== false;
}

function tracks_filter_tracks(array $tracks, array $filters): array {
	$search = tracks_strtolower($filters['search']);

	return array_values(array_filter($tracks, static function (Track $track) use ($filters, $search): bool {
		$searchable = tracks_strtolower(((string) ($track->name ?? '')) . ' ' . ((string) ($track->city ?? '')));
		$matchesSearch = tracks_contains($searchable, $search);
		$matchesRegion = ($filters['region'] === '') || (($track->region ?? '') === $filters['region']);
		$matchesTags = true;

		foreach ($filters['tags'] as $tag) {
			if (!in_array($tag, $track->tags ?? [], true)) {
				$matchesTags = false;
				break;
			}
		}

		$matchesOpenTime = tracks_is_open_at($track, $filters['day'], $filters['hour']);

		return $matchesSearch && $matchesRegion && $matchesTags && $matchesOpenTime;
	}));
}

function tracks_sort_tracks(array &$tracks, string $sortBy): void {
	usort($tracks, static function (Track $a, Track $b) use ($sortBy): int {
		if ($sortBy === 'openDays') {
			return tracks_open_days_count($b) <=> tracks_open_days_count($a);
		}

		if ($sortBy === 'earliest') {
			return tracks_earliest_opening($a) <=> tracks_earliest_opening($b);
		}

		if ($sortBy === 'latest') {
			return tracks_latest_closing($b) <=> tracks_latest_closing($a);
		}

		return strcasecmp((string) ($a->name ?? ''), (string) ($b->name ?? ''));
	});
}

function tracks_tag_class(string $tag): string {
	if (strpos($tag, 'Training') !== false) return 'tag-training';
	if (strpos($tag, 'Race') !== false) return 'tag-race';
	if ($tag === 'Open' || $tag === 'Open Time') return 'tag-open';
	if ($tag === 'Closed') return 'tag-closed';
	if (strpos($tag, 'Partime') !== false) return 'tag-partime';
	if (strpos($tag, 'Reconstruction') !== false) return 'tag-reconstruction';
	if (strpos($tag, 'Abandoned') !== false) return 'tag-abandoned';
	return '';
}

function tracks_day_label(string $day): string {
	return ucfirst(substr($day, 0, 3));
}

function tracks_days(): array {
	return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
}

function tracks_render_region_options(array $regions, string $selectedRegion): void {
	foreach ($regions as $region) {
		$isSelected = $selectedRegion === $region ? ' selected' : '';
		echo '<option value="' . htmlspecialchars($region) . '"' . $isSelected . '>' . htmlspecialchars($region) . '</option>';
	}
}

function tracks_render_day_options(string $selectedDay): void {
	foreach (tracks_days() as $day) {
		$isSelected = $selectedDay === $day ? ' selected' : '';
		echo '<option value="' . htmlspecialchars($day) . '"' . $isSelected . '>' . htmlspecialchars(ucfirst($day)) . '</option>';
	}
}

function tracks_render_tag_inputs(array $availableTags, array $selectedTags): void {
	foreach ($availableTags as $tag) {
		$isChecked = in_array($tag, $selectedTags, true) ? ' checked' : '';
		$chipClass = $isChecked !== '' ? 'tag-chip active' : 'tag-chip';
		echo '<label class="' . $chipClass . '">'
			. '<input class="tag-checkbox" type="checkbox" name="tags[]" value="' . htmlspecialchars($tag) . '"' . $isChecked . '>'
			. '<span>' . htmlspecialchars($tag) . '</span>'
			. '</label>';
	}
}

function tracks_render_cards(array $tracks): void {
	if (!$tracks) {
		echo '<p class="text-center text-secondary py-5">No tracks match current filters. Try clearing one tag or selecting another day/hour.</p>';
		return;
	}

	foreach ($tracks as $track) {
		echo '<div class="col-12 col-lg-6">';
		echo '<article class="track-card">';
		echo '<div class="d-flex justify-content-between align-items-start gap-3 mb-2">';
		echo '<div>';
		echo '<h3 class="h5 mb-1">' . htmlspecialchars((string) ($track->name ?? '')) . '</h3>';
		echo '<p class="mb-0 text-secondary small">' . htmlspecialchars((string) ($track->city ?? '')) . ', ' . htmlspecialchars((string) ($track->region ?? '')) . '</p>';
		echo '</div>';
		echo '<span class="badge text-bg-dark border border-secondary-subtle">' . tracks_open_days_count($track) . ' open days</span>';
		echo '</div>';

		echo '<p class="small text-secondary mb-3">' . htmlspecialchars((string) ($track->description ?? '')) . '</p>';

		echo '<div class="d-flex flex-wrap gap-2 mb-3">';
		foreach (($track->tags ?? []) as $tag) {
			echo '<span class="tag-badge ' . tracks_tag_class((string) $tag) . '">' . htmlspecialchars((string) $tag) . '</span>';
		}
		echo '</div>';

		echo '<div class="d-flex flex-wrap gap-2 mb-3">';
		echo '<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">Difficulty: ' . htmlspecialchars((string) ($track->difficulty ?? '')) . '</span>';
		echo '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Surface: ' . htmlspecialchars((string) ($track->surface ?? '')) . '</span>';
		echo '</div>';

		echo '<div class="d-flex flex-wrap gap-2">';
		$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
		foreach ($days as $day) {
			$slot = $track->schedule[$day] ?? null;
			if (!$slot) {
				echo '<div class="schedule-item schedule-closed"><strong>' . tracks_day_label($day) . ':</strong> Closed</div>';
				continue;
			}

			echo '<div class="schedule-item"><strong>' . tracks_day_label($day) . ':</strong> '
				. htmlspecialchars((string) $slot[0]) . ' - ' . htmlspecialchars((string) $slot[1])
				. '</div>';
		}
		echo '</div>';
		echo '</article>';
		echo '</div>';
	}
}

$pageData = tracks_page_data($trackRepo);
$availableTags = $pageData['availableTags'];
$regions = $pageData['regions'];
$tracks = $pageData['tracks'];
$filters = $pageData['filters'];
$formAction = parse_url($_SERVER['REQUEST_URI'] ?? '/tracks', PHP_URL_PATH) ?: '/tracks';
?>

<section class="py-5 text-light">

	<div class="container py-4">
		<div class="tracks-hero p-4 p-md-5 mb-4">
			<div class="row align-items-center g-4">
				<div class="col-lg-8">
					<p class="text-uppercase small fw-bold text-info mb-2">Slovakia Motocross Directory</p>
					<h1 class="display-5 fw-bold mb-3">Find tracks for training and race preparation</h1>
					<p class="lead text-secondary mb-0">
						Browse motocross tracks in all regions of Slovakia. Filter by tags, state (kraj), and opening day/hour.
					</p>
				</div>
			</div>
		</div>

		<form method="get" action="<?= htmlspecialchars($formAction) ?>" class="tracks-panel p-4 mb-4">
			<div class="row g-3 align-items-end">
				<div class="col-md-6 col-xl-3">
					<label for="tracks_filter_search" class="form-label fw-semibold">Search Track</label>
					<input type="text" id="tracks_filter_search" name="search" class="form-control filter-input" placeholder="Name or city" value="<?= htmlspecialchars($filters['search']) ?>">
				</div>
				<div class="col-md-6 col-xl-3">
					<label for="tracks_filter_region" class="form-label fw-semibold">Region (Kraj)</label>
					<select id="tracks_filter_region" name="region" class="form-select filter-input">
						<option value="">All regions</option>
						<?php tracks_render_region_options($regions, $filters['region']); ?>
					</select>
				</div>
				<div class="col-md-6 col-xl-2">
					<label for="tracks_filter_day" class="form-label fw-semibold">Open Day</label>
					<select id="tracks_filter_day" name="day" class="form-select filter-input">
						<option value="">Any day</option>
						<?php tracks_render_day_options($filters['day']); ?>
					</select>
				</div>
				<div class="col-md-6 col-xl-2">
					<label for="tracks_filter_hour" class="form-label fw-semibold">At Hour</label>
					<input type="time" id="tracks_filter_hour" name="hour" class="form-control filter-input" value="<?= htmlspecialchars($filters['hour']) ?>">
				</div>
				<div class="col-xl-2">
					<label for="tracks_filter_sort" class="form-label fw-semibold">Sort</label>
					<select id="tracks_filter_sort" name="sort" class="form-select filter-input">
						<option value="name"<?= $filters['sort'] === 'name' ? ' selected' : '' ?>>Name (A-Z)</option>
						<option value="openDays"<?= $filters['sort'] === 'openDays' ? ' selected' : '' ?>>Most Open Days</option>
						<option value="earliest"<?= $filters['sort'] === 'earliest' ? ' selected' : '' ?>>Earliest Opening</option>
						<option value="latest"<?= $filters['sort'] === 'latest' ? ' selected' : '' ?>>Latest Closing</option>
					</select>
				</div>
			</div>

			<div class="mt-4">
				<p class="text-secondary small mb-2">Filter by Tags</p>
				<div class="d-flex flex-wrap gap-2">
					<?php tracks_render_tag_inputs($availableTags, $filters['tags']); ?>
				</div>
			</div>

			<div class="mt-4 d-flex flex-wrap gap-2">
				<button type="submit" class="btn btn-primary">Apply Filters</button>
    <a href="<?= url('tracks') ?>" class="btn btn-outline-light">Reset</a>
			</div>
		</form>

		<div class="row g-4"><?php tracks_render_cards($tracks); ?></div>
		<div class="d-none tracks-stat tag-chip active tag-checkbox tag-badge tag-training tag-race tag-open tag-closed tag-partime tag-reconstruction tag-abandoned" aria-hidden="true"></div>
	</div>
</section>

<!--suppress CssUnusedSymbol -->
<style>
	/*noinspection CssUnusedSymbol*/
	.tracks-hero,
	.tracks-panel,
	.track-card,
	.tracks-stat {
		background: rgba(23, 29, 36, 0.82);
		border: 1px solid rgba(255, 255, 255, 0.08);
		border-radius: 1rem;
		backdrop-filter: blur(8px);
	}


	.filter-input {
		background-color: rgba(10, 12, 15, 0.92);
		border-color: rgba(255, 255, 255, 0.18);
		color: #fff;
	}

	.filter-input:focus {
		background-color: rgba(10, 12, 15, 0.92);
		color: #fff;
		border-color: rgba(13, 202, 240, 0.8);
		box-shadow: 0 0 0 0.2rem rgba(13, 202, 240, 0.15);
	}

	.tag-chip {
		display: inline-flex;
		align-items: center;
		gap: 0.4rem;
		border-radius: 999px;
		border: 1px solid rgba(255, 255, 255, 0.18);
		color: #d5dde7;
		background: rgba(255, 255, 255, 0.04);
		transition: all 0.2s ease;
		padding: 0.35rem 0.7rem;
		cursor: pointer;
	}

	.tag-chip:hover,
	.tag-chip.active {
		transform: translateY(-1px);
		color: #071218;
		background: #f8b500;
		border-color: #f8b500;
	}

	.tag-checkbox {
		margin: 0;
	}

	.track-card {
		height: 100%;
		padding: 1.2rem;
		transition: transform 0.22s ease, border-color 0.22s ease;
	}

	.track-card:hover {
		transform: translateY(-4px);
		border-color: rgba(248, 181, 0, 0.65);
	}

	.tag-badge {
		border-radius: 999px;
		font-size: 0.72rem;
		padding: 0.28rem 0.62rem;
		letter-spacing: 0.02em;
		border: 1px solid transparent;
		display: inline-block;
	}

	.tag-training { background: rgba(13, 202, 240, 0.18); color: #9de9f8; border-color: rgba(13, 202, 240, 0.45); }
	.tag-race { background: rgba(255, 193, 7, 0.17); color: #ffe08a; border-color: rgba(255, 193, 7, 0.45); }
	.tag-open { background: rgba(25, 135, 84, 0.2); color: #8ef0be; border-color: rgba(25, 135, 84, 0.52); }
	.tag-closed { background: rgba(220, 53, 69, 0.2); color: #ff9eaa; border-color: rgba(220, 53, 69, 0.48); }
	.tag-partime { background: rgba(255, 193, 7, 0.14); color: #ffe39d; border-color: rgba(255, 193, 7, 0.35); }
	.tag-reconstruction { background: rgba(111, 66, 193, 0.2); color: #ccb3ff; border-color: rgba(111, 66, 193, 0.5); }
	.tag-abandoned { background: rgba(108, 117, 125, 0.25); color: #c4cbd1; border-color: rgba(108, 117, 125, 0.45); }

	.schedule-item {
		background: rgba(255, 255, 255, 0.03);
		border: 1px solid rgba(255, 255, 255, 0.07);
		border-radius: 0.5rem;
		font-size: 0.78rem;
		padding: 0.4rem 0.5rem;
		min-width: 7.2rem;
	}

	.schedule-closed {
		color: #cf6874;
	}

	@media (max-width: 767px) {
		.tracks-hero {
			border-radius: 0.8rem;
		}

		.track-card {
			padding: 1rem;
		}
	}
</style>
