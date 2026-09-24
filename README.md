# PHP Skeleton App

A modular PHP 8.2+ / MySQL / Bootstrap 5 admin starter kit with authentication, RBAC, audit logging, settings management, notifications, file uploads, reports, and database backups. No framework — small custom core (`app/Core`), PSR-4 autoloading via Composer, and a console CLI.

## Features

- **Authentication** — register, login, password reset, email verification stubs, login throttling, remember-me.
- **RBAC** — roles, permissions, per-role permission assignment, role-level menu control.
- **User management** — CRUD, activate/deactivate, assign roles, departments & designations.
- **Audit** — login history, activity logs, security events, failed logins, per-user work log.
- **Settings** — application, organization, general, security, SMTP, and audit preferences (encrypted secrets).
- **Dynamic menus** — hierarchical sidebar menus stored in DB, filtered by permission.
- **Notifications** — in-app notifications with read/unread state.
- **File manager** — upload (10 MB limit, whitelisted types), download, delete.
- **Reports** — CSV export.
- **System** — health check, database backups (create/download/delete), application logs viewer.
- **Security** — CSRF middleware, hashed passwords, prepared statements, rate-limited auth, standalone error pages.

## Requirements

- PHP 8.2+ (ext-pdo, ext-pdo_mysql, ext-mbstring, ext-openssl, ext-json, ext-fileinfo, ext-zlib recommended)
- MySQL 5.7+ / MariaDB 10.3+
- Composer (only used for the PSR-4 autoloader)
- Apache with `mod_rewrite` (or PHP built-in server)

## Quick start

```bash
composer dump-autoload
cp .env.example .env          # then set DB creds + APP_URL + APP_KEY
php console key:generate      # writes APP_KEY into .env
php console migrate           # apply database migrations
php console seed              # permissions, roles, menus, settings
php console admin:create --email=admin@example.com --password='YourPass@123'
```

Default admin from the seed:

```
Email:    superadmin@example.com
Password: Password@123
```

> Change it immediately after first login via My Profile.

## Web server

Under Apache in the XAMPP htdocs directory (`http://localhost/php_Skeleton/`), `.htaccess` rewrites everything to `public/index.php`; set `APP_URL` to the base URL. One file `public/index.php` bootstraps the app and delegates to `public/` paths for assets.

With PHP's built-in server (no Apache needed):

```bash
php -S 127.0.0.1:8099 -t public
```

## Console commands

```
migrate              Run pending migrations
seed                 Insert seed data (permissions, roles, menus, settings)
fresh                Drop all tables, migrate, then seed
admin:create         Create a super administrator  (--email= --password= --name=)
backup               Create a database backup in storage/backups
key:generate         Write a fresh APP_KEY into the .env file
```

## Project layout

```
public/              Web root (index.php, .htaccess, assets/)
app/
  Console/           CLI commands + seeders
  Core/              Router, Request, Response, View, Model, Validator, Session, etc.
  Controllers/       HTTP controllers
  Middleware/        auth, guest, csrf, permission
  Models/            Active-record-ish models
  Repositories/      Data access
  Services/          Business logic (Auth, Settings, Audit, Notifications, Permissions, ...)
  Views/             Templates (layouts, partials, modules)
config/              config.php, routes.php
database/            migrations/*.sql, seeds/seed.sql
storage/             uploads/, backups/, logs/
```

## Tests

No test framework is wired up yet; `composer test` is not defined. The app has been smoke-tested end-to-end against XAMPP Apache.

## License

MIT (adjust to your liking).