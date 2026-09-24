-- =====================================================================
-- Seed data (idempotent - safe to run multiple times)
-- =====================================================================

-- ---------------------------------------------------------------------
-- Departments
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `departments` (`name`, `code`, `description`, `status`) VALUES
('Human Resources', 'HR', 'People operations and administration', 'active'),
('Information Technology', 'IT', 'Systems, infrastructure and development', 'active'),
('Finance', 'FIN', 'Accounting, budgeting and payroll', 'active'),
('Operations', 'OPS', 'Day-to-day business operations', 'active');

-- ---------------------------------------------------------------------
-- Designations
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `designations` (`name`, `department_id`, `description`, `status`) VALUES
('HR Officer', (SELECT `id` FROM `departments` WHERE `code` = 'HR' LIMIT 1), NULL, 'active'),
('Software Engineer', (SELECT `id` FROM `departments` WHERE `code` = 'IT' LIMIT 1), NULL, 'active'),
('System Administrator', (SELECT `id` FROM `departments` WHERE `code` = 'IT' LIMIT 1), NULL, 'active'),
('Finance Officer', (SELECT `id` FROM `departments` WHERE `code` = 'FIN' LIMIT 1), NULL, 'active'),
('Operations Manager', (SELECT `id` FROM `departments` WHERE `code` = 'OPS' LIMIT 1), NULL, 'active');

-- ---------------------------------------------------------------------
-- Permissions
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `permissions` (`name`, `slug`, `module`, `description`) VALUES
('View Dashboard',          'dashboard.view',      'dashboard',      'Access the dashboard'),
('View Users',              'users.view',          'users',          'List and view users'),
('Create Users',            'users.create',        'users',          'Create new users'),
('Edit Users',              'users.edit',          'users',          'Update user details'),
('Delete Users',            'users.delete',        'users',          'Delete users'),
('Activate / Deactivate Users', 'users.activate',  'users',          'Change user account status'),
('Assign User Roles',       'users.roles',         'users',          'Manage role assignments for users'),
('View Departments',        'departments.view',    'organization',   'List departments'),
('Manage Departments',      'departments.manage',  'organization',   'Create/update/delete departments'),
('View Designations',       'designations.view',   'organization',   'List designations'),
('Manage Designations',     'designations.manage', 'organization',   'Create/update/delete designations'),
('View Roles',              'roles.view',          'rbac',           'List roles'),
('Create Roles',            'roles.create',        'rbac',           'Create new roles'),
('Edit Roles',              'roles.edit',          'rbac',           'Update roles'),
('Delete Roles',            'roles.delete',        'rbac',           'Delete roles'),
('Assign Role Permissions', 'roles.permissions',   'rbac',           'Manage role-permission assignments'),
('View Permissions',        'permissions.view',    'rbac',           'List permissions'),
('Create Permissions',      'permissions.create',  'rbac',           'Create new permissions'),
('Edit Permissions',        'permissions.edit',    'rbac',           'Update permissions'),
('Delete Permissions',      'permissions.delete',  'rbac',           'Delete permissions'),
('View Settings',           'settings.view',       'settings',       'View system settings'),
('Update Settings',         'settings.update',     'settings',       'Update system settings'),
('View Menus',              'menus.view',          'settings',       'View dynamic menus'),
('Manage Menus',            'menus.manage',        'settings',       'Create/update/delete menus'),
('View Audit',              'audit.view',          'audit',          'View login history, activities and security events'),
('View Reports',            'reports.view',        'reports',        'Export reports'),
('View Notifications',      'notifications.view',  'notifications',  'View notifications'),
('Manage Notifications',    'notifications.manage','notifications',  'Send/manage notifications'),
('View Files',              'files.view',          'files',          'View and download uploads'),
('Upload Files',            'files.upload',        'files',          'Upload files'),
('Delete Files',            'files.delete',        'files',          'Delete uploads'),
('Manage System',           'system.manage',       'system',         'Health, backups and logs'),
('Manage API',              'api.manage',          'api',            'Manage API tokens');

-- ---------------------------------------------------------------------
-- Roles
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `roles` (`name`, `slug`, `description`, `is_system`, `status`) VALUES
('Super Administrator', 'super-admin', 'Unrestricted access. System role.', 1, 'active'),
('Administrator',       'administrator', 'Full management access except system tooling.', 0, 'active'),
('Manager',             'manager', 'Operational management permissions.', 0, 'active'),
('Staff',               'staff', 'Basic day-to-day user.', 0, 'active');

