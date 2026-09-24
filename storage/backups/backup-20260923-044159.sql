-- Skeleton App backup generated 2026-09-23 04:41:59
-- Database: skeleton_app
SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- Table structure for `activities`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `activities`;

CREATE TABLE `activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(100) DEFAULT 'general',
  `description` varchar(500) DEFAULT NULL,
  `method` varchar(10) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `context` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_act_user` (`user_id`),
  KEY `idx_act_action` (`action`),
  KEY `idx_act_module` (`module`),
  KEY `idx_act_created` (`created_at`),
  CONSTRAINT `fk_act_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Dumping data for `activities`
-- ------------------------------------------------------------
INSERT INTO `activities` (`id`,`user_id`,`action`,`module`,`description`,`method`,`url`,`ip_address`,`user_agent`,`context`,`created_at`) VALUES ('1','1','login','auth','User logged in',NULL,NULL,'::1',NULL,NULL,'2026-09-23 10:39:42');
INSERT INTO `activities` (`id`,`user_id`,`action`,`module`,`description`,`method`,`url`,`ip_address`,`user_agent`,`context`,`created_at`) VALUES ('2','1','user_created','users','User created: jane@example.com','POST','http://localhost/php_Skeleton/users','::1','curl/8.21.0',NULL,'2026-09-23 10:40:07');
INSERT INTO `activities` (`id`,`user_id`,`action`,`module`,`description`,`method`,`url`,`ip_address`,`user_agent`,`context`,`created_at`) VALUES ('3','1','role_created','rbac','Role created: Editor','POST','http://localhost/php_Skeleton/roles','::1','curl/8.21.0',NULL,'2026-09-23 10:40:07');
INSERT INTO `activities` (`id`,`user_id`,`action`,`module`,`description`,`method`,`url`,`ip_address`,`user_agent`,`context`,`created_at`) VALUES ('4','1','department_created','organization','Department created: IT','POST','http://localhost/php_Skeleton/departments','::1','curl/8.21.0',NULL,'2026-09-23 10:40:59');
INSERT INTO `activities` (`id`,`user_id`,`action`,`module`,`description`,`method`,`url`,`ip_address`,`user_agent`,`context`,`created_at`) VALUES ('5','1','user_created','users','User created: jane2@example.com','POST','http://localhost/php_Skeleton/users','::1','curl/8.21.0',NULL,'2026-09-23 10:41:08');
INSERT INTO `activities` (`id`,`user_id`,`action`,`module`,`description`,`method`,`url`,`ip_address`,`user_agent`,`context`,`created_at`) VALUES ('6','1','user_updated','users','User updated: superadmin@example.com','PUT','http://localhost/php_Skeleton/users/1','::1','curl/8.21.0',NULL,'2026-09-23 10:41:45');
INSERT INTO `activities` (`id`,`user_id`,`action`,`module`,`description`,`method`,`url`,`ip_address`,`user_agent`,`context`,`created_at`) VALUES ('7','1','permissions_assigned','rbac','Permissions updated for role Super Administrator',NULL,NULL,'::1',NULL,NULL,'2026-09-23 10:41:45');
INSERT INTO `activities` (`id`,`user_id`,`action`,`module`,`description`,`method`,`url`,`ip_address`,`user_agent`,`context`,`created_at`) VALUES ('8','1','user_created','users','User created: temp@example.com','POST','http://localhost/php_Skeleton/users','::1','curl/8.21.0',NULL,'2026-09-23 10:41:58');
INSERT INTO `activities` (`id`,`user_id`,`action`,`module`,`description`,`method`,`url`,`ip_address`,`user_agent`,`context`,`created_at`) VALUES ('9','1','profile_updated','profile','Profile details updated','POST','http://localhost/php_Skeleton/profile','::1','curl/8.21.0',NULL,'2026-09-23 10:41:58');

-- ------------------------------------------------------------
-- Table structure for `api_tokens`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `api_tokens`;

CREATE TABLE `api_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `token_hash` varchar(100) NOT NULL,
  `abilities` varchar(500) DEFAULT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_api_tokens_hash` (`token_hash`),
  KEY `idx_api_user` (`user_id`),
  CONSTRAINT `fk_api_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table structure for `departments`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `departments`;

