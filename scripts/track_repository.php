<?php

function track_default_tags(): array {
    return [
        'Training Track',
        'Race Track',
        'Open Time',
        'Closed',
        'Open',
        'Partime Closed',
        'Reconstruction',
        'Abandoned',
    ];
}

function track_regions(): array {
    return [
        'Bratislavsky kraj',
        'Trnavsky kraj',
        'Trenciansky kraj',
        'Nitriansky kraj',
        'Zilinsky kraj',
        'Banskobystricky kraj',
        'Presovsky kraj',
        'Kosicky kraj',
    ];
}

function track_days(): array {
    return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
}

function ensure_tracks_table(PDO $pdo): void {
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tracks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            region VARCHAR(80) NOT NULL,
            city VARCHAR(80) NOT NULL,
            difficulty VARCHAR(40) NOT NULL,
            surface VARCHAR(80) NOT NULL,
            description TEXT NOT NULL,
            tags TEXT NOT NULL,
            schedule_json TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tracks_region (region),
            INDEX idx_tracks_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $ensured = true;
}

function track_parse_tags(string $raw): array {
    if ($raw === '') {
        return [];
    }

    $parts = array_map('trim', explode(',', $raw));
    $parts = array_values(array_filter($parts, static fn($tag) => $tag !== ''));

    return array_values(array_unique($parts));
}

function track_normalize_schedule(array $schedule): array {
    $normalized = [];

    foreach (track_days() as $day) {
        $slot = $schedule[$day] ?? null;

        if (!is_array($slot) || count($slot) !== 2) {
            $normalized[$day] = null;
            continue;
        }

        $open = trim((string) ($slot[0] ?? ''));
        $close = trim((string) ($slot[1] ?? ''));

        if ($open === '' || $close === '') {
            $normalized[$day] = null;
            continue;
        }

        $normalized[$day] = [$open, $close];
    }

    return $normalized;
}

function track_schedule_from_form(array $post): array {
    $schedule = [];

    foreach (track_days() as $day) {
        $open = trim((string) ($post[$day . '_open'] ?? ''));
        $close = trim((string) ($post[$day . '_close'] ?? ''));

        if ($open === '' || $close === '') {
            $schedule[$day] = null;
            continue;
        }

        $schedule[$day] = [$open, $close];
    }

    return track_normalize_schedule($schedule);
}

function track_validate_payload(array $post): array {
    $errors = [];
    $allowedTags = track_default_tags();
    $allowedRegions = track_regions();

    $name = trim((string) ($post['name'] ?? ''));
    $region = trim((string) ($post['region'] ?? ''));
    $city = trim((string) ($post['city'] ?? ''));
    $difficulty = trim((string) ($post['difficulty'] ?? ''));
    $surface = trim((string) ($post['surface'] ?? ''));
    $description = trim((string) ($post['description'] ?? ''));

    $tagsInput = $post['tags'] ?? [];
    if (!is_array($tagsInput)) {
        $tagsInput = [];
    }

    $tags = [];
    foreach ($tagsInput as $tag) {
        $tag = trim((string) $tag);
        if ($tag !== '' && in_array($tag, $allowedTags, true)) {
            $tags[] = $tag;
        }
    }
    $tags = array_values(array_unique($tags));

    $schedule = track_schedule_from_form($post);

    if ($name === '') {
        $errors[] = 'Track name is required.';
    }
    if ($region === '' || !in_array($region, $allowedRegions, true)) {
        $errors[] = 'Please select a valid region.';
    }
    if ($city === '') {
        $errors[] = 'City is required.';
    }
    if ($difficulty === '') {
        $errors[] = 'Difficulty is required.';
    }
    if ($surface === '') {
        $errors[] = 'Surface is required.';
    }
    if ($description === '') {
        $errors[] = 'Description is required.';
    }
    if (count($tags) === 0) {
        $errors[] = 'Select at least one tag.';
    }

    foreach (track_days() as $day) {
        $slot = $schedule[$day] ?? null;
        if (!$slot) {
            continue;
        }

        if ($slot[0] >= $slot[1]) {
            $errors[] = ucfirst($day) . ': opening time must be before closing time.';
        }
    }

    $payload = [
        'name' => $name,
        'region' => $region,
        'city' => $city,
        'difficulty' => $difficulty,
        'surface' => $surface,
        'description' => $description,
        'tags' => $tags,
        'schedule' => $schedule,
    ];

    return [$payload, $errors];
}