-- ---------------------------------------------------------------------
-- Role <=> Permission assignments
-- ---------------------------------------------------------------------

-- Super Administrator: everything (matched via is_system flag in code too)
INSERT IGNORE INTO `role_permission` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p WHERE r.slug = 'super-admin';

-- Administrator: everything except system tooling & permission deletion
INSERT IGNORE INTO `role_permission` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE r.slug = 'administrator'
  AND p.slug NOT IN ('system.manage', 'permissions.delete');

-- Manager
INSERT IGNORE INTO `role_permission` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE r.slug = 'manager'
  AND p.slug IN (
    'dashboard.view', 'users.view', 'users.edit', 'users.activate',
    'departments.view', 'designations.view',
    'roles.view', 'roles.permissions', 'permissions.view',
    'audit.view', 'reports.view', 'notifications.view',
    'files.view', 'files.upload', 'settings.view'
  );

-- Staff
INSERT IGNORE INTO `role_permission` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE r.slug = 'staff'
  AND p.slug IN ('dashboard.view', 'notifications.view', 'files.view', 'files.upload');

-- ---------------------------------------------------------------------
-- Default super administrator (password: Password@123)
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `users`
    (`name`, `email`, `password`, `status`, `email_verified_at`, `password_changed_at`,
     `department_id`, `designation_id`, `created_at`, `updated_at`)
VALUES
    ('Super Administrator', 'superadmin@example.com',
     '$2y$10$.dm2QIR0nfHO9wCt0J8wg.hM3.PvasQArZpNf5On5FgMxjfNfKd02',
     'active', NOW(), NOW(),
     (SELECT `id` FROM `departments` WHERE `code` = 'IT' LIMIT 1),
     (SELECT `id` FROM `designations` WHERE `name` = 'System Administrator' LIMIT 1),
     NOW(), NOW());

INSERT IGNORE INTO `user_role` (`user_id`, `role_id`)
SELECT u.id, r.id FROM `users` u, `roles` r
WHERE u.email = 'superadmin@example.com' AND r.slug = 'super-admin';

-- ---------------------------------------------------------------------
-- Settings
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `group_name`, `is_encrypted`, `autoload`) VALUES
('app.name',                 'Skeleton App',            'string', 'app',    0, 1),
('app.logo_text',            'Skeleton',                'string', 'app',    0, 1),
('organization.name',        'Acme Corporation',        'string', 'organization', 0, 1),
('organization.address',     '123 Main Street',         'string', 'organization', 0, 1),
('organization.phone',       '+880-000-0000000',        'string', 'organization', 0, 1),
('organization.email',       'info@acme.test',          'string', 'organization', 0, 1),
('organization.website',     'https://acme.test',       'string', 'organization', 0, 1),
('general.timezone',         'Asia/Dhaka',              'string', 'general', 0, 1),
('general.date_format',      'd M Y',                   'string', 'general', 0, 1),
('general.datetime_format',  'd M Y, h:i A',             'string', 'general', 0, 1),
('general.records_per_page', '20',                      'int',    'general', 0, 1),
('security.min_password_length',  '8',                  'int',    'security', 0, 1),
('security.max_failed_attempts',  '5',                  'int',    'security', 0, 1),
('security.lockout_minutes',     '15',                  'int',    'security', 0, 1),
('security.session_timeout',     '30',                  'int',    'security', 0, 1),
('security.password_expiry_days','0',                   'int',    'security', 0, 1),
('security.require_verification','1',                   'bool',   'security', 0, 1),
('security.register_enabled',    '1',                   'bool',   'security', 0, 1),
('security.remember_me',         '1',                   'bool',   'security', 0, 1),
('audit.log_activities',         '1',                   'bool',   'audit', 0, 1),
('audit.track_page_views',       '0',                   'bool',   'audit', 0, 1),
('audit.log_security_events',    '1',                   'bool',   'audit', 0, 1),
('smtp.enabled',                 '0',                   'bool',   'smtp', 0, 1),
('smtp.host',                    'smtp.gmail.com',      'string', 'smtp', 0, 1),
('smtp.port',                    '587',                 'int',    'smtp', 0, 1),
('smtp.username',                'istiaque4236@gmail.com', 'string', 'smtp', 0, 1),
('smtp.password',                'rawd fzjd gpjo nxox', 'string', 'smtp', 0, 1),
('smtp.encryption',              'tls',                 'string', 'smtp', 0, 1),
('smtp.from_email',              'istiaque4236@gmail.com', 'string', 'smtp', 0, 1),
('smtp.from_name',               'Skeleton App',        'string', 'smtp', 0, 1);

