CREATE DATABASE IF NOT EXISTS skola;
USE skola;

CREATE TABLE 'groups' (
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
    FOREIGN KEY (group_id) REFERENCES 'groups' (id) ON DELETE CASCADE,
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
    FOREIGN KEY (group_id) REFERENCES 'groups' (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed initial data
INSERT INTO 'groups' (name) VALUES ('Admin'), ('User');

INSERT INTO permissions (name, slug) VALUES 
('Create Track', 'track_create'),
('Edit Track', 'track_edit'),
('Delete Track', 'track_delete'),
('View Dashboard', 'dashboard_view');

-- Assign permissions to Admin (id 1)
INSERT INTO group_permissions (group_id, permission_id) 
SELECT 1, id FROM permissions;

-- Assign permissions to User (id 2)
INSERT INTO group_permissions (group_id, permission_id) 
SELECT 2, id FROM permissions WHERE slug IN (''); -- No initial permissions for basic user for now

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