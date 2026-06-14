<?php

namespace Entity;

class Contact {
    public ?int $id;

    public string $email;
    public string $fullName;
    public string $subject;
    public string $message;

    public function __construct(array $data = []) {
        $this->id = $data['id'] ?? null;
        $this->fullName = $data['fullName'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->subject = $data['subject'] ?? '';
        $this->message = $data['message'] ?? '';
    }
}