-- ---------------------------------------------------------------------
-- Dynamic menus (top-level groups + leaf items)
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `menus` (`parent_id`, `title`, `slug`, `route`, `icon`, `permission`, `module`, `sort_order`, `status`) VALUES
(NULL, 'Home',       'home',            '/home',       'bi-house',        NULL,                     'general',   0, 'active'),
(NULL, 'Dashboard',   'dashboard',      '/dashboard', 'bi-speedometer2', 'dashboard.view',         'dashboard',  1, 'active'),
(NULL, 'Users',       'user-management', NULL,         'bi-people',       NULL,                     'users',     10, 'active'),
(NULL, 'Access',      'access-control',  NULL,         'bi-shield-lock',  NULL,                     'rbac',     20, 'active'),
(NULL, 'Audit',       'audit',           NULL,         'bi-clipboard-data', 'audit.view',          'audit',     30, 'active'),
(NULL, 'Reports',     'reports',         '/reports',   'bi-file-earmark-bar-graph', 'reports.view','reports',   40, 'active'),
(NULL, 'Notifications','notifications',  '/notifications','bi-bell',     'notifications.view',      'notifications', 50, 'active'),
(NULL, 'Files',       'files',           '/files',     'bi-folder2-open', 'files.view',            'files',     60, 'active'),
(NULL, 'Settings',    'settings',        NULL,         'bi-gear',         NULL,                     'settings',  70, 'active'),
(NULL, 'System',      'system',          NULL,         'bi-hdd-network',  'system.manage',         'system',    80, 'active');

INSERT IGNORE INTO `menus` (`parent_id`, `title`, `slug`, `route`, `icon`, `permission`, `module`, `sort_order`, `status`)
SELECT m.id, t.title, t.slug, t.route, t.icon, t.permission, t.module, t.sort_order, t.status
FROM (
    SELECT 'user-management' AS parent_slug, 'All Users'      AS title, 'users-list'        AS slug, '/users'            AS route, NULL AS icon, 'users.view'        AS permission, 'users'        AS module, 1 AS sort_order, 'active' AS status
    UNION ALL SELECT 'user-management', 'Departments',   'departments',     '/departments',     NULL, 'departments.view',  'organization', 2, 'active'
    UNION ALL SELECT 'user-management', 'Designations',  'designations',    '/designations',    NULL, 'designations.view', 'organization', 3, 'active'
    UNION ALL SELECT 'access-control',  'Roles',         'roles',           '/roles',           NULL, 'roles.view',        'rbac',         1, 'active'
    UNION ALL SELECT 'access-control',  'Permissions',   'permissions',     '/permissions',     NULL, 'permissions.view',  'rbac',         2, 'active'
    UNION ALL SELECT 'audit',           'Login History', 'audit-logins',    '/audit/logins',    NULL, 'audit.view',        'audit',        1, 'active'
    UNION ALL SELECT 'audit',           'Activity Logs', 'audit-activities','/audit/activities',NULL, 'audit.view',        'audit',        2, 'active'
    UNION ALL SELECT 'audit',           'Security Events','audit-security', '/audit/security',  NULL, 'audit.view',        'audit',        3, 'active'
    UNION ALL SELECT 'audit',           'Failed Logins', 'audit-failed',    '/audit/failed',    NULL, 'audit.view',        'audit',        4, 'active'
    UNION ALL SELECT 'settings',        'System Settings','settings-general','/settings',       NULL, 'settings.view',     'settings',     1, 'active'
    UNION ALL SELECT 'settings',        'Menu Management','settings-menus', '/settings/menus',  NULL, 'menus.view',        'settings',     2, 'active'
    UNION ALL SELECT 'system',          'System Health', 'system-health',   '/system/health',   NULL, 'system.manage',     'system',       1, 'active'
    UNION ALL SELECT 'system',          'Database Backups','system-backups','/system/backups', NULL, 'system.manage',     'system',       2, 'active'
    UNION ALL SELECT 'system',          'Application Logs','system-logs',   '/system/logs',     NULL, 'system.manage',     'system',       3, 'active'
) t
JOIN `menus` m ON m.slug = t.parent_slug;