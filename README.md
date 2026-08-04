# St. Charles Borromeo Website

Laravel implementation workspace for the St. Charles Borromeo Pre & Primary School public website and administration panel.

## Current Build State

- The approved UI/UX prototype ZIP is extracted to `reference/scb_uiux_prototype/`.
- Prototype hash verified: `45a8095cfb10247092535cc3f79a84ad6343d321e3a09c2add1a9652fcfab3e4`.
- Immutable school logo hash verified: `2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b`.
- Public and admin prototype screens are served through Laravel routes as the current visual baseline.
- The supplied school JPG images are copied to `public/assets/images/school/`.
- Falconode credit assets are copied to `public/assets/images/brand/` and shown cleanly in the public/admin footer areas.
- Active configuration now defaults to MySQL, database sessions, database cache, and database queues.
- Phase 0B database documentation is complete in `docs/`.
- Phase 0C foundation code is present: migrations, Eloquent models, factories, deterministic seed data, destructive-command guard, and MySQL-focused tests.

## Local Commands

```bash
composer install
npm install
php artisan test
npm run build
php artisan serve
```

The required local database settings are:

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

Open the public site after MySQL schemas and migrations exist:

```text
http://127.0.0.1:8000
```

Open the current admin UI baseline at:

```text
http://127.0.0.1:8000/admin
```

## Important Constraints

- Do not edit `assets/logo-original.jpg`, `resources/reference/logo-original.jpg`, or `public/assets/images/brand/scb-logo-original.jpg`.
- The Falconode cropped credit image is a derived display asset only; the supplied original remains at `public/assets/images/brand/falconode-original.png`.
- The current `/admin` screens are static UI baseline screens, not authenticated Filament resources yet.
- The current prototype bridge is not the final database-backed implementation. The next milestone is verifying the existing MySQL migrations/seeders against real `scb_school` and `scb_school_test` schemas, then replacing prototype rendering with Eloquent-backed Blade/Filament screens.
- MySQL admin credentials or pre-created schemas are needed before `php artisan migrate:fresh --seed` and the MySQL-dependent tests can fully run locally.
- This checkout has an empty `.git` directory, so commits cannot be created until Git metadata is restored.

## Documentation

- Project contract: `AGENTS.md`
- MySQL amendment: `PRD_MYSQL_AMENDMENT.md`
- Database source design: `DATABASE_DESIGN.md`
- Product requirements: `PRD.md`
- Database design: `docs/database-design.md`
- ERD: `docs/erd.md`
- Data dictionary: `docs/data-dictionary.md`
- MySQL index plan: `docs/mysql-index-plan.md`
- Migration order: `docs/migration-order.md`
- Seed plan: `docs/seed-plan.md`
- Reference inventory: `docs/reference-inventory.md`
- Build log: `docs/build-log.md`
- Decisions: `docs/decisions/`
