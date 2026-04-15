<?php

namespace Repository;

use PDO;
use Core\Database;
use Entity\Group;

class GroupRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?Group {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare('SELECT * FROM `groups` WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        $permissions = $this->getGroupPermissions($id);
        
        return new Group([
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'permissions' => $permissions
        ]);
    }

    public function getGroupPermissions(int $groupId): array {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare('
            SELECT p.slug 
            FROM `permissions` p
            JOIN `group_permissions` gp ON p.id = gp.permission_id
            WHERE gp.group_id = :group_id
        ');
        $stmt->execute(['group_id' => $groupId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function findByName(string $name): ?Group {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare('SELECT * FROM `groups` WHERE name = :name');
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch();

        if (!$row) return null;

        $permissions = $this->getGroupPermissions((int)$row['id']);
        
        return new Group([
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'permissions' => $permissions
        ]);
    }
}