function track_fetch_all(PDO $pdo): array {
    ensure_tracks_table($pdo);

    $stmt = $pdo->query('SELECT * FROM tracks ORDER BY name ASC');
    $rows = $stmt->fetchAll();

    return array_map('track_hydrate_row', $rows);
}

function track_fetch_one(PDO $pdo, int $id): ?array {
    ensure_tracks_table($pdo);

    $stmt = $pdo->prepare('SELECT * FROM tracks WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }

    return track_hydrate_row($row);
}

function track_insert(PDO $pdo, array $payload): int {
    ensure_tracks_table($pdo);

    $stmt = $pdo->prepare(
        'INSERT INTO tracks (name, region, city, difficulty, surface, description, tags, schedule_json)
         VALUES (:name, :region, :city, :difficulty, :surface, :description, :tags, :schedule_json)'
    );

    $stmt->execute([
        'name' => $payload['name'],
        'region' => $payload['region'],
        'city' => $payload['city'],
        'difficulty' => $payload['difficulty'],
        'surface' => $payload['surface'],
        'description' => $payload['description'],
        'tags' => implode(',', $payload['tags']),
        'schedule_json' => json_encode($payload['schedule'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);

    return (int) $pdo->lastInsertId();
}

function track_update(PDO $pdo, int $id, array $payload): bool {
    ensure_tracks_table($pdo);

    $stmt = $pdo->prepare(
        'UPDATE tracks
         SET name = :name,
             region = :region,
             city = :city,
             difficulty = :difficulty,
             surface = :surface,
             description = :description,
             tags = :tags,
             schedule_json = :schedule_json
         WHERE id = :id'
    );

    return $stmt->execute([
        'id' => $id,
        'name' => $payload['name'],
        'region' => $payload['region'],
        'city' => $payload['city'],
        'difficulty' => $payload['difficulty'],
        'surface' => $payload['surface'],
        'description' => $payload['description'],
        'tags' => implode(',', $payload['tags']),
        'schedule_json' => json_encode($payload['schedule'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
}

function track_hydrate_row(array $row): array {
    $decodedSchedule = [];
    if (!empty($row['schedule_json'])) {
        $decoded = json_decode((string) $row['schedule_json'], true);
        if (is_array($decoded)) {
            $decodedSchedule = $decoded;
        }
    }

    return [
        'id' => (int) $row['id'],
        'name' => (string) $row['name'],
        'region' => (string) $row['region'],
        'city' => (string) $row['city'],
        'difficulty' => (string) $row['difficulty'],
        'surface' => (string) $row['surface'],
        'description' => (string) $row['description'],
        'tags' => track_parse_tags((string) ($row['tags'] ?? '')),
        'schedule' => track_normalize_schedule($decodedSchedule),
    ];
}

function track_fallback_tracks(): array {
    return [
        [
            'name' => 'Mx Park Zahorie',
            'region' => 'Bratislavsky kraj',
            'city' => 'Malacky',
            'difficulty' => 'Intermediate',
            'surface' => 'Sand / Clay',
            'description' => 'Fast technical laps with whoops and jump sections for regular training sessions.',
            'tags' => ['Training Track', 'Open', 'Open Time'],
            'schedule' => [
                'monday' => ['09:00', '18:00'],
                'tuesday' => ['09:00', '18:00'],
                'wednesday' => ['09:00', '18:00'],
                'thursday' => ['09:00', '18:00'],
                'friday' => ['09:00', '18:00'],
                'saturday' => ['08:00', '19:00'],
                'sunday' => ['08:00', '17:00'],
            ],
        ],
        [
            'name' => 'Revuca Race Arena',
            'region' => 'Banskobystricky kraj',
            'city' => 'Revuca',
            'difficulty' => 'Advanced',
            'surface' => 'Hard Pack',
            'description' => 'Long race-ready track often used for competition weekends and club races.',
            'tags' => ['Race Track', 'Open', 'Open Time'],
            'schedule' => [
                'monday' => null,
                'tuesday' => ['13:00', '19:00'],
                'wednesday' => ['13:00', '19:00'],
                'thursday' => ['13:00', '19:00'],
                'friday' => ['13:00', '19:00'],
                'saturday' => ['09:00', '19:00'],
                'sunday' => ['09:00', '18:00'],
            ],
        ],
        [
            'name' => 'Trencin MX Valley',
            'region' => 'Trenciansky kraj',
            'city' => 'Trencin',
            'difficulty' => 'Beginner',
            'surface' => 'Mixed Surface',
            'description' => 'Friendly training area with shorter loops and coaching-focused sections.',
            'tags' => ['Training Track', 'Partime Closed'],
            'schedule' => [
                'monday' => null,
                'tuesday' => ['15:00', '19:00'],
                'wednesday' => ['15:00', '19:00'],
                'thursday' => ['15:00', '19:00'],
                'friday' => ['15:00', '19:00'],
                'saturday' => ['10:00', '17:00'],
                'sunday' => null,
            ],
        ],
        [
            'name' => 'Nitra Dirt Club',
            'region' => 'Nitriansky kraj',
            'city' => 'Nitra',
            'difficulty' => 'Intermediate',
            'surface' => 'Clay',
            'description' => 'Compact motocross line with rhythm lanes and seasonal evening openings.',
            'tags' => ['Training Track', 'Open Time', 'Open'],
            'schedule' => [
                'monday' => ['16:00', '20:00'],
                'tuesday' => ['16:00', '20:00'],
                'wednesday' => ['16:00', '20:00'],
                'thursday' => ['16:00', '20:00'],
                'friday' => ['16:00', '20:00'],
                'saturday' => ['09:00', '18:00'],
                'sunday' => ['09:00', '15:00'],
            ],
        ],
        [
            'name' => 'Orava Offroad Circuit',
            'region' => 'Zilinsky kraj',
            'city' => 'Dolny Kubin',
            'difficulty' => 'Advanced',
            'surface' => 'Mud / Clay',
            'description' => 'High elevation track with deep turns, ideal for race pace preparation.',
            'tags' => ['Race Track', 'Open', 'Open Time'],
            'schedule' => [
                'monday' => null,
                'tuesday' => ['14:00', '19:00'],
                'wednesday' => ['14:00', '19:00'],
                'thursday' => ['14:00', '19:00'],
                'friday' => ['14:00', '19:00'],
                'saturday' => ['09:00', '18:00'],
                'sunday' => ['09:00', '18:00'],
            ],
        ],
        [
            'name' => 'Tatran Gravel MX',
            'region' => 'Presovsky kraj',
            'city' => 'Poprad',
            'difficulty' => 'Intermediate',
            'surface' => 'Gravel / Hard Pack',
            'description' => 'Mountain-side circuit with mixed speed sections and technical corners.',
            'tags' => ['Training Track', 'Reconstruction'],
            'schedule' => [
                'monday' => null,
                'tuesday' => null,
                'wednesday' => ['12:00', '17:00'],
                'thursday' => ['12:00', '17:00'],
                'friday' => ['12:00', '17:00'],
                'saturday' => ['10:00', '16:00'],
                'sunday' => null,
            ],
        ],
        [
            'name' => 'Kosice Industrial Track',
            'region' => 'Kosicky kraj',
            'city' => 'Kosice',
            'difficulty' => 'Expert',
            'surface' => 'Hard Pack / Sand',
            'description' => 'Wide race style layout with high jumps and spectator zones.',
            'tags' => ['Race Track', 'Open', 'Open Time'],
            'schedule' => [
                'monday' => ['13:00', '20:00'],
                'tuesday' => ['13:00', '20:00'],
                'wednesday' => ['13:00', '20:00'],
                'thursday' => ['13:00', '20:00'],
                'friday' => ['13:00', '20:00'],
                'saturday' => ['09:00', '20:00'],
                'sunday' => ['09:00', '18:00'],
            ],
        ],
        [
            'name' => 'Dunajska Historic MX',
            'region' => 'Trnavsky kraj',
            'city' => 'Dunajska Streda',
            'difficulty' => 'Intermediate',
            'surface' => 'Clay',
            'description' => 'Legacy track that is currently not maintained and closed for public rides.',
            'tags' => ['Closed', 'Abandoned'],
            'schedule' => [
                'monday' => null,
                'tuesday' => null,
                'wednesday' => null,
                'thursday' => null,
                'friday' => null,
                'saturday' => null,
                'sunday' => null,
            ],
        ],
    ];
}

function track_resolve_listing_tracks(?PDO $pdo = null): array {
    $tracks = track_fallback_tracks();

    if (!$pdo) {
        return $tracks;
    }

    try {
        $dbTracks = track_fetch_all($pdo);
        if ($dbTracks) {
            return $dbTracks;
        }
    } catch (Throwable $e) {
        if (function_exists('log_to_dev_panel')) {
            log_to_dev_panel('Tracks fallback data used: ' . $e->getMessage(), 'warning');
        }
    }

    return $tracks;
}
