<?php

use Entity\Track;
use Repository\TrackRepository;

/**
 * Class TrackFilter
 * A Domain Value Object representing the active search and filter criteria.
 */
class TrackFilter
{
    public const ALLOWED_DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    public const ALLOWED_SORT = ['name', 'openDays', 'earliest', 'latest'];

    public function __construct(
            public readonly string $search = '',
            public readonly string $region = '',
            public readonly string $day = '',
            public readonly string $hour = '',
            public readonly string $sort = 'name',
            public readonly array $tags = []
    ) {}

    /**
     * Factory method to securely self-instantiate from raw, untrusted user inputs.
     */
    public static function fromRequest(array $getParams, array $availableTags, array $regions): self
    {
        $search = trim((string) ($getParams['search'] ?? ''));
        $region = trim((string) ($getParams['region'] ?? ''));
        $day    = trim((string) ($getParams['day'] ?? ''));
        $hour   = trim((string) ($getParams['hour'] ?? ''));
        $sort   = trim((string) ($getParams['sort'] ?? 'name'));

        $tagsRaw = is_array($getParams['tags'] ?? null) ? $getParams['tags'] : [];
        $validTags = [];
        foreach ($tagsRaw as $tag) {
            $tag = trim((string) $tag);
            if ($tag !== '' && in_array($tag, $availableTags, true)) {
                $validTags[] = $tag;
            }
        }

        return new self(
                search: $search,
                region: in_array($region, $regions, true) ? $region : '',
                day:    in_array($day, self::ALLOWED_DAYS, true) ? $day : '',
                hour:   preg_match('/^\d{2}:\d{2}$/', $hour) ? $hour : '',
                sort:   in_array($sort, self::ALLOWED_SORT, true) ? $sort : 'name',
                tags:   array_values(array_unique($validTags))
        );
    }
}

/**
 * Class FilterableTrackRepository
 * Handles collection filtering and sorting operations using the TrackFilter specification.
 */
class FilterableTrackRepository
{
    public function __construct(private readonly TrackRepository $baseRepository) {}

    /**
     * Fetches matching tracks dynamically evaluated against a TrackFilter domain entity.
     * @return Track[]
     */
    public function findByFilter(TrackFilter $filter): array
    {
        $tracks = $this->baseRepository->getAll();
        $filtered = $this->applyFilters($tracks, $filter);
        return $this->applySorting($filtered, $filter->sort);
    }

    private function applyFilters(array $tracks, TrackFilter $filter): array
    {
        $search = $this->lowercase($filter->search);

        return array_values(array_filter($tracks, function (Track $track) use ($filter, $search): bool {
            $searchable = $this->lowercase(($track->name ?? '') . ' ' . ($track->city ?? ''));

            if (!$this->contains($searchable, $search)) return false;
            if ($filter->region !== '' && ($track->region ?? '') !== $filter->region) return false;

            foreach ($filter->tags as $tag) {
                if (!in_array($tag, $track->tags ?? [], true)) return false;
            }

            return $this->isTrackOpenAt($track, $filter->day, $filter->hour);
        }));
    }

    private function applySorting(array $tracks, string $sortBy): array
    {
        usort($tracks, function (Track $a, Track $b) use ($sortBy): int {
            return match ($sortBy) {
                'openDays' => $this->calculateOpenDaysCount($b) <=> $this->calculateOpenDaysCount($a),
                'earliest' => $this->calculateEarliestOpening($a) <=> $this->calculateEarliestOpening($b),
                'latest'   => $this->calculateLatestClosing($b) <=> $this->calculateLatestClosing($a),
                default    => strcasecmp((string) ($a->name ?? ''), (string) ($b->name ?? '')),
            };
        });

        return $tracks;
    }

    public function calculateOpenDaysCount(Track $track): int
    {
        $count = 0;
        foreach (TrackFilter::ALLOWED_DAYS as $day) {
            if (!empty($track->schedule[$day])) $count++;
        }
        return $count;
    }

    private function calculateEarliestOpening(Track $track): int
    {
        $min = 9999;
        foreach (TrackFilter::ALLOWED_DAYS as $day) {
            if ($slot = $track->schedule[$day] ?? null) {
                $min = min($min, $this->timeToMinutes((string) $slot[0]));
            }
        }
        return $min;
    }

    private function calculateLatestClosing(Track $track): int
    {
        $max = -1;
        foreach (TrackFilter::ALLOWED_DAYS as $day) {
            if ($slot = $track->schedule[$day] ?? null) {
                $max = max($max, $this->timeToMinutes((string) $slot[1]));
            }
        }
        return $max;
    }

