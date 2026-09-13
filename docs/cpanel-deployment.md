# cPanel Deployment

This guide deploys the application without exposing `.env`, application source, private media, or Composer packages through the website.

## Hosting requirements

- PHP 8.2+ with `curl`, `dom`, `fileinfo`, `gd`, `mbstring`, `pdo_mysql`, `xmlwriter`, and `zip`.
- MySQL 8.0+ (actual MySQL). Do not deploy to MariaDB unless the owner gives explicit written approval.
- cPanel Terminal or SSH is strongly recommended for migrations, cache commands, queues, and the scheduler.
- The domain must have HTTPS before production login or form submission.

## Release archive layout

The generated cPanel archive contains:

```text
scb-cpanel-release/
├── scb_app/       Laravel application, production Composer dependencies, and compiled assets
└── public_html/   Files that may be exposed by the web server
```

The release intentionally excludes `.env`, local/test databases, logs, sessions, caches, tests, Node dependencies, local uploaded media, and development reference files.

## 1. Back up the account

Create a cPanel account/database backup before replacing an existing site. If this is a site move, transfer the MySQL database and `storage/app/private` media together; database rows without their matching binaries are incomplete.

## 2. Select PHP 8.2 or newer

In **cPanel → MultiPHP Manager**, select PHP 8.2 or newer for the domain. Confirm the required extensions in **Select PHP Version** or ask the host to enable them.

In **cPanel → MultiPHP INI Editor**, set:

```ini
file_uploads=On
upload_max_filesize=21M
post_max_size=25M
memory_limit=256M
max_execution_time=120
```

`post_max_size` must remain larger than `upload_max_filesize`. The release also includes `public_html/.user.ini`, but the cPanel editor is authoritative when the host disables per-directory PHP settings.

## 3. Upload and extract

Open **File Manager**, enable **Show Hidden Files**, upload the release ZIP to the cPanel account home directory, and extract it.

Move the extracted `scb_app` directory to:

```text
/home/CPANEL_USER/scb_app
```

Copy the contents of the extracted `public_html` directory—including `.htaccess` and `.user.ini`—into the domain's actual document root.

For a primary domain, that is normally `/home/CPANEL_USER/public_html`. For an addon domain or subdomain, cPanel may allow its document root to point directly to `/home/CPANEL_USER/scb_app/public`; if so, the second copy is unnecessary and the standard `scb_app/public/index.php` can be used.

The packaged split-layout `public_html/index.php` expects the private application at `~/scb_app`. Edit `$applicationRoot` only if a different private directory name is used.

## 4. Create MySQL database and user

Use **cPanel → MySQL Database Wizard** to create a database and least-privilege application user, then grant that user all privileges on this application database. cPanel normally prefixes both names with the account username; use the complete displayed names in `.env`.

Create `/home/CPANEL_USER/scb_app/.env` from `.env.production.example` and set at least:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_KEY=

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=CPANEL_USER_scb_school
DB_USERNAME=CPANEL_USER_scb_app
DB_PASSWORD=STRONG_DATABASE_PASSWORD

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
```

Never place `.env` in `public_html` or include it in a ZIP shared with another person.

## 5. Install the application

From cPanel Terminal or SSH:

```bash
cd /home/CPANEL_USER/scb_app
php artisan key:generate --force
php artisan migrate --force
php artisan optimize
chmod -R u+rwX,g+rwX storage bootstrap/cache
```

Do not run `migrate:fresh`, `db:wipe`, or `db:seed` in production. The application rejects destructive and representative development seed commands outside local/testing environments.

A new empty schema still needs approved production content and an administrator account through the controlled data-promotion process. For an existing site move, import its reviewed MySQL backup before running `php artisan migrate --force`, and copy the matching private media into `storage/app/private`.

## 6. Cron and queues

Create this cPanel cron job, replacing `CPANEL_USER` and the PHP binary when the host uses a versioned path:

```text
* * * * * cd /home/CPANEL_USER/scb_app && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

For queued mail, use a supervised worker if the host provides one. On shared hosting without a process supervisor, ask the host for its supported queue-worker method; do not run an unbounded worker from a web request.

## 7. Final checks

```bash
cd /home/CPANEL_USER/scb_app
php artisan about --only=environment,drivers
php artisan migrate:status
php artisan route:list
```

Then verify:

- `/up` returns HTTP 200;
- `/`, `/admin`, `/admin-core`, and `/staff-portal/login` load over HTTPS;
- `APP_DEBUG` is false;
- image upload accepts up to 10 MB and documents up to 20 MB;
- private/restricted media URLs return 404 publicly;
- the queue and scheduler are operating;
- no `.env`, `vendor`, `storage`, or source file can be fetched through the domain.

If cPanel returns 403/404/500 after extraction, first confirm that hidden files were copied, `public_html/index.php` points to the correct private application directory, PHP 8.2 or newer is selected, and `storage` plus `bootstrap/cache` are writable.
