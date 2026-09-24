-- =====================================================================
-- 0005 - Upload categories
-- =====================================================================

ALTER TABLE `uploads` ADD COLUMN `category` VARCHAR(120) NULL DEFAULT NULL AFTER `extension`;
ALTER TABLE `uploads` ADD KEY `idx_uploads_category` (`category`);