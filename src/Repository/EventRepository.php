<?php

namespace Repository;

use Core\Database;
use Entity\Event;
use PDO;

class EventRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllUpcoming(): array {
        /** @noinspection SqlResolve */
        $stmt = $this->db->query('SELECT * FROM events WHERE is_active = 1 ORDER BY event_date, id');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'hydrate'], $rows ?: []);
    }

    public function getAccessibleEventsForUser(?int $userId, bool $isAdmin = false): array {
        if ($isAdmin || $userId === null) {
            return $this->getAllUpcoming();
        }

        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare(
            'SELECT DISTINCT e.*
             FROM events e
             LEFT JOIN event_assignments ea ON ea.event_id = e.id
             WHERE e.is_active = 1
               AND ea.user_id = :user_id
               AND ea.assignment_type IN ("owner", "assigned")
             ORDER BY e.event_date, e.id'
        );
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'hydrate'], $rows ?: []);
    }

    public function findBySlug(string $slug): ?Event {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare('SELECT * FROM events WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->hydrate($row) : null;
    }

    public function findAccessibleBySlug(string $slug, ?int $userId, bool $isAdmin = false): ?Event {
        if ($isAdmin || $userId === null) {
            return $this->findBySlug($slug);
        }

        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare(
            'SELECT DISTINCT e.*
             FROM events e
             LEFT JOIN event_assignments ea ON ea.event_id = e.id
             WHERE e.slug = :slug
               AND e.is_active = 1
               AND ea.user_id = :user_id
               AND ea.assignment_type IN ("owner", "assigned")
             LIMIT 1'
        );
        $stmt->execute(['slug' => $slug, 'user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->hydrate($row) : null;
    }

    public function getEventAssignments(int $eventId): array {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare(
            'SELECT ea.*, u.first_name, u.last_name, u.email
             FROM event_assignments ea
             INNER JOIN users u ON u.id = ea.user_id
             WHERE ea.event_id = :event_id
             ORDER BY ea.assignment_type, ea.created_at DESC, ea.id DESC'
        );
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getAssignableOrganizers(): array {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare(
            'SELECT u.id, u.first_name, u.last_name, u.email
             FROM users u
             INNER JOIN `groups` g ON g.id = u.group_id
             WHERE g.name = :group_name
             ORDER BY u.last_name, u.first_name'
        );
        $stmt->execute(['group_name' => 'Organizator']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function assignUserToEvent(int $eventId, int $userId, int $assignedBy, string $assignmentType = 'assigned', string $notes = ''): bool {
        $assignmentType = in_array($assignmentType, ['owner', 'assigned'], true) ? $assignmentType : 'assigned';
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare(
            'INSERT INTO event_assignments (event_id, user_id, assignment_type, assigned_by, notes)
             VALUES (:event_id, :user_id, :assignment_type, :assigned_by, :notes)
             ON DUPLICATE KEY UPDATE
                assignment_type = VALUES(assignment_type),
                assigned_by = VALUES(assigned_by),
                notes = VALUES(notes)'
        );
        return $stmt->execute([
            'event_id' => $eventId,
            'user_id' => $userId,
            'assignment_type' => $assignmentType,
            'assigned_by' => $assignedBy,
            'notes' => trim($notes),
        ]);
    }

    private function hydrate(array $row): Event {
        $dateRaw = (string) ($row['event_date'] ?? '');
        $formattedDate = $dateRaw !== '' ? date('d M Y', strtotime($dateRaw) ?: time()) : '';

        return new Event([
            'id' => (int) ($row['id'] ?? 0),
            'slug' => (string) ($row['slug'] ?? ''),
            'type' => (string) ($row['type'] ?? ''),
            'type_class' => (string) ($row['type_class'] ?? 'text-bg-secondary'),
            'title' => (string) ($row['title'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'date' => $formattedDate,
            'location' => (string) ($row['location'] ?? ''),
            'status' => (string) ($row['status'] ?? ''),
            'organizer' => (string) ($row['organizer'] ?? ''),
            'organizer_email' => (string) ($row['organizer_email'] ?? ''),
            'details' => (string) ($row['details'] ?? ''),
        ]);
    }

    public function addMessage(int $eventId, int $userId, string $senderName, string $senderEmail, string $subject, string $message): int {
        $table = 'event_messages';
        $stmt = $this->db->prepare(
            'INSERT INTO ' . $table . ' (event_id, user_id, sender_name, sender_email, subject, message)
             VALUES (:event_id, :user_id, :sender_name, :sender_email, :subject, :message)'
        );

        $stmt->execute([
            'event_id' => $eventId,
            'user_id' => $userId,
            'sender_name' => trim($senderName),
            'sender_email' => trim($senderEmail),
            'subject' => trim($subject),
            'message' => trim($message),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getMessagesForEvent(int $eventId, ?int $userId = null): array {
        $table = 'event_messages';
        $sql = 'SELECT * FROM ' . $table . ' WHERE event_id = :event_id';

        $params = ['event_id' => $eventId];
        if ($userId !== null) {
            $sql .= ' AND user_id = :user_id';
            $params['user_id'] = $userId;
        }

        $sql .= ' ORDER BY created_at DESC, id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getMessageReplies(int $messageId): array {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare(
            'SELECT r.*, u.first_name, u.last_name, u.email
             FROM event_message_replies r
             INNER JOIN users u ON u.id = r.user_id
             WHERE r.message_id = :message_id
             ORDER BY r.created_at, r.id'
        );
        $stmt->execute(['message_id' => $messageId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function markMessageRead(int $messageId, int $userId): bool {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare(
            'INSERT INTO event_message_reads (message_id, user_id)
             VALUES (:message_id, :user_id)
             ON DUPLICATE KEY UPDATE read_at = CURRENT_TIMESTAMP'
        );
        return $stmt->execute(['message_id' => $messageId, 'user_id' => $userId]);
    }

    public function isMessageReadByUser(int $messageId, int $userId): bool {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare(
            'SELECT id FROM event_message_reads WHERE message_id = :message_id AND user_id = :user_id LIMIT 1'
        );
        $stmt->execute(['message_id' => $messageId, 'user_id' => $userId]);
        return (bool) $stmt->fetchColumn();
    }

    public function addMessageReply(int $messageId, int $userId, string $replyBody): int {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare(
            'INSERT INTO event_message_replies (message_id, user_id, reply_body)
             VALUES (:message_id, :user_id, :reply_body)'
        );
        $stmt->execute([
            'message_id' => $messageId,
            'user_id' => $userId,
            'reply_body' => trim($replyBody),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getRegistrationRequestsForEvent(int $eventId): array {
        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare(
            'SELECT * FROM event_registration_requests WHERE event_id = :event_id ORDER BY created_at DESC, id DESC'
        );
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getRegistrationRequestForUser(int $eventId, int $userId): ?array {
        $table = 'event_registration_requests';
        $stmt = $this->db->prepare(
            'SELECT * FROM ' . $table . ' WHERE event_id = :event_id AND user_id = :user_id LIMIT 1'
        );
        $stmt->execute(['event_id' => $eventId, 'user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function requestRegistration(int $eventId, int $userId, string $firstName, string $lastName, string $email, string $note = ''): array {
        $existing = $this->getRegistrationRequestForUser($eventId, $userId);
        if ($existing && in_array($existing['status'], ['pending', 'approved'], true)) {
            return ['created' => false, 'id' => (int) $existing['id'], 'status' => $existing['status']];
        }

        if ($existing && (string) $existing['status'] === 'rejected') {
            $table = 'event_registration_requests';
            $stmt = $this->db->prepare(
                'UPDATE ' . $table . '
                 SET first_name = :first_name,
                     last_name = :last_name,
                     email = :email,
                     note = :note,
                     status = "pending",
                     reviewed_by = NULL,
                     review_note = NULL,
                     reviewed_at = NULL
                 WHERE id = :id'
            );
            $stmt->execute([
                'first_name' => trim($firstName),
                'last_name' => trim($lastName),
                'email' => trim($email),
                'note' => trim($note),
                'id' => (int) $existing['id'],
            ]);

            return ['created' => true, 'id' => (int) $existing['id'], 'status' => 'pending'];
        }

        $table = 'event_registration_requests';
        $stmt = $this->db->prepare(
            'INSERT INTO ' . $table . ' (event_id, user_id, first_name, last_name, email, note, status)
             VALUES (:event_id, :user_id, :first_name, :last_name, :email, :note, "pending")'
        );
        $stmt->execute([
            'event_id' => $eventId,
            'user_id' => $userId,
            'first_name' => trim($firstName),
            'last_name' => trim($lastName),
            'email' => trim($email),
            'note' => trim($note),
        ]);

        return ['created' => true, 'id' => (int) $this->db->lastInsertId(), 'status' => 'pending'];
    }

    public function getPendingRegistrationRequests(int $eventId): array {
        $table = 'event_registration_requests';
        $stmt = $this->db->prepare(
            'SELECT * FROM ' . $table . '
             WHERE event_id = :event_id AND status = "pending"
             ORDER BY created_at, id'
        );
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function reviewRegistrationRequest(int $requestId, int $reviewedBy, string $status, string $reviewNote = ''): bool {
        $status = in_array($status, ['approved', 'rejected'], true) ? $status : 'pending';
        if ($status === 'pending') {
            return false;
        }

        /** @noinspection SqlResolve */
        $stmt = $this->db->prepare(
            'UPDATE event_registration_requests
             SET status = :status,
                 reviewed_by = :reviewed_by,
                 review_note = :review_note,
                 reviewed_at = NOW()
             WHERE id = :id AND status = "pending"'
        );

        return $stmt->execute([
            'status' => $status,
            'reviewed_by' => $reviewedBy,
            'review_note' => trim($reviewNote),
            'id' => $requestId,
        ]);
    }

    public function getAllRegistrationRequests(int $eventId): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM event_registration_requests
         WHERE event_id = :event_id
         ORDER BY
            CASE status
                WHEN "pending" THEN 1
                WHEN "approved" THEN 2
                WHEN "rejected" THEN 3
                ELSE 4
            END,
            created_at DESC,
            id DESC'
        );

        $stmt->execute(['event_id' => $eventId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

