-- =====================================================================
-- 0002 - Security tracking: login history, activities, security events
-- =====================================================================

CREATE TABLE IF NOT EXISTS `login_history` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     BIGINT UNSIGNED NULL DEFAULT NULL,
    `email`       VARCHAR(190) NULL DEFAULT NULL,
    `ip_address`  VARCHAR(45) NULL DEFAULT NULL,
    `user_agent`  VARCHAR(500) NULL DEFAULT NULL,
    `device_type` VARCHAR(30)  NOT NULL DEFAULT 'unknown',
    `browser`     VARCHAR(100) NULL DEFAULT NULL,
    `platform`    VARCHAR(100) NULL DEFAULT NULL,
    `status`      ENUM('success','failed','lockout','logout') NOT NULL DEFAULT 'success',
    `reason`      VARCHAR(255) NULL DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lh_user` (`user_id`),
    KEY `idx_lh_email` (`email`),
    KEY `idx_lh_status` (`status`),
    KEY `idx_lh_created` (`created_at`),
    CONSTRAINT `fk_lh_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `activities` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     BIGINT UNSIGNED NULL DEFAULT NULL,
    `action`      VARCHAR(100) NOT NULL,
    `module`      VARCHAR(100) NULL DEFAULT 'general',
    `description` VARCHAR(500) NULL DEFAULT NULL,
    `method`      VARCHAR(10)  NULL DEFAULT NULL,
    `url`         VARCHAR(500) NULL DEFAULT NULL,
    `ip_address`  VARCHAR(45) NULL DEFAULT NULL,
    `user_agent`  VARCHAR(500) NULL DEFAULT NULL,
    `context`     TEXT NULL DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_act_user` (`user_id`),
    KEY `idx_act_action` (`action`),
    KEY `idx_act_module` (`module`),
    KEY `idx_act_created` (`created_at`),
    CONSTRAINT `fk_act_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `security_events` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     BIGINT UNSIGNED NULL DEFAULT NULL,
    `event_type`  VARCHAR(100) NOT NULL,
    `severity`    ENUM('info','warning','critical') NOT NULL DEFAULT 'info',
    `message`     VARCHAR(500) NULL DEFAULT NULL,
    `ip_address`  VARCHAR(45) NULL DEFAULT NULL,
    `user_agent`  VARCHAR(500) NULL DEFAULT NULL,
    `context`     TEXT NULL DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_se_event` (`event_type`),
    KEY `idx_se_severity` (`severity`),
    KEY `idx_se_created` (`created_at`),
    CONSTRAINT `fk_se_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;