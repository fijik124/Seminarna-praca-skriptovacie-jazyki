CREATE DATABASE IF NOT EXISTS skola;
USE skola;

CREATE TABLE `groups` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE group_permissions (
    group_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (group_id, permission_id),
    FOREIGN KEY (group_id) REFERENCES `groups` (id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Length allows for secure PHP password_hash()
    group_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES `groups` (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed initial data
INSERT INTO `groups` (name) VALUES ('Admin'), ('User'), ('Track Marshal'), ('Organizator');

INSERT INTO permissions (name, slug) VALUES 
('Create Track', 'track_create'),
('Edit Track', 'track_edit'),
('Delete Track', 'track_delete'),
('View Dashboard', 'dashboard_view'),
('Request Event Registration', 'event_registration_request_create'),
('Review Event Registration', 'event_registration_request_review'),
('Reply to Event Message', 'event_message_reply');

-- Assign permissions to Admin (id 1)
INSERT INTO group_permissions (group_id, permission_id) 
SELECT 1, id FROM permissions;

-- Assign permissions to User (id 2)
INSERT INTO group_permissions (group_id, permission_id) 
SELECT 2, id FROM permissions WHERE slug IN (''); -- No initial permissions for basic user for now

-- Assign marshal registration permission to Track Marshal (id 3)
INSERT INTO group_permissions (group_id, permission_id)
SELECT 3, id FROM permissions WHERE slug = 'event_registration_request_create';

-- Assign organizer permissions (id 4)
INSERT INTO group_permissions (group_id, permission_id)
SELECT 4, id FROM permissions WHERE slug IN ('dashboard_view', 'event_registration_request_review', 'event_message_reply');

CREATE TABLE tracks (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    type VARCHAR(80) NOT NULL,
    type_class VARCHAR(40) NOT NULL DEFAULT 'text-bg-secondary',
    title VARCHAR(160) NOT NULL,
    description TEXT NOT NULL,
    event_date DATE NOT NULL,
    location VARCHAR(160) NOT NULL,
    status VARCHAR(80) NOT NULL,
    organizer VARCHAR(160) NOT NULL,
    organizer_email VARCHAR(190) NOT NULL,
    details TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_events_date (event_date),
    INDEX idx_events_active_date (is_active, event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE event_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    sender_name VARCHAR(160) NOT NULL,
    sender_email VARCHAR(190) NOT NULL,
    subject VARCHAR(160) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_messages_event (event_id),
    INDEX idx_event_messages_user (user_id),
    FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE event_message_reads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_message_read (message_id, user_id),
    INDEX idx_message_reads_message (message_id),
    INDEX idx_message_reads_user (user_id),
    FOREIGN KEY (message_id) REFERENCES event_messages (id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE event_message_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    reply_body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_message_replies_message (message_id),
    INDEX idx_message_replies_user (user_id),
    FOREIGN KEY (message_id) REFERENCES event_messages (id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE event_registration_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(190) NOT NULL,
    note TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    reviewed_by INT DEFAULT NULL,
    review_note TEXT NULL,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_event_request (event_id, user_id),
    INDEX idx_event_request_event (event_id),
    INDEX idx_event_request_status (status),
    INDEX idx_event_request_user (user_id),
    FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE event_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    assignment_type VARCHAR(20) NOT NULL DEFAULT 'assigned',
    assigned_by INT DEFAULT NULL,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_event_assignment (event_id, user_id),
    INDEX idx_event_assignment_event (event_id),
    INDEX idx_event_assignment_user (user_id),
    FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO events (slug, type, type_class, title, description, event_date, location, status, organizer, organizer_email, details)
VALUES
('bratislava-mx-open', 'Race Weekend', 'text-bg-primary', 'Bratislava MX Open', 'Regional motocross race with training sessions and qualifying rounds.', '2026-05-12', 'Bratislava Region', 'Registration Open', 'RevTrack Racing Club', 'events@revtrack.test', 'Full weekend format: Saturday training and qualifying, Sunday race blocks for multiple rider classes.'),
('youth-skill-camp', 'Training Event', 'text-bg-info', 'Youth Skill Camp', 'Technical training day focused on safety, starts, and corner control.', '2026-05-20', 'Trnava Region', 'Limited Capacity', 'Youth MX Academy', 'academy@revtrack.test', 'Coached drills, bike setup checks, and small-group sessions tailored for younger riders and beginners.'),
('race-staff-coordination', 'Organizer Briefing', 'text-bg-warning', 'Race Staff Coordination', 'Planning session for marshals, commissioners, and event logistics.', '2026-05-28', 'Online + Nitra', 'Internal Registration', 'RevTrack Operations Team', 'ops@revtrack.test', 'Internal operations runbook review, staffing assignments, safety checkpoints, and communication protocols.');

