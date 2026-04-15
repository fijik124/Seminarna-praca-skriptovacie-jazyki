<?php

namespace Repository;

use PDO;
use Entity\User;
use Core\Database;

class UserRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getByEmail(string $email): ?User {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare('SELECT * FROM `users` WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return new User([
            'id' => (int)$row['id'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => $row['email'],
            'password' => $row['password'],
            'group_id' => $row['group_id']
        ]);
    }

    public function create(User $user): int {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare(
            'INSERT INTO `users` (first_name, last_name, email, password, group_id)
             VALUES (:first_name, :last_name, :email, :password, :group_id)'
        );

        $stmt->execute([
            'first_name' => $user->firstName,
            'last_name' => $user->lastName,
            'email' => $user->email,
            'password' => $user->password,
            'group_id' => $user->groupId
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function authenticate(string $email, string $password): ?User {
        $user = $this->getByEmail($email);
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }
        return null;
    }

    public function update(User $user): bool {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare(
            'UPDATE `users` SET first_name = :first_name, last_name = :last_name, email = :email, group_id = :group_id
             WHERE id = :id'
        );

        return $stmt->execute([
            'first_name' => $user->firstName,
            'last_name' => $user->lastName,
            'email' => $user->email,
            'group_id' => $user->groupId,
            'id' => $user->id
        ]);
    }

    public function updatePassword(int $userId, string $hashedPassword): bool {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare('UPDATE `users` SET password = :password WHERE id = :id');
        return $stmt->execute(['password' => $hashedPassword, 'id' => $userId]);
    }
}
