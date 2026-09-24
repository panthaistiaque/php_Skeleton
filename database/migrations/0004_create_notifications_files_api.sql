-- =====================================================================
-- 0004 - Notifications, file uploads, API tokens
-- =====================================================================

CREATE TABLE IF NOT EXISTS `notifications` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    BIGINT UNSIGNED NULL DEFAULT NULL,
    `type`       VARCHAR(60) NOT NULL DEFAULT 'info',
    `title`      VARCHAR(190) NOT NULL,
    `body`       TEXT NULL DEFAULT NULL,
    `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
    `read_at`    DATETIME NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notif_user` (`user_id`),
    KEY `idx_notif_read` (`is_read`),
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `uploads` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`       BIGINT UNSIGNED NULL DEFAULT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `stored_name`   VARCHAR(255) NOT NULL,
    `path`          VARCHAR(500) NOT NULL,
    `mime_type`     VARCHAR(120) NOT NULL DEFAULT 'application/octet-stream',
    `extension`     VARCHAR(20)  NULL DEFAULT NULL,
    `size`          BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `disk`          ENUM('local') NOT NULL DEFAULT 'local',
    `status`        ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_uploads_user` (`user_id`),
    CONSTRAINT `fk_uploads_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `api_tokens` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`      BIGINT UNSIGNED NOT NULL,
    `name`         VARCHAR(120) NOT NULL,
    `token_hash`   VARCHAR(100) NOT NULL,
    `abilities`    VARCHAR(500) NULL DEFAULT NULL,
    `last_used_at` DATETIME NULL DEFAULT NULL,
    `expires_at`   DATETIME NULL DEFAULT NULL,
    `revoked_at`   DATETIME NULL DEFAULT NULL,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_api_tokens_hash` (`token_hash`),
    KEY `idx_api_user` (`user_id`),
    CONSTRAINT `fk_api_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;