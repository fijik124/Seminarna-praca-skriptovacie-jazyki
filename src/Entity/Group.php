<?php

namespace Entity;

class Group {
    public ?int $id;
    public string $name;
    /** @var string[] */
    public array $permissions = [];

    public function __construct(array $data = []) {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->permissions = $data['permissions'] ?? [];
    }
}
