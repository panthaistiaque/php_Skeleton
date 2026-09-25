# AGENTS.md

Custom PHP 8.2+ / MySQL / Bootstrap 5 admin skeleton. No framework — MVC-ish custom core under `app/Core` (Router, Request, Model, Validator, Session, View). PSR-4 `App\` → `app/` via Composer autoloader (only Composer dependency).

## Environment (this machine)
- Repo lives in XAMPP htdocs: base URL `http://localhost/php_Skeleton` (matches `APP_URL`). `.htaccess` at root rewrites to `public/index.php`.
- PHP: `E:\xampp\php\php.exe`. MySQL client: `E:\xampp\mysql\bin\mysql.exe -u root` (no password), DB `skeleton_app`. MySQL service must be running for CLI commands.
- No git CLI on PATH — do not rely on `git`; `.git` exists but is unusable from the shell.
- Super admin test login: `superadmin@example.com` / `Password@123`.

## Commands (run from project root; always use the PHP path above)
- `php console migrate|seed|fresh|backup|admin:create|key:generate`
- No test framework, no lint/typecheck script. Verify PHP syntax with `php -l <file>`. Web changes are verified end-to-end with curl (see Verification).
- `php console migrate` applies pending `database/migrations/*.sql` in filename order, tracked in the `migrations` table. There is no rollback and applied files are skipped — **never edit an applied migration; add a new numbered file** (e.g. `0006_...sql`). `fresh` drops all tables, re-runs migrations, then seeds.

## Settings: DB wins, .env is only fallback
- Runtime settings come from the `settings` table read via `setting('group.key')` (e.g. `general.date_format`, `general.records_per_page`, `security.*`). `config/config.php` `default_settings` are fallbacks only.
- The live app's SMTP comes from `settings` (Settings → SMTP), **not** `.env` mail section. `.env` SMTP block is a fallback.
- When you change a live setting, mirror it in `database/seeds/seed.sql` (`INSERT IGNORE INTO settings ...`) so `fresh`/`seed` reproduces it. `smtp.password` currently answers `is_encrypted=0` (Gmail app password stored plaintext by decision).

## Timezones — recurring trap
- `.env` `APP_TIMEZONE=Asia/Dhaka`, but CLI `php` uses its own php.ini timezone and MySQL `NOW()` is server-local (Dhaka, UTC+6). Wall-clock strings written by DB functions and PHP can disagree.
- Password reset / email-verification expiry is intentionally UTC-only: store with `gmdate('Y-m-d H:i:s', ...)` and compare against `strtotime($row['expires_at'] . ' UTC')`. Keep that convention for new token/expiry code.

## Query-builder gotcha
- `app/Core/Concerns/BuildsQueries.php::buildWhere` maps a `null` value to `column IS NULL` (fixed). Never hand-write `= NULL`; pass `null` in the where-array instead.

## Auth / RBAC / menus
- Post-login landing is `/home` (no permission). `/dashboard` still requires `dashboard.view`. 403 page includes logout + home links.
- `user_can('slug')` and `User::isSuperAdmin($id)`; views get `$appUser`. Routes declare `permission:slug` middleware in `config/routes.php`.
- Sidebar menus come from the `menus` table. `permission` NULL → visible to everyone. Parent groups are hidden when all children are inaccessible (see `PermissionService::sidebarMenus`). Add menus to both live DB and `seed.sql`.

## File uploads (`app/Controllers/UploadController.php`)
- Whitelist `ALLOWED_MIME`, 10 MB cap. `uploads` table has an optional `category` VARCHAR(120).
- Delete is restricted to the uploader OR a super admin (`destroy()`), regardless of having `files.delete`. View `app/Views/files/index.php` hides the delete button for non-owners.

## Views conventions
- Bootstrap 5.3 from CDN + `app.css`. Form pattern: `row g-3` with `col-md-6`, stacked labels, submit button in `mt-4 d-flex justify-content-end gap-2`. Category filters use the pagination partial with `$paginationQuery`.
- Dates use `d M Y` / `d M Y, h:i A`.

## Verification workflow (Windows PowerShell gotchas)
- Login with curl requires a CSRF token: GET the page, regex `name="_token" value="(...)"`. When already signed in, `/login` 302s and returns no token — grab the token from an authenticated page (e.g. `/files`) instead.
- Save bodies with `curl -s -o file` and read via `Get-Content file -Raw`. Do not inline `-join` after curl arguments (parsed as a curl option) and don't assign `curl -s URL` directly to a string (truncated to ~one line).
- DELETE routes use POST + `_method=DELETE` (Bootstrap-style); sending raw `-X DELETE` isn't how the app forms work.
- Apache 2.4 on this XAMPP rewrites the non-standard HTTP status 419/429 to `500` (the 419 page still renders). Don't treat it as an app bug. 403/404 report correctly.
- Throwables log to `storage/logs/app-<date>.log` with the request URI — check it when a page 500s.