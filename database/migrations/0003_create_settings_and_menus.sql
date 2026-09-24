-- =====================================================================
-- 0003 - Settings store + dynamic menus
-- =====================================================================

CREATE TABLE IF NOT EXISTS `settings` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key`   VARCHAR(120) NOT NULL,
    `setting_value` TEXT NULL DEFAULT NULL,
    `setting_type`  ENUM('string','int','bool','json','array') NOT NULL DEFAULT 'string',
    `group_name`    VARCHAR(60) NOT NULL DEFAULT 'general',
    `is_encrypted`  TINYINT(1) NOT NULL DEFAULT 0,
    `autoload`      TINYINT(1) NOT NULL DEFAULT 1,
    `updated_at`    DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_settings_key` (`setting_key`),
    KEY `idx_settings_group` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `menus` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id`    BIGINT UNSIGNED NULL DEFAULT NULL,
    `title`        VARCHAR(120) NOT NULL,
    `slug`         VARCHAR(120) NOT NULL,
    `route`        VARCHAR(190) NULL DEFAULT NULL,
    `icon`         VARCHAR(60)  NULL DEFAULT NULL,
    `permission`   VARCHAR(120) NULL DEFAULT NULL,
    `module`       VARCHAR(60)  NOT NULL DEFAULT 'general',
    `sort_order`   INT NOT NULL DEFAULT 0,
    `status`       ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at`   DATETIME NULL DEFAULT NULL,
    `updated_at`   DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_menus_slug` (`slug`),
    KEY `idx_menus_parent` (`parent_id`),
    KEY `idx_menus_module` (`module`),
    CONSTRAINT `fk_menus_parent` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;