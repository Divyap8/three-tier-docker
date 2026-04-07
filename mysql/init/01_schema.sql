-- ─────────────────────────────────────────────────────────────
--  Database Initialisation Script
--  Runs automatically on first container start
-- ─────────────────────────────────────────────────────────────

USE appdb;

-- ── Schema ────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    username   VARCHAR(64)     NOT NULL UNIQUE,
    email      VARCHAR(255)    NOT NULL UNIQUE,
    password   VARCHAR(255)    NOT NULL,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS posts (
    id         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED    NOT NULL,
    title      VARCHAR(255)    NOT NULL,
    body       TEXT            NOT NULL,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Seed data ─────────────────────────────────────────────────

INSERT IGNORE INTO users (username, email, password) VALUES
    ('admin',   'admin@example.com',  SHA2('adminpass', 256)),
    ('devuser', 'dev@example.com',    SHA2('devpass',   256));

INSERT IGNORE INTO posts (user_id, title, body) VALUES
    (1, 'Welcome to Three-Tier Docker', 'This stack runs Nginx → PHP-FPM → MySQL using Docker Compose with private network segmentation.'),
    (1, 'Health Checks & Restart Policies', 'All containers include Docker health checks and restart policies for automatic recovery.');
