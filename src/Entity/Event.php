<?php

namespace Entity;

class Event {
    public ?int $id;
    public string $slug;
    public string $type;
    public string $typeClass;
    public string $title;
    public string $description;
    public string $date;
    public string $location;
    public string $status;
    public string $organizer;
    public string $organizerEmail;
    public string $details;

    public function __construct(array $data = []) {
        $this->id = $data['id'] ?? null;
        $this->slug = $data['slug'] ?? '';
        $this->type = $data['type'] ?? '';
        $this->typeClass = $data['type_class'] ?? 'text-bg-secondary';
        $this->title = $data['title'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->date = $data['date'] ?? '';
        $this->location = $data['location'] ?? '';
        $this->status = $data['status'] ?? '';
        $this->organizer = $data['organizer'] ?? '';
        $this->organizerEmail = $data['organizer_email'] ?? '';
        $this->details = $data['details'] ?? '';
    }
}