    private function isTrackOpenAt(Track $track, string $day, string $hour): bool
    {
        if ($day === '' || $hour === '') return true;
        if (!$slot = $track->schedule[$day] ?? null) return false;

        $current = $this->timeToMinutes($hour);
        $start   = $this->timeToMinutes((string) $slot[0]);
        $end     = $this->timeToMinutes((string) $slot[1]);

        return $current >= $start && $current <= $end;
    }

    private function timeToMinutes(string $hhmm): int
    {
        [$h, $m] = array_map('intval', explode(':', $hhmm));
        return ($h * 60) + $m;
    }

    private function lowercase(string $value): string
    {
        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }

    private function contains(string $haystack, string $needle): bool
    {
        if ($needle === '') return true;
        return function_exists('mb_strpos')
                ? mb_strpos($haystack, $needle, 0, 'UTF-8') !== false
                : strpos($haystack, $needle) !== false;
    }
}

// Global Controller Initialization Lifecycle Setup
$trackRepo = new TrackRepository();
$availableTags = TrackRepository::getDefaultTags();
$regions = TrackRepository::getRegions();

$filterableRepository = new FilterableTrackRepository($trackRepo);
$activeFilter = TrackFilter::fromRequest($_GET, $availableTags, $regions);

$tracks = $filterableRepository->findByFilter($activeFilter);
$formAction = parse_url($_SERVER['REQUEST_URI'] ?? '/tracks', PHP_URL_PATH) ?: '/tracks';

// Helper view presenter function mapped explicitly for theme contextual style classes
function getBootstrapTagClass(string $tag): string {
    return match (true) {
        str_contains($tag, 'Training')       => 'text-bg-info bg-opacity-25 border border-info text-info',
        str_contains($tag, 'Race')           => 'text-bg-warning bg-opacity-25 border border-warning text-warning',
        $tag === 'Open', $tag === 'Open Time'=> 'text-bg-success bg-opacity-25 border border-success text-success',
        $tag === 'Closed'                    => 'text-bg-danger bg-opacity-25 border border-danger text-danger',
        str_contains($tag, 'Partime')        => 'text-bg-warning bg-opacity-10 border border-warning text-warning-emphasis',
        str_contains($tag, 'Reconstruction') => 'text-bg-primary bg-opacity-25 border border-primary text-primary-emphasis',
        str_contains($tag, 'Abandoned')      => 'text-bg-secondary bg-opacity-25 border border-secondary text-secondary',
        default                              => 'text-bg-light',
    };
}
?>

