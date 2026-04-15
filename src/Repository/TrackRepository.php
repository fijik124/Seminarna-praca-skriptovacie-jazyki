<?php

namespace Repository;

use PDO;
use Entity\Track;
use Core\Database;

class TrackRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->ensureTable();
    }

    private function ensureTable(): void {
        $this->db->exec(
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
    }

    public function getAll(): array {
        /** @noinspection SqlResolve */
        $stmt = $this->db->query('SELECT * FROM `tracks` ORDER BY name');
        $rows = $stmt->fetchAll();
        return array_map([$this, 'hydrate'], $rows);
    }

    public function getById(int $id): ?Track {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare('SELECT * FROM `tracks` WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function create(Track $track): int {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare(
            'INSERT INTO `tracks` (name, region, city, difficulty, surface, description, tags, schedule_json)
             VALUES (:name, :region, :city, :difficulty, :surface, :description, :tags, :schedule_json)'
        );

        $stmt->execute([
            'name' => $track->name,
            'region' => $track->region,
            'city' => $track->city,
            'difficulty' => $track->difficulty,
            'surface' => $track->surface,
            'description' => $track->description,
            'tags' => implode(',', $track->tags),
            'schedule_json' => json_encode($track->schedule, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(Track $track): bool {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare(
            'UPDATE `tracks`
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
            'id' => $track->id,
            'name' => $track->name,
            'region' => $track->region,
            'city' => $track->city,
            'difficulty' => $track->difficulty,
            'surface' => $track->surface,
            'description' => $track->description,
            'tags' => implode(',', $track->tags),
            'schedule_json' => json_encode($track->schedule, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function delete(int $id): bool {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare('DELETE FROM `tracks` WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    private function hydrate(array $row): Track {
        $decodedSchedule = [];
        if (!empty($row['schedule_json'])) {
            $decoded = json_decode((string) $row['schedule_json'], true);
            if (is_array($decoded)) {
                $decodedSchedule = $decoded;
            }
        }

        return new Track([
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'region' => (string) $row['region'],
            'city' => (string) $row['city'],
            'difficulty' => (string) $row['difficulty'],
            'surface' => (string) $row['surface'],
            'description' => (string) $row['description'],
            'tags' => $this->parseTags((string) ($row['tags'] ?? '')),
            'schedule' => $this->normalizeSchedule($decodedSchedule),
        ]);
    }

    private function parseTags(string $raw): array {
        if ($raw === '') return [];
        $parts = array_map('trim', explode(',', $raw));
        return array_values(array_unique(array_filter($parts, fn($tag) => $tag !== '')));
    }

    private function normalizeSchedule(array $schedule): array {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $normalized = [];
        foreach ($days as $day) {
            $slot = $schedule[$day] ?? null;
            if (!is_array($slot) || count($slot) !== 2 || empty($slot[0]) || empty($slot[1])) {
                $normalized[$day] = null;
            } else {
                $normalized[$day] = [trim($slot[0]), trim($slot[1])];
            }
        }
        return $normalized;
    }

    public function validate(array $post): array {
        $errors = [];
        $allowedTags = self::getDefaultTags();
        $allowedRegions = self::getRegions();

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

        $schedule = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($days as $day) {
            $open = trim((string) ($post[$day . '_open'] ?? ''));
            $close = trim((string) ($post[$day . '_close'] ?? ''));

            if ($open === '' || $close === '') {
                $schedule[$day] = null;
            } else {
                $schedule[$day] = [$open, $close];
                if ($open >= $close) {
                    $errors[] = ucfirst($day) . ': opening time must be before closing time.';
                }
            }
        }

        if ($name === '') $errors[] = 'Track name is required.';
        if ($region === '' || !in_array($region, $allowedRegions, true)) $errors[] = 'Please select a valid region.';
        if ($city === '') $errors[] = 'City is required.';
        if ($difficulty === '') $errors[] = 'Difficulty is required.';
        if ($surface === '') $errors[] = 'Surface is required.';
        if ($description === '') $errors[] = 'Description is required.';
        if (count($tags) === 0) $errors[] = 'Select at least one tag.';

        $track = new Track([
            'id' => isset($post['id']) ? (int)$post['id'] : null,
            'name' => $name,
            'region' => $region,
            'city' => $city,
            'difficulty' => $difficulty,
            'surface' => $surface,
            'description' => $description,
            'tags' => $tags,
            'schedule' => $schedule,
        ]);

        return [$track, $errors];
    }

    public static function getDefaultTags(): array {
        return ['Training Track', 'Race Track', 'Open Time', 'Closed', 'Open', 'Partime Closed', 'Reconstruction', 'Abandoned'];
    }

    public static function getRegions(): array {
        return ['Bratislavsky kraj', 'Trnavsky kraj', 'Trenciansky kraj', 'Nitriansky kraj', 'Zilinsky kraj', 'Banskobystricky kraj', 'Presovsky kraj', 'Kosicky kraj'];
    }
}
