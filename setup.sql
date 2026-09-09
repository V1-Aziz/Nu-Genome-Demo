-- GenomePlatform NU - database schema
--
-- Load with:
--   /Applications/XAMPP/xamppfiles/bin/mysql -u root < setup.sql

CREATE DATABASE IF NOT EXISTS genome_platform
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE genome_platform;

-- ---------------------------------------------------------------- users

CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    username   VARCHAR(100) NOT NULL,
    email      VARCHAR(190) NOT NULL,
    password   VARCHAR(255) NOT NULL,               -- bcrypt hash, never plaintext
    role       ENUM('user','researcher','admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_users_email (email),
    UNIQUE KEY uniq_users_username (username),
    KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------ analysis_results

CREATE TABLE IF NOT EXISTS analysis_results (
    id               INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id          INT UNSIGNED NOT NULL,
    allele_frequency DECIMAL(6,5)  NOT NULL,
    cadd_score       DECIMAL(5,2)  NOT NULL,
    consequence_type VARCHAR(50)   NOT NULL,
    rarity           VARCHAR(20)   NOT NULL,
    pathogenic_score DECIMAL(5,2)  NOT NULL,
    risk_level       VARCHAR(20)   NOT NULL,
    notes            TEXT NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_results_user_created (user_id, created_at),
    CONSTRAINT fk_results_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------- demo accounts
--
-- These bcrypt hashes are real and do verify. The ones in the previous
-- schema were malformed, so those accounts could never be logged into.
--
--   admin@genomeplatform.local       asas1212
--   researcher@genomeplatform.local  ChangeMe!Research1
--   user@genomeplatform.local        ChangeMe!User1
--
-- Change or delete them before this is reachable by anyone else.

INSERT INTO users (username, email, password, role) VALUES
    ('admin',      'admin@genomeplatform.local',      '$2y$10$zKGzohU744z7DElIHwdLbOwYohQrSMMZ4iHr5VLklYer8Fu5v.i9K',    'admin'),
    ('researcher', 'researcher@genomeplatform.local', '$2y$10$VtNWSgHKDsrR4JNYhynrhOgVJNm6H6o2JR6Zv8H4pzmcG/6cfwYSG', 'researcher'),
    ('demo',       'user@genomeplatform.local',       '$2y$10$RhNpGaGJNNfA40wyi7CqT.9lAid5rzSUAjqNY5c4AQJ/kSdn2ItwC',     'user')
ON DUPLICATE KEY UPDATE username = username;