CREATE TABLE `departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `code` varchar(30) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_departments_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Dumping data for `departments`
-- ------------------------------------------------------------
INSERT INTO `departments` (`id`,`name`,`code`,`description`,`status`,`created_at`,`updated_at`) VALUES ('1','Human Resources','HR','People operations and administration','active',NULL,NULL);
INSERT INTO `departments` (`id`,`name`,`code`,`description`,`status`,`created_at`,`updated_at`) VALUES ('2','Information Technology','IT','Systems, infrastructure and development','active',NULL,NULL);
INSERT INTO `departments` (`id`,`name`,`code`,`description`,`status`,`created_at`,`updated_at`) VALUES ('3','Finance','FIN','Accounting, budgeting and payroll','active',NULL,NULL);
INSERT INTO `departments` (`id`,`name`,`code`,`description`,`status`,`created_at`,`updated_at`) VALUES ('4','Operations','OPS','Day-to-day business operations','active',NULL,NULL);
INSERT INTO `departments` (`id`,`name`,`code`,`description`,`status`,`created_at`,`updated_at`) VALUES ('9','IT','IT','IT team','active','2026-09-23 04:40:59','2026-09-23 04:40:59');

-- ------------------------------------------------------------
-- Table structure for `designations`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `designations`;

CREATE TABLE `designations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_designations_department` (`department_id`),
  CONSTRAINT `fk_designations_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Dumping data for `designations`
-- ------------------------------------------------------------
INSERT INTO `designations` (`id`,`name`,`department_id`,`description`,`status`,`created_at`,`updated_at`) VALUES ('1','HR Officer','1',NULL,'active',NULL,NULL);
INSERT INTO `designations` (`id`,`name`,`department_id`,`description`,`status`,`created_at`,`updated_at`) VALUES ('2','Software Engineer','2',NULL,'active',NULL,NULL);
INSERT INTO `designations` (`id`,`name`,`department_id`,`description`,`status`,`created_at`,`updated_at`) VALUES ('3','System Administrator','2',NULL,'active',NULL,NULL);
INSERT INTO `designations` (`id`,`name`,`department_id`,`description`,`status`,`created_at`,`updated_at`) VALUES ('4','Finance Officer','3',NULL,'active',NULL,NULL);
INSERT INTO `designations` (`id`,`name`,`department_id`,`description`,`status`,`created_at`,`updated_at`) VALUES ('5','Operations Manager','4',NULL,'active',NULL,NULL);
INSERT INTO `designations` (`id`,`name`,`department_id`,`description`,`status`,`created_at`,`updated_at`) VALUES ('6','HR Officer','1',NULL,'active',NULL,NULL);
INSERT INTO `designations` (`id`,`name`,`department_id`,`description`,`status`,`created_at`,`updated_at`) VALUES ('7','Software Engineer','2',NULL,'active',NULL,NULL);
INSERT INTO `designations` (`id`,`name`,`department_id`,`description`,`status`,`created_at`,`updated_at`) VALUES ('8','System Administrator','2',NULL,'active',NULL,NULL);
INSERT INTO `designations` (`id`,`name`,`department_id`,`description`,`status`,`created_at`,`updated_at`) VALUES ('9','Finance Officer','3',NULL,'active',NULL,NULL);
INSERT INTO `designations` (`id`,`name`,`department_id`,`description`,`status`,`created_at`,`updated_at`) VALUES ('10','Operations Manager','4',NULL,'active',NULL,NULL);

-- ------------------------------------------------------------
-- Table structure for `email_verifications`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `email_verifications`;

CREATE TABLE `email_verifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `token_hash` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email_verifications_user` (`user_id`),
  KEY `idx_email_verifications_token` (`token_hash`),
  CONSTRAINT `fk_ev_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table structure for `login_history`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `login_history`;

CREATE TABLE `login_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `device_type` varchar(30) NOT NULL DEFAULT 'unknown',
  `browser` varchar(100) DEFAULT NULL,
  `platform` varchar(100) DEFAULT NULL,
  `status` enum('success','failed','lockout','logout') NOT NULL DEFAULT 'success',
  `reason` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lh_user` (`user_id`),
  KEY `idx_lh_email` (`email`),
  KEY `idx_lh_status` (`status`),
  KEY `idx_lh_created` (`created_at`),
  CONSTRAINT `fk_lh_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Dumping data for `login_history`
