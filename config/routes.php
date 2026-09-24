<?php

/**
 * Route definitions.
 *
 * Format:
 *   [HTTP_METHOD, 'pattern', 'ControllerClass@method', ['middleware'|'middleware:param', ...]]
 */

return [

    // ---- Public ------------------------------------------------------------------
    ['GET', '/', 'HomeController@index', []],
    ['GET', '/home', 'HomeController@home', ['auth']],

    ['GET', '/login', 'AuthController@showLogin', ['guest']],
    ['POST', '/login', 'AuthController@login', ['guest', 'csrf']],
    ['GET', '/register', 'AuthController@showRegister', ['guest']],
    ['POST', '/register', 'AuthController@register', ['guest', 'csrf']],
    ['GET', '/verify-email/{token}', 'AuthController@verifyEmail', []],
    ['POST', '/verify-email/resend', 'AuthController@resendVerification', ['csrf']],
    ['GET', '/forgot-password', 'AuthController@showForgot', ['guest']],
    ['POST', '/forgot-password', 'AuthController@forgot', ['guest', 'csrf']],
    ['GET', '/reset-password/{token}', 'AuthController@showReset', ['guest']],
    ['POST', '/reset-password', 'AuthController@reset', ['guest', 'csrf']],
    ['POST', '/logout', 'AuthController@logout', ['auth', 'csrf']],

    // ---- Dashboard ----------------------------------------------------------------
    ['GET', '/dashboard', 'DashboardController@index', ['auth', 'permission:dashboard.view']],

    // ---- Profile ------------------------------------------------------------------
    ['GET', '/profile', 'ProfileController@show', ['auth']],
    ['POST', '/profile', 'ProfileController@update', ['auth', 'csrf']],
    ['POST', '/profile/password', 'ProfileController@changePassword', ['auth', 'csrf']],

    // ---- User management ----------------------------------------------------------
    ['GET', '/users', 'UserController@index', ['auth', 'permission:users.view']],
    ['GET', '/users/create', 'UserController@create', ['auth', 'permission:users.create']],
    ['POST', '/users', 'UserController@store', ['auth', 'permission:users.create', 'csrf']],
    ['GET', '/users/{id}', 'UserController@show', ['auth', 'permission:users.view']],
    ['GET', '/users/{id}/edit', 'UserController@edit', ['auth', 'permission:users.edit']],
    ['PUT', '/users/{id}', 'UserController@update', ['auth', 'permission:users.edit', 'csrf']],
    ['DELETE', '/users/{id}', 'UserController@destroy', ['auth', 'permission:users.delete', 'csrf']],
    ['POST', '/users/{id}/toggle-status', 'UserController@toggleStatus', ['auth', 'permission:users.activate', 'csrf']],
    ['POST', '/users/{id}/roles', 'UserController@assignRoles', ['auth', 'permission:users.roles', 'csrf']],

    // ---- Departments ---------------------------------------------------------------
    ['GET', '/departments', 'DepartmentsController@index', ['auth', 'permission:departments.view']],
    ['GET', '/departments/create', 'DepartmentsController@create', ['auth', 'permission:departments.manage']],
    ['POST', '/departments', 'DepartmentsController@store', ['auth', 'permission:departments.manage', 'csrf']],
    ['GET', '/departments/{id}/edit', 'DepartmentsController@edit', ['auth', 'permission:departments.manage']],
    ['PUT', '/departments/{id}', 'DepartmentsController@update', ['auth', 'permission:departments.manage', 'csrf']],
    ['DELETE', '/departments/{id}', 'DepartmentsController@destroy', ['auth', 'permission:departments.manage', 'csrf']],

    // ---- Designations --------------------------------------------------------------
    ['GET', '/designations', 'DesignationsController@index', ['auth', 'permission:designations.view']],
    ['GET', '/designations/create', 'DesignationsController@create', ['auth', 'permission:designations.manage']],
    ['POST', '/designations', 'DesignationsController@store', ['auth', 'permission:designations.manage', 'csrf']],
    ['GET', '/designations/{id}/edit', 'DesignationsController@edit', ['auth', 'permission:designations.manage']],
    ['PUT', '/designations/{id}', 'DesignationsController@update', ['auth', 'permission:designations.manage', 'csrf']],
    ['DELETE', '/designations/{id}', 'DesignationsController@destroy', ['auth', 'permission:designations.manage', 'csrf']],

    // ---- Roles ---------------------------------------------------------------------
    ['GET', '/roles', 'RoleController@index', ['auth', 'permission:roles.view']],
    ['GET', '/roles/create', 'RoleController@create', ['auth', 'permission:roles.create']],
    ['POST', '/roles', 'RoleController@store', ['auth', 'permission:roles.create', 'csrf']],
    ['GET', '/roles/{id}/edit', 'RoleController@edit', ['auth', 'permission:roles.edit']],
    ['PUT', '/roles/{id}', 'RoleController@update', ['auth', 'permission:roles.edit', 'csrf']],
    ['DELETE', '/roles/{id}', 'RoleController@destroy', ['auth', 'permission:roles.delete', 'csrf']],
    ['GET', '/roles/{id}/permissions', 'RoleController@permissions', ['auth', 'permission:roles.permissions']],
    ['POST', '/roles/{id}/permissions', 'RoleController@savePermissions', ['auth', 'permission:roles.permissions', 'csrf']],

    // ---- Permissions ---------------------------------------------------------------
    ['GET', '/permissions', 'PermissionController@index', ['auth', 'permission:permissions.view']],
    ['GET', '/permissions/create', 'PermissionController@create', ['auth', 'permission:permissions.create']],
    ['POST', '/permissions', 'PermissionController@store', ['auth', 'permission:permissions.create', 'csrf']],
    ['GET', '/permissions/{id}/edit', 'PermissionController@edit', ['auth', 'permission:permissions.edit']],
    ['PUT', '/permissions/{id}', 'PermissionController@update', ['auth', 'permission:permissions.edit', 'csrf']],
    ['DELETE', '/permissions/{id}', 'PermissionController@destroy', ['auth', 'permission:permissions.delete', 'csrf']],

    // ---- Settings -------------------------------------------------------------------
    ['GET', '/settings', 'SettingsController@index', ['auth', 'permission:settings.view']],
    ['POST', '/settings/application', 'SettingsController@updateApplication', ['auth', 'permission:settings.update', 'csrf']],
    ['POST', '/settings/organization', 'SettingsController@updateOrganization', ['auth', 'permission:settings.update', 'csrf']],
    ['POST', '/settings/general', 'SettingsController@updateGeneral', ['auth', 'permission:settings.update', 'csrf']],
    ['POST', '/settings/smtp', 'SettingsController@updateSmtp', ['auth', 'permission:settings.update', 'csrf']],
    ['POST', '/settings/security', 'SettingsController@updateSecurity', ['auth', 'permission:settings.update', 'csrf']],
    ['POST', '/settings/audit', 'SettingsController@updateAudit', ['auth', 'permission:settings.update', 'csrf']],
    ['POST', '/settings/test-mail', 'SettingsController@testMail', ['auth', 'permission:settings.update', 'csrf']],

    // ---- Dynamic menu management ----------------------------------------------------
    ['GET', '/settings/menus', 'SettingsController@menus', ['auth', 'permission:menus.view']],
    ['GET', '/settings/menus/create', 'SettingsController@createMenu', ['auth', 'permission:menus.manage']],
    ['POST', '/settings/menus', 'SettingsController@storeMenu', ['auth', 'permission:menus.manage', 'csrf']],
    ['GET', '/settings/menus/{id}/edit', 'SettingsController@editMenu', ['auth', 'permission:menus.manage']],
    ['PUT', '/settings/menus/{id}', 'SettingsController@updateMenu', ['auth', 'permission:menus.manage', 'csrf']],
    ['DELETE', '/settings/menus/{id}', 'SettingsController@destroyMenu', ['auth', 'permission:menus.manage', 'csrf']],

    // ---- Audit & tracking -----------------------------------------------------------
    ['GET', '/audit/logins', 'AuditController@logins', ['auth', 'permission:audit.view']],
    ['GET', '/audit/activities', 'AuditController@activities', ['auth', 'permission:audit.view']],
    ['GET', '/audit/security', 'AuditController@security', ['auth', 'permission:audit.view']],
    ['GET', '/audit/failed', 'AuditController@failedLogins', ['auth', 'permission:audit.view']],
    ['GET', '/audit/work/{id}', 'AuditController@work', ['auth', 'permission:audit.view']],

    // ---- Reports -----------------------------------------------------------------------
    ['GET', '/reports', 'ReportController@index', ['auth', 'permission:reports.view']],
    ['GET', '/reports/export/users', 'ReportController@exportUsers', ['auth', 'permission:reports.view']],
    ['GET', '/reports/export/logins', 'ReportController@exportLogins', ['auth', 'permission:reports.view']],
    ['GET', '/reports/export/activities', 'ReportController@exportActivities', ['auth', 'permission:reports.view']],

    // ---- Notifications ----------------------------------------------------------------
    ['GET', '/notifications', 'NotificationController@index', ['auth', 'permission:notifications.view']],
    ['POST', '/notifications/{id}/read', 'NotificationController@markRead', ['auth', 'csrf']],
    ['POST', '/notifications/read-all', 'NotificationController@markAllRead', ['auth', 'csrf']],

    // ---- File uploads ------------------------------------------------------------------
    ['GET', '/files', 'UploadController@index', ['auth', 'permission:files.view']],
    ['POST', '/files/upload', 'UploadController@upload', ['auth', 'permission:files.upload', 'csrf']],
    ['GET', '/files/{id}/download', 'UploadController@download', ['auth', 'permission:files.view']],
    ['DELETE', '/files/{id}', 'UploadController@destroy', ['auth', 'permission:files.delete', 'csrf']],

    // ---- System health / backups -------------------------------------------------------
    ['GET', '/system/health', 'SystemController@health', ['auth', 'permission:system.manage']],
    ['GET', '/system/backups', 'SystemController@backups', ['auth', 'permission:system.manage']],
    ['POST', '/system/backups', 'SystemController@createBackup', ['auth', 'permission:system.manage', 'csrf']],
    ['GET', '/system/backups/{file}/download', 'SystemController@downloadBackup', ['auth', 'permission:system.manage']],
    ['DELETE', '/system/backups/{file}', 'SystemController@destroyBackup', ['auth', 'permission:system.manage', 'csrf']],
    ['GET', '/system/logs', 'SystemController@logs', ['auth', 'permission:system.manage']],

    // ---- API foundation (token auth) ----------------------------------------------------
    ['POST', '/api/v1/auth/login', 'Api\AuthController@login', []],
    ['GET', '/api/v1/me', 'Api\UserController@me', ['api']],
    ['GET', '/api/v1/stats', 'Api\StatsController@index', ['api']],
];