# Build Log

## 2026-08-03

### Completed

- Read `AGENTS.md`, `PRD.md`, `CODEX_MASTER_PROMPT.md`, and the prototype README.
- Verified the prototype ZIP SHA-256:
  `45a8095cfb10247092535cc3f79a84ad6343d321e3a09c2add1a9652fcfab3e4`.
- Verified the immutable school logo SHA-256:
  `2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b`.
- Extracted `st_charles_borromeo_uiux_prototype.zip` to `reference/scb_uiux_prototype/`.
- Copied approved prototype assets to `public/assets/prototype/`.
- Copied the protected logo to `resources/reference/logo-original.jpg` and `public/assets/images/brand/scb-logo-original.jpg` without editing.
- Copied the supplied school JPG images to `public/assets/images/school/`.
- Copied the supplied Falconode logo to `public/assets/images/brand/falconode-original.png`.
- Created a derived Falconode display crop at `public/assets/images/brand/falconode-credit.png`.
- Added `PrototypePageController` to render the approved public and admin prototype screens through Laravel routes.
- Added Falconode "Designed by" credit markup to public and admin screens, then revised it into footer/admin-footer placement.
- Added branded `404` and `500` error pages using the same visual system and Falconode credit.
- Replaced the default Laravel README with project-specific setup and status notes.
- Added `docs/reference-inventory.md`.
- Added feature tests for public/admin rendering, Falconode credit, asset rewriting, and admin link rewriting.
- Verified the Laravel dev server before the MySQL-session switch at `http://127.0.0.1:8010`.
- Imported the updated MySQL-first binding docs: `AGENTS.md`, `CODEX_MASTER_PROMPT.md`, `PRD_MYSQL_AMENDMENT.md`, and `DATABASE_DESIGN.md`.
- Added the Google verification file at `public/googled53056c205f02776.html`.
- Changed active environment defaults to MySQL with database sessions, cache, and queues.
- Removed active SQLite, PostgreSQL, MariaDB, and SQL Server connection definitions from `config/database.php`.
- Removed SQLite defaults from `config/queue.php`, `phpunit.xml`, and `composer.json`.
- Completed Phase 0B documentation:
  `docs/database-design.md`,
  `docs/erd.md`,
  `docs/data-dictionary.md`,
  `docs/mysql-index-plan.md`,
  `docs/migration-order.md`,
  and `docs/seed-plan.md`.
- Documented blockers in `docs/decisions/2026-08-03-mysql-admin-credentials-needed.md` and `docs/decisions/2026-08-03-framework-version-gap.md`.

### Verification

- `composer validate` passed.
- `php artisan test` passed with 3 skipped MySQL-dependent HTTP tests: 3 passed, 3 skipped, 9 assertions.
- Skipped tests are blocked by unavailable `scb_school_test` credentials/schema, not by fallback database configuration.
- `./vendor/bin/pint` passed.
- `npm install` completed: 86 packages installed, 0 vulnerabilities reported.
- `npm run build` passed with Vite.
- `php artisan about` confirms: Database `mysql`, Session `database`, Cache `database`, Queue `database`.
- `php artisan route:list --except-vendor` lists 33 public/admin visual-baseline routes.
- Active-configuration prohibition scan over `.env`, `.env.example`, `config`, `phpunit.xml`, `composer.json`, `app`, `routes`, `tests`, and `database` found no active SQLite/PostgreSQL references.
- After switching to database sessions, `curl -I http://127.0.0.1:8010/` returns `HTTP/1.1 500 Internal Server Error` until the MySQL `sessions` table exists.
- The temporary dev server was stopped after this verification.
- MySQL tooling is present: `mysql`, `mysqld`, and PHP `pdo_mysql`.
- MySQL client version: `8.0.46-0ubuntu0.22.04.2`.
- Local PHP version: `8.2.31`; this is below the required PHP 8.3+.
- `service mysql status` reports MySQL Community Server running.
- MySQL schema setup is blocked by missing local database administrator credentials.

### Known Blockers And Gaps

- The checkout contains an empty `.git` directory, so a required Git commit cannot be created yet.
- Clean MySQL migrations are blocked until credentials are provided for creating or accessing `scb_school` and `scb_school_test`.
- Filament is not installed; admin screens are currently static visual baseline pages.
- Authentication and authorization for `/admin/*` are not implemented yet.
- The installed framework is Laravel 12.x and local PHP is 8.2.31, while the updated project contract specifies Laravel 13.x and PHP 8.3+.
- The current prototype bridge is not yet compliant with the final "all displayed data from MySQL" rule. Phase 0C must add migrations, models, seeders, and Eloquent-backed rendering before UI work proceeds.

## 2026-08-04

### Completed

- Reviewed the project markdown status and reconciled it with the current Laravel files.
- Confirmed Phase 0B documentation remains present:
  `docs/database-design.md`,
  `docs/erd.md`,
  `docs/data-dictionary.md`,
  `docs/mysql-index-plan.md`,
  `docs/migration-order.md`,
  and `docs/seed-plan.md`.
- Confirmed Phase 0C foundation code is now present:
  MySQL migrations, Eloquent models, model factories, deterministic seed data, destructive database command guard, and MySQL-specific tests.
- Updated the MySQL-dependent test harness so unavailable local test credentials skip database tests before Laravel attempts schema refresh.
- Updated `README.md` and `docs/reference-inventory.md` so the next milestone points to MySQL schema verification rather than creating already-present foundation files.

### Verification

- `composer validate` passed.
- `php artisan about` confirms Laravel `12.64.0`, PHP `8.2.31`, and active drivers: Database `mysql`, Session `database`, Cache `database`, Queue `database`.
- `php artisan test` passed with 3 passing checks and 7 skipped MySQL-dependent checks because `scb_school_test` is unavailable.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed with Vite.
- PHP syntax check passed for application, migration, factory, seeder, route, config, and test PHP files.
- `php artisan route:list --except-vendor` lists 33 public/admin prototype-baseline routes.
- Project prohibition scan found no active SQLite/PostgreSQL configuration outside binding documentation that describes the prohibition.

### Known Blockers And Gaps

- Clean MySQL migration and seed proof remains blocked until `scb_school` and `scb_school_test` are created and accessible to `scb_app`.
- Filament remains uninstalled; admin routes are still static prototype-baseline screens.
- Public screens still render the approved prototype HTML instead of querying seeded MySQL records.
- Authentication and authorization for `/admin/*` are not implemented yet.
- The installed framework is Laravel 12.x and local PHP is 8.2.31, while the contract specifies Laravel 13.x and PHP 8.3+.
- The checkout is still not a valid Git repository, so milestone commits cannot be created yet.
