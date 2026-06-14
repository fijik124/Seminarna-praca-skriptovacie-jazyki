<?php

namespace Repository;

use PDO;
use Entity\Contact;
use Core\Database;

class ContactRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): array {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare('SELECT * FROM `contact` WHERE email = :email');
        $stmt->execute(['email' => $email]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(Contact $contact): int {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare(
            'INSERT INTO `contact` (fullName, email, subject,message)
             VALUES (:fullName, :email, :subject, :message)'
        );

        $stmt->execute([
            'fullName' => $contact->fullName,
            'email' => $contact->email,
            'subject' => $contact->subject,
            'message' => $contact->message
        ]);

        return (int)$this->db->lastInsertId();
    }
}
