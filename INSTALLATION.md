# Installation Guide

Step-by-step setup for local development (XAMPP on Windows) and a generic LAMP/LNMP stack.

## 1. Requirements

- **PHP 8.2+** with extensions: `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `json`, `fileinfo`, `ctype`, `session`.
  - On XAMPP it ships with PHP; use the bundled `php.exe`, e.g. `E:\xampp\php\php.exe`.
- **MySQL 5.7+ / MariaDB** running (XAMPP: start Apache and MySQL from the control panel).
- **Composer** — only used to generate the PSR-4 autoloader. Get it from getcomposer.org.
- **Apache** with `mod_rewrite` enabled and `AllowOverride All` for the htdocs directory.

## 2. Copy the project

Place the project folder inside your web root, e.g.:

```
E:\xampp\htdocs\php_Skeleton
```

It should stay at the *top level* of `htdocs` for the `.htaccess` rewrite base in this layout.

## 3. Generate the autoloader

From the project root:

```bash
composer dump-autoload
```

If Composer isn't on PATH:

```bash
php "C:\path\to\composer.phar" dump-autoload
```

This creates `vendor/autoload.php` (the app will also fall back to a built-in minimal autoloader if Composer is unavailable).

## 4. Configure environment

```bash
copy .env.example .env        # Windows
# or: cp .env.example .env    # Linux/macOS
```

Edit `.env`:

```
APP_NAME="Skeleton App"
APP_URL=http://localhost/php_Skeleton
APP_KEY=
APP_ENV=local
APP_DEBUG=true

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=skeleton_app
DB_USER=root
DB_PASSWORD=
```

- `APP_URL` must be the public base URL (no trailing slash). For a different folder/domain adjust both the `.env` and `.htaccess` `RewriteBase`.
- `DB_PASSWORD` stays empty when XAMPP's root user has no password.
- `APP_ENV=local` + `APP_DEBUG=true` show detailed error screens during development; flip both for production.
- `APP_KEY` is written automatically by `key:generate` (below). Keep it secret in production.

## 5. Create the database

```sql
CREATE DATABASE IF NOT EXISTS skeleton_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 6. Run migrations, seed, and generate a key

```bash
php console key:generate
php console migrate
php console seed
php console admin:create --email=admin@example.com --password='ChangeMe!123' --name="Admin"
```

`key:generate` writes a fresh `APP_KEY` into `.env`, so run it before `migrate`.

## 7. Sign in

Start Apache + MySQL, then open:

```
http://localhost/php_Skeleton/login
```

Seed admin account:

```
Email:    superadmin@example.com
Password: Password@123
```

Or use the account created by `admin:create`.

## 8. File/directory permissions

Ensure PHP can read/write:

- `storage/uploads/` — 775 (web server + PHP user)
- `storage/backups/` — 775 (backup command writes here)
- `storage/logs/` — 775

## 9. Common issues

| Problem | Fix |
|---|---|
| 404 / CSS missing on `/php_Skeleton/*` | Confirm `APP_URL` matches the folder name and `.htaccess` `RewriteBase /php_Skeleton/`; Apache needs `AllowOverride All` + `mod_rewrite`. |
| `Unknown column 'updated_at'` | You are on an older schema. Run `php console migrate` again or `php console fresh`. |
| 403 Forbidden after login | The logged-in role lacks a permission for that module; assign it on `Roles → permissions`. |
| SMTP no mail sent | Fill `Settings → SMTP` (host/port/username/password). Password is stored encrypted. |

## 10. Going to production

- Set `APP_ENV=production`, `APP_DEBUG=false`.
- Regenerate `APP_KEY` and don't commit `.env`.
- Use HTTPS; force `HTTPS` in `config/config.php` if a reverse proxy terminates TLS.
- Point the document root at `public/` if possible (or keep `.htaccess`).
- Tighten `storage/uploads` to only-required users and keep `session.cookie_secure`/`httponly` on.
- Run `php console backup` regularly or cron it.