<section class="py-5 text-light bg-dark min-vh-100">
    <div class="container py-4">

        <div class="p-4 p-md-5 mb-4 bg-dark bg-opacity-75 border border-secondary border-opacity-25 rounded-4 shadow-lg">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <p class="text-uppercase small fw-bold text-info mb-2">Slovakia Motocross Directory</p>
                    <h1 class="display-5 fw-bold mb-3 text-white">Find tracks for training and race preparation</h1>
                    <p class="lead text-secondary mb-0">
                        Browse motocross tracks in all regions of Slovakia. Filter by tags, state (kraj), and opening day/hour.
                    </p>
                </div>
            </div>
        </div>

        <form method="get" action="<?= htmlspecialchars($formAction) ?>" class="p-4 mb-4 bg-dark bg-opacity-75 border border-secondary border-opacity-25 rounded-4 shadow-sm">
            <div class="row g-3 align-items-end">
                <div class="col-md-6 col-xl-3">
                    <label for="tracks_filter_search" class="form-label fw-semibold text-secondary small">Search Track</label>
                    <input type="text" id="tracks_filter_search" name="search" class="form-control bg-black text-white border-secondary border-opacity-50" placeholder="Name or city" value="<?= htmlspecialchars($activeFilter->search) ?>">
                </div>
                <div class="col-md-6 col-xl-3">
                    <label for="tracks_filter_region" class="form-label fw-semibold text-secondary small">Region (Kraj)</label>
                    <select id="tracks_filter_region" name="region" class="form-select bg-black text-white border-secondary border-opacity-50">
                        <option value="">All regions</option>
                        <?php foreach ($regions as $region): ?>
                            <option value="<?= htmlspecialchars($region) ?>"<?= $activeFilter->region === $region ? ' selected' : '' ?>><?= htmlspecialchars($region) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 col-xl-2">
                    <label for="tracks_filter_day" class="form-label fw-semibold text-secondary small">Open Day</label>
                    <select id="tracks_filter_day" name="day" class="form-select bg-black text-white border-secondary border-opacity-50">
                        <option value="">Any day</option>
                        <?php foreach (TrackFilter::ALLOWED_DAYS as $day): ?>
                            <option value="<?= htmlspecialchars($day) ?>"<?= $activeFilter->day === $day ? ' selected' : '' ?>><?= htmlspecialchars(ucfirst($day)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 col-xl-2">
                    <label for="tracks_filter_hour" class="form-label fw-semibold text-secondary small">At Hour</label>
                    <input type="time" id="tracks_filter_hour" name="hour" class="form-control bg-black text-white border-secondary border-opacity-50" value="<?= htmlspecialchars($activeFilter->hour) ?>">
                </div>
                <div class="col-xl-2">
                    <label for="tracks_filter_sort" class="form-label fw-semibold text-secondary small">Sort</label>
                    <select id="tracks_filter_sort" name="sort" class="form-select bg-black text-white border-secondary border-opacity-50">
                        <option value="name"<?= $activeFilter->sort === 'name' ? ' selected' : '' ?>>Name (A-Z)</option>
                        <option value="openDays"<?= $activeFilter->sort === 'openDays' ? ' selected' : '' ?>>Most Open Days</option>
                        <option value="earliest"<?= $activeFilter->sort === 'earliest' ? ' selected' : '' ?>>Earliest Opening</option>
                        <option value="latest"<?= $activeFilter->sort === 'latest' ? ' selected' : '' ?>>Latest Closing</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <p class="text-secondary small mb-2 fw-semibold">Filter by Tags</p>
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($availableTags as $tag):
                        $isChecked = in_array($tag, $activeFilter->tags, true) ? ' checked' : '';
                        $btnClass = $isChecked !== '' ? 'btn btn-warning text-dark' : 'btn btn-outline-secondary text-light';
                        ?>
                        <label class="<?= $btnClass ?> rounded-pill px-3 py-1 btn-sm m-1 cursor-pointer">
                            <input class="form-check-input d-none" type="checkbox" name="tags[]" value="<?= htmlspecialchars($tag) ?>"<?= $isChecked ?> onchange="this.form.submit()">
                            <span><?= htmlspecialchars($tag) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mt-4 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-warning px-4 text-dark fw-semibold">Apply Filters</button>
                <a href="/tracks" class="btn btn-outline-secondary px-4 text-light">Reset</a>
            </div>
        </form>

        <div class="row g-4">
            <?php if (empty($tracks)): ?>
                <div class="col-12">
                    <p class="text-center text-secondary py-5">No tracks match current filters. Try clearing one tag or selecting another day/hour.</p>
                </div>
            <?php else: ?>
                <?php foreach ($tracks as $track):
                    $openDays = $filterableRepository->calculateOpenDaysCount($track);
                    ?>
                    <div class="col-12 col-lg-6">
                        <article class="h-100 p-4 bg-dark bg-opacity-75 border border-secondary border-opacity-25 rounded-4 shadow-sm">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                <div>
                                    <h3 class="h5 mb-1 text-white fw-bold"><?= htmlspecialchars((string)($track->name ?? '')) ?></h3>
                                    <p class="mb-0 text-secondary small"><?= htmlspecialchars((string)($track->city ?? '')) . ', ' . htmlspecialchars((string)($track->region ?? '')) ?></p>
                                </div>
                                <span class="badge text-bg-dark border border-secondary"><?= $openDays ?> open days</span>
                            </div>

                            <p class="small text-secondary mb-3"><?= htmlspecialchars((string)($track->description ?? '')) ?></p>

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <?php foreach (($track->tags ?? []) as $tag): ?>
                                    <span class="badge rounded-pill px-2 py-1 fs-7 <?= getBootstrapTagClass((string)$tag) ?>"><?= htmlspecialchars((string)$tag) ?></span>
                                <?php endforeach; ?>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">Difficulty: <?= htmlspecialchars((string)($track->difficulty ?? '')) ?></span>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Surface: <?= htmlspecialchars((string)($track->surface ?? '')) ?></span>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach (TrackFilter::ALLOWED_DAYS as $day):
                                    $slot = $track->schedule[$day] ?? null;
                                    $dayLabel = ucfirst(substr($day, 0, 3));
                                    ?>
                                    <?php if (!$slot): ?>
                                    <div class="p-2 border border-secondary border-opacity-10 bg-white bg-opacity-5 rounded text-danger text-opacity-75 small">
                                        <strong><?= $dayLabel ?>:</strong> Closed
                                    </div>
                                <?php else: ?>
                                    <div class="p-2 border border-secondary border-opacity-10 bg-white bg-opacity-5 rounded text-light small">
                                        <strong><?= $dayLabel ?>:</strong> <?= htmlspecialchars((string)$slot[0]) ?> - <?= htmlspecialchars((string)$slot[1]) ?>
                                    </div>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>