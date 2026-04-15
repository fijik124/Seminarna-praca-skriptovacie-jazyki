<?php

namespace Entity;

class Track {
    public ?int $id;
    public string $name;
    public string $region;
    public string $city;
    public string $difficulty;
    public string $surface;
    public string $description;
    public array $tags;
    public array $schedule;

    public function __construct(array $data = []) {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->region = $data['region'] ?? '';
        $this->city = $data['city'] ?? '';
        $this->difficulty = $data['difficulty'] ?? '';
        $this->surface = $data['surface'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->tags = $data['tags'] ?? [];
        $this->schedule = $data['schedule'] ?? [];
    }
}
