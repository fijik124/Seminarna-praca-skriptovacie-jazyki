<?php

namespace Entity;

class User {
    public ?int $id;
    public string $firstName;
    public string $lastName;
    public string $email;
    public string $password;
    public ?int $groupId;

    public function __construct(array $data = []) {
        $this->id = $data['id'] ?? null;
        $this->firstName = $data['first_name'] ?? '';
        $this->lastName = $data['last_name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->groupId = isset($data['group_id']) ? (int)$data['group_id'] : null;
    }

    public function getFullName(): string {
        return trim($this->firstName . ' ' . $this->lastName);
    }
}
