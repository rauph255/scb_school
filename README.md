# St. Charles Borromeo Website

Production-oriented Laravel 12 and Filament 5 implementation for the St. Charles Borromeo Pre & Primary School public website, administration experience, and staff portal.

## Build state

- PHP `^8.2`, Laravel `12.64.0`, Filament `5.7.5`, Tailwind CSS 4, and Vite are locked in `composer.lock` and `package-lock.json`.
- MySQL 8 is the only runtime and test database. Sessions, cache, and queues use MySQL-backed database drivers.
- Public pages, the visible `/admin` experience, `/admin-core` Filament resources, and the staff portal use Eloquent records rather than runtime fixtures.
- Public media and downloads are streamed through authorization-aware routes; managed binaries are private and MySQL visibility, consent, and safeguarding state control access.
- Public forms persist to MySQL, are rate-limited and honeypot-protected, and queue non-sensitive administrator notifications.
- Administrator and staff authentication include role enforcement, CSRF-protected logout, throttling, and token-based password reset.
- Development seed accounts are blocked when `APP_ENV` is not `local` or `testing`.
- The approved administration screens are production resources under `resources/admin-experience/`; runtime code no longer reads from the design-reference directory.
- The immutable logo SHA-256 remains `2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b`.

## Requirements

- PHP 8.2 or newer with `curl`, `dom`, `fileinfo`, `gd`, `mbstring`, `pdo_mysql`, `xmlwriter`, and `zip`
- MySQL 8.0+ using InnoDB and `utf8mb4`
- Composer 2
- Node.js 20+ and npm
- A queue worker and scheduler process in production

## Local setup

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
php artisan test
composer run dev
```

The development command starts PHP with `upload_max_filesize=21M` and `post_max_size=25M`, supporting the application's 10 MB image/media limit and 20 MB document limit. Restart the development server after changing PHP upload settings.

The local MySQL contract is:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=scb_school
DB_USERNAME=scb_app
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Tests must use the separate `scb_school_test` schema. The test harness refuses a second concurrent destructive suite and fails, rather than skips, when MySQL is unavailable.

Local-only seeded access:

```text
Admin: local.admin@example.test / ChangeMeLocalOnly!
Staff: local.teacher@example.test / ChangeMeLocalOnly!
```

These representative accounts cannot be seeded in production.

## Application entry points

- Public website: `/`
- Administration: `/admin`
- Internal Filament panel: `/admin-core`
- Staff portal: `/staff-portal/login`
- Health check: `/up`

## Production

Use `.env.production.example` as the deployment checklist, supply secrets outside Git, configure HTTPS, run queue workers and the scheduler, and follow `docs/production-deployment.md`. Do not run the representative `DatabaseSeeder` in production.

## Documentation

- Database design: `docs/database-design.md`
- ERD: `docs/erd.md`
- Data dictionary: `docs/data-dictionary.md`
- Index plan: `docs/mysql-index-plan.md`
- Migration order: `docs/migration-order.md`
- Seed plan: `docs/seed-plan.md`
- Reference inventory: `docs/reference-inventory.md`
- Production deployment: `docs/production-deployment.md`
- Production readiness evidence: `docs/production-readiness-report.md`
- Design parity status: `docs/design-parity-report.md`
- Build log: `docs/build-log.md`
