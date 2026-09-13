# Production Deployment

Last verified: 2026-08-22

## Runtime Contract

- PHP 8.2+.
- Required PHP extensions: `curl`, `dom`, `fileinfo`, `filter`, `gd`, `hash`, `mbstring`, `openssl`, `pcre`, `pdo`, `pdo_mysql`, `session`, `tokenizer`, `xml`, `xmlwriter`, and `zip`.
- Laravel 12.64.0 and Filament 5.7.5 from the committed `composer.lock`.
- MySQL 8.0+ only, using InnoDB and `utf8mb4_unicode_ci`.
- Node.js 20+ is required on the build host only.
- The web root must be the repository's `public/` directory.
- `storage/` and `bootstrap/cache/` must be writable by the PHP/web process.
- PHP-FPM or the web server must set `upload_max_filesize=21M` and `post_max_size=25M`; `public/.user.ini` supplies these values where per-directory PHP configuration is enabled.
- Set the reverse-proxy request-body limit to at least 25 MB as well (for example, Nginx `client_max_body_size 25m`); otherwise the proxy can reject uploads before PHP or Laravel receives them.

The release must be built and verified with PHP 8.2-compatible dependencies and the required PHP extensions enabled.

## Environment

Copy `.env.production.example` to the server's secret environment store and replace every blank or example value. At minimum:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://school.example
APP_KEY=<generated-production-key>

DB_CONNECTION=mysql
DB_HOST=<private-mysql-host>
DB_PORT=3306
DB_DATABASE=scb_school
DB_USERNAME=<least-privilege-user>
DB_PASSWORD=<secret>
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Configure a real SMTP sender and set the MySQL `contact.primary_email` site setting so public enquiry notifications have an approved recipient. Never commit the populated production environment.

In Site settings, leave analytics disabled until the owner has approved a privacy-conscious provider. Plausible loads only after optional cookie consent and requires the production website domain in `analytics.site_id`. Confirm the privacy notice reflects the final provider before enabling it.

## Release Sequence

Run on an immutable release directory with PHP 8.2+ selected:

```bash
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
php artisan storage:unlink
php artisan queue:restart
```

`storage:unlink` removes any legacy public storage symlink. Managed media is stored privately and served through authorization-aware routes. Do not run `db:seed`, `migrate:fresh`, `migrate:refresh`, `migrate:reset`, `migrate:rollback`, or `db:wipe` in production; application guards reject destructive commands and representative seed data outside local/testing environments.

The curated graduation WebP masters and variants are versioned under `resources/reference/school/2026/graduation/`, while their runtime metadata, consent and content relationships live in MySQL. Promote approved content records and their corresponding private-storage binaries together using the deployment data process; never deploy database rows without the matching files. New production uploads should use the media library so the same conversion, consent and audit rules are applied.

Before switching traffic, verify:

```bash
php artisan about --only=environment,drivers
php artisan migrate:status
php artisan route:list
curl --fail --silent --show-error https://school.example/up
```

The expected drivers are MySQL database, database cache, database queue, and database session. `APP_DEBUG` must be disabled.

## Long-Running Processes

Run at least one supervised queue worker:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=120 --max-time=3600
```

The worker delivers public-submission notifications and tracked administrator replies. Monitor `failed_jobs` and `email_replies.status`; retry or investigate failed mail only after correcting SMTP/provider errors.

Run the scheduler every minute from the operating system scheduler:

```text
* * * * * cd /path/to/current && php artisan schedule:run >> /dev/null 2>&1
```

Reload PHP-FPM or the application server after every deployment so cached code is replaced.

## Backup

Create an encrypted, access-controlled MySQL backup before each migration and on the operational backup schedule:

```bash
mysqldump --single-transaction --quick --routines --triggers --default-character-set=utf8mb4 --host=<host> --user=<backup-user> --password scb_school > scb_school_YYYYMMDD_HHMMSS.sql
```

Back up the private media storage or S3 bucket in the same recovery set. Database rows without their corresponding stored binaries are not a complete backup. Retain backup checksums and test restoration regularly.

## Restore Drill

Restore only to a newly created, isolated schema first:

```bash
mysql --host=<host> --user=<restore-user> --password -e 'CREATE DATABASE scb_school_restore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
mysql --host=<host> --user=<restore-user> --password scb_school_restore < scb_school_YYYYMMDD_HHMMSS.sql
```

Point a non-production application instance at `scb_school_restore`, run `php artisan migrate:status`, verify representative public/admin records, and verify media checksums. Production replacement requires an approved maintenance window and a fresh pre-restore backup.

## Release Acceptance

- Run the full MySQL suite, Pint, Composer audit, npm audit, and the Vite build in CI.
- Confirm public child media cannot be fetched unless the MySQL visibility/consent scopes permit it.
- Confirm admin and staff routes redirect unauthenticated users and reject users without the required role.
- Confirm SMTP delivery, queue processing, HTTPS cookies, security headers, backups, monitoring, and alerting.
- Confirm `/robots.txt`, `/sitemap.xml`, public search, canonical/social metadata, consent reset, and optional analytics behaviour on the production hostname.
- Complete the screenshot sign-off described in `docs/design-parity-report.md` using the production build and approved content.