-- ------------------------------------------------------------
INSERT INTO `login_history` (`id`,`user_id`,`email`,`ip_address`,`user_agent`,`device_type`,`browser`,`platform`,`status`,`reason`,`created_at`) VALUES ('1','1','superadmin@example.com','::1','curl/8.21.0','desktop','unknown','unknown','success',NULL,'2026-09-23 10:39:42');

-- ------------------------------------------------------------
-- Table structure for `menus`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `menus`;

CREATE TABLE `menus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `route` varchar(190) DEFAULT NULL,
  `icon` varchar(60) DEFAULT NULL,
  `permission` varchar(120) DEFAULT NULL,
  `module` varchar(60) NOT NULL DEFAULT 'general',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_menus_slug` (`slug`),
  KEY `idx_menus_parent` (`parent_id`),
  KEY `idx_menus_module` (`module`),
  CONSTRAINT `fk_menus_parent` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Dumping data for `menus`
-- ------------------------------------------------------------
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('1',NULL,'Dashboard','dashboard','/dashboard','bi-speedometer2','dashboard.view','dashboard','1','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('2',NULL,'Users','user-management',NULL,'bi-people',NULL,'users','10','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('3',NULL,'Access','access-control',NULL,'bi-shield-lock',NULL,'rbac','20','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('4',NULL,'Audit','audit',NULL,'bi-clipboard-data','audit.view','audit','30','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('5',NULL,'Reports','reports','/reports','bi-file-earmark-bar-graph','reports.view','reports','40','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('6',NULL,'Notifications','notifications','/notifications','bi-bell','notifications.view','notifications','50','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('7',NULL,'Files','files','/files','bi-folder2-open','files.view','files','60','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('8',NULL,'Settings','settings',NULL,'bi-gear',NULL,'settings','70','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('9',NULL,'System','system',NULL,'bi-hdd-network','system.manage','system','80','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('19','2','All Users','users-list','/users',NULL,'users.view','users','1','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('20','2','Departments','departments','/departments',NULL,'departments.view','organization','2','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('21','2','Designations','designations','/designations',NULL,'designations.view','organization','3','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('22','3','Roles','roles','/roles',NULL,'roles.view','rbac','1','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('23','3','Permissions','permissions','/permissions',NULL,'permissions.view','rbac','2','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('24','4','Login History','audit-logins','/audit/logins',NULL,'audit.view','audit','1','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('25','4','Activity Logs','audit-activities','/audit/activities',NULL,'audit.view','audit','2','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('26','4','Security Events','audit-security','/audit/security',NULL,'audit.view','audit','3','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('27','4','Failed Logins','audit-failed','/audit/failed',NULL,'audit.view','audit','4','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('28','8','System Settings','settings-general','/settings',NULL,'settings.view','settings','1','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('29','8','Menu Management','settings-menus','/settings/menus',NULL,'menus.view','settings','2','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('30','9','System Health','system-health','/system/health',NULL,'system.manage','system','1','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('31','9','Database Backups','system-backups','/system/backups',NULL,'system.manage','system','2','active',NULL,NULL);
INSERT INTO `menus` (`id`,`parent_id`,`title`,`slug`,`route`,`icon`,`permission`,`module`,`sort_order`,`status`,`created_at`,`updated_at`) VALUES ('32','9','Application Logs','system-logs','/system/logs',NULL,'system.manage','system','3','active',NULL,NULL);

-- ------------------------------------------------------------
-- Table structure for `migrations`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(190) NOT NULL,
  `applied_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_migrations_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Dumping data for `migrations`
-- ------------------------------------------------------------
INSERT INTO `migrations` (`id`,`name`,`applied_at`) VALUES ('1','0001_create_core_tables.sql','2026-09-23 04:35:33');
INSERT INTO `migrations` (`id`,`name`,`applied_at`) VALUES ('2','0002_create_security_tracking.sql','2026-09-23 04:35:33');
INSERT INTO `migrations` (`id`,`name`,`applied_at`) VALUES ('3','0003_create_settings_and_menus.sql','2026-09-23 04:35:33');
INSERT INTO `migrations` (`id`,`name`,`applied_at`) VALUES ('4','0004_create_notifications_files_api.sql','2026-09-23 04:35:33');

-- ------------------------------------------------------------
-- Table structure for `notifications`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(60) NOT NULL DEFAULT 'info',
  `title` varchar(190) NOT NULL,
  `body` text DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`),
  KEY `idx_notif_read` (`is_read`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table structure for `password_resets`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `password_resets`;

CREATE TABLE `password_resets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(190) NOT NULL,
  `token_hash` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_password_resets_email` (`email`),
  KEY `idx_password_resets_token` (`token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table structure for `permissions`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `permissions`;

CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL DEFAULT 'general',
  `description` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_permissions_slug` (`slug`),
  KEY `idx_permissions_module` (`module`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Dumping data for `permissions`
-- ------------------------------------------------------------
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('1','View Dashboard','dashboard.view','dashboard','Access the dashboard',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('2','View Users','users.view','users','List and view users',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('3','Create Users','users.create','users','Create new users',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('4','Edit Users','users.edit','users','Update user details',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('5','Delete Users','users.delete','users','Delete users',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('6','Activate / Deactivate Users','users.activate','users','Change user account status',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('7','Assign User Roles','users.roles','users','Manage role assignments for users',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('8','View Departments','departments.view','organization','List departments',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('9','Manage Departments','departments.manage','organization','Create/update/delete departments',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('10','View Designations','designations.view','organization','List designations',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('11','Manage Designations','designations.manage','organization','Create/update/delete designations',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('12','View Roles','roles.view','rbac','List roles',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('13','Create Roles','roles.create','rbac','Create new roles',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('14','Edit Roles','roles.edit','rbac','Update roles',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('15','Delete Roles','roles.delete','rbac','Delete roles',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('16','Assign Role Permissions','roles.permissions','rbac','Manage role-permission assignments',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('17','View Permissions','permissions.view','rbac','List permissions',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('18','Create Permissions','permissions.create','rbac','Create new permissions',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('19','Edit Permissions','permissions.edit','rbac','Update permissions',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('20','Delete Permissions','permissions.delete','rbac','Delete permissions',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('21','View Settings','settings.view','settings','View system settings',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('22','Update Settings','settings.update','settings','Update system settings',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('23','View Menus','menus.view','settings','View dynamic menus',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('24','Manage Menus','menus.manage','settings','Create/update/delete menus',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('25','View Audit','audit.view','audit','View login history, activities and security events',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('26','View Reports','reports.view','reports','Export reports',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('27','View Notifications','notifications.view','notifications','View notifications',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('28','Manage Notifications','notifications.manage','notifications','Send/manage notifications',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('29','View Files','files.view','files','View and download uploads',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('30','Upload Files','files.upload','files','Upload files',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('31','Delete Files','files.delete','files','Delete uploads',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('32','Manage System','system.manage','system','Health, backups and logs',NULL);
INSERT INTO `permissions` (`id`,`name`,`slug`,`module`,`description`,`created_at`) VALUES ('33','Manage API','api.manage','api','Manage API tokens',NULL);

-- ------------------------------------------------------------
-- Table structure for `role_permission`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `role_permission`;

CREATE TABLE `role_permission` (
  `role_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `idx_rp_permission` (`permission_id`),
  CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Dumping data for `role_permission`
-- ------------------------------------------------------------
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','1');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','2');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','3');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','4');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','5');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','6');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','7');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','8');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','9');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','10');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','11');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','12');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','13');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','14');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','15');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','16');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','17');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','18');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','19');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','21');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','22');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','23');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','24');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','25');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','26');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','27');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','28');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','29');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','30');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','31');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('2','33');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('3','1');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('3','2');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('3','4');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('3','6');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('3','8');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('3','10');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('3','12');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('3','16');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('3','17');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('3','21');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('3','25');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('3','26');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('3','27');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('3','29');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('3','30');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('4','1');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('4','27');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('4','29');
INSERT INTO `role_permission` (`role_id`,`permission_id`) VALUES ('4','30');

-- ------------------------------------------------------------
-- Table structure for `roles`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Dumping data for `roles`
-- ------------------------------------------------------------
INSERT INTO `roles` (`id`,`name`,`slug`,`description`,`is_system`,`status`,`created_at`,`updated_at`) VALUES ('1','Super Administrator','super-admin','Unrestricted access. System role.','1','active',NULL,NULL);
INSERT INTO `roles` (`id`,`name`,`slug`,`description`,`is_system`,`status`,`created_at`,`updated_at`) VALUES ('2','Administrator','administrator','Full management access except system tooling.','0','active',NULL,NULL);
INSERT INTO `roles` (`id`,`name`,`slug`,`description`,`is_system`,`status`,`created_at`,`updated_at`) VALUES ('3','Manager','manager','Operational management permissions.','0','active',NULL,NULL);
INSERT INTO `roles` (`id`,`name`,`slug`,`description`,`is_system`,`status`,`created_at`,`updated_at`) VALUES ('4','Staff','staff','Basic day-to-day user.','0','active',NULL,NULL);
INSERT INTO `roles` (`id`,`name`,`slug`,`description`,`is_system`,`status`,`created_at`,`updated_at`) VALUES ('9','Editor','editor','Editors','0','active','2026-09-23 04:40:07','2026-09-23 04:40:07');

-- ------------------------------------------------------------
-- Table structure for `security_events`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `security_events`;

CREATE TABLE `security_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `event_type` varchar(100) NOT NULL,
  `severity` enum('info','warning','critical') NOT NULL DEFAULT 'info',
  `message` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `context` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_se_event` (`event_type`),
  KEY `idx_se_severity` (`severity`),
  KEY `idx_se_created` (`created_at`),
  KEY `fk_se_user` (`user_id`),
  CONSTRAINT `fk_se_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table structure for `settings`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(120) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','int','bool','json','array') NOT NULL DEFAULT 'string',
  `group_name` varchar(60) NOT NULL DEFAULT 'general',
  `is_encrypted` tinyint(1) NOT NULL DEFAULT 0,
  `autoload` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`setting_key`),
  KEY `idx_settings_group` (`group_name`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Dumping data for `settings`
-- ------------------------------------------------------------
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('1','app.name','Skeleton App','string','app','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('2','app.logo_text','Skeleton','string','app','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('3','organization.name','Acme Corporation','string','organization','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('4','organization.address','123 Main Street','string','organization','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('5','organization.phone','+880-000-0000000','string','organization','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('6','organization.email','info@acme.test','string','organization','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('7','organization.website','https://acme.test','string','organization','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('8','general.timezone','Asia/Dhaka','string','general','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('9','general.date_format','d-m-Y','string','general','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('10','general.datetime_format','d-m-Y H:i:s','string','general','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('11','general.records_per_page','20','int','general','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('12','security.min_password_length','8','int','security','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('13','security.max_failed_attempts','5','int','security','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('14','security.lockout_minutes','15','int','security','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('15','security.session_timeout','30','int','security','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('16','security.password_expiry_days','0','int','security','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('17','security.require_verification','1','bool','security','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('18','security.register_enabled','1','bool','security','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('19','security.remember_me','1','bool','security','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('20','audit.log_activities','1','bool','audit','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('21','audit.track_page_views','0','bool','audit','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('22','audit.log_security_events','1','bool','audit','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('23','smtp.enabled','0','bool','smtp','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('24','smtp.host','smtp.mailtrap.io','string','smtp','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('25','smtp.port','2525','int','smtp','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('26','smtp.username','','string','smtp','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('27','smtp.password','','string','smtp','1','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('28','smtp.encryption','tls','string','smtp','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('29','smtp.from_email','noreply@acme.test','string','smtp','0','1',NULL);
INSERT INTO `settings` (`id`,`setting_key`,`setting_value`,`setting_type`,`group_name`,`is_encrypted`,`autoload`,`updated_at`) VALUES ('30','smtp.from_name','Acme Corporation','string','smtp','0','1',NULL);

-- ------------------------------------------------------------
-- Table structure for `uploads`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `uploads`;

CREATE TABLE `uploads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `path` varchar(500) NOT NULL,
  `mime_type` varchar(120) NOT NULL DEFAULT 'application/octet-stream',
  `extension` varchar(20) DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL DEFAULT 0,
  `disk` enum('local') NOT NULL DEFAULT 'local',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_uploads_user` (`user_id`),
  CONSTRAINT `fk_uploads_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table structure for `user_role`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `user_role`;

CREATE TABLE `user_role` (
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `idx_ur_role` (`role_id`),
  CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Dumping data for `user_role`
-- ------------------------------------------------------------
INSERT INTO `user_role` (`user_id`,`role_id`) VALUES ('1','1');

-- ------------------------------------------------------------
-- Table structure for `users`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'pending',
  `email_verified_at` datetime DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `failed_attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `password_changed_at` datetime DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `designation_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_status` (`status`),
  KEY `idx_users_department` (`department_id`),
  KEY `idx_users_designation` (`designation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Dumping data for `users`
-- ------------------------------------------------------------
INSERT INTO `users` (`id`,`name`,`email`,`password`,`status`,`email_verified_at`,`remember_token`,`last_login_at`,`last_login_ip`,`failed_attempts`,`locked_until`,`password_changed_at`,`avatar`,`department_id`,`designation_id`,`created_by`,`created_at`,`updated_at`) VALUES ('1','Super Admin','superadmin@example.com','$2y$10$.dm2QIR0nfHO9wCt0J8wg.hM3.PvasQArZpNf5On5FgMxjfNfKd02','active','2026-09-23 10:35:46',NULL,'2026-09-23 04:39:42','::1','0',NULL,'2026-09-23 10:35:46',NULL,'0','0',NULL,'2026-09-23 10:35:46','2026-09-23 04:41:58');
INSERT INTO `users` (`id`,`name`,`email`,`password`,`status`,`email_verified_at`,`remember_token`,`last_login_at`,`last_login_ip`,`failed_attempts`,`locked_until`,`password_changed_at`,`avatar`,`department_id`,`designation_id`,`created_by`,`created_at`,`updated_at`) VALUES ('3','Jane Doe','jane@example.com','$argon2id$v=19$m=65536,t=4,p=2$NmhvWUZtQ081di9pcjZsaw$tqbIo/RLXaBfVG90kkQV2wqjwpbgiMzJhc0xe9lgAto','active','2026-09-23 04:40:07',NULL,NULL,NULL,'0',NULL,'2026-09-23 04:40:07',NULL,'0','0','1','2026-09-23 04:40:07','2026-09-23 04:40:07');
INSERT INTO `users` (`id`,`name`,`email`,`password`,`status`,`email_verified_at`,`remember_token`,`last_login_at`,`last_login_ip`,`failed_attempts`,`locked_until`,`password_changed_at`,`avatar`,`department_id`,`designation_id`,`created_by`,`created_at`,`updated_at`) VALUES ('4','Jane Doe','jane2@example.com','$argon2id$v=19$m=65536,t=4,p=2$eHI1UkpwblA5blp5WEtWaQ$6Jk0XX2NMoZpgZ6eyI4i6WbpAf2VErsqHATQAr4ci78','active','2026-09-23 04:41:08',NULL,NULL,NULL,'0',NULL,'2026-09-23 04:41:08',NULL,'0','0','1','2026-09-23 04:41:08','2026-09-23 04:41:08');
INSERT INTO `users` (`id`,`name`,`email`,`password`,`status`,`email_verified_at`,`remember_token`,`last_login_at`,`last_login_ip`,`failed_attempts`,`locked_until`,`password_changed_at`,`avatar`,`department_id`,`designation_id`,`created_by`,`created_at`,`updated_at`) VALUES ('5','Temp User','temp@example.com','$argon2id$v=19$m=65536,t=4,p=2$TW9xNlBKNE5vZ1cwbmhrNg$xrptkctyn9siFGjW/LnxXToCa2lHSIi8pjoASAqEDus','active','2026-09-23 04:41:58',NULL,NULL,NULL,'0',NULL,'2026-09-23 04:41:58',NULL,'0','0','1','2026-09-23 04:41:58','2026-09-23 04:41:58');

SET FOREIGN_KEY_CHECKS=1;