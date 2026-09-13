# AGENTS.md — Binding MySQL Database-First Implementation Contract

This file governs all planning, coding, testing, configuration, and delivery for the St. Charles Borromeo Pre & Primary School website and administration system.

## 1. Authority and precedence

When instructions conflict, follow this order:

1. This `AGENTS.md`.
2. `PRD_MYSQL_AMENDMENT.md`.
3. `DATABASE_DESIGN.md`.
4. The original `PRD.md`.
5. The extracted approved UI/UX prototype.
6. Automated acceptance tests.
7. Existing implementation code.
8. Personal engineering preference.

The prototype controls visual output. The requirements documents control behaviour. This file controls database technology, persistence, implementation order, and non-deviation rules.

## 2. Mandatory technology

Use:

- PHP 8.2+
- Laravel 12.x
- Filament 5.x
- Blade
- Livewire only where interaction requires it
- Alpine.js
- Tailwind CSS 4.x
- Vite
- **MySQL 8.0+ only**
- InnoDB
- `utf8mb4`
- Eloquent ORM and Laravel Query Builder
- Pest or PHPUnit
- Laravel Pint
- Git-based milestone delivery

## 3. Prohibited database choices

Do not use:

- SQLite;
- SQLite in-memory databases;
- PostgreSQL;
- MariaDB unless the owner gives explicit written approval;
- an array-backed repository;
- a JSON-file database;
- a fake persistence layer;
- browser local storage as the source of operational content;
- static PHP arrays as the website content source;
- hard-coded page records in Blade templates;
- fixture files as the runtime data source;
- a second relational database engine for tests.

These prohibitions apply to local development, automated tests, CI, staging, and production.

Delete or ignore `database/database.sqlite`. Do not create it. Do not configure `DB_CONNECTION=sqlite` in `.env`, `.env.example`, `phpunit.xml`, Pest configuration, CI, Docker, or scripts.

## 4. MySQL configuration contract

The default environment must be MySQL:

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

Testing must use a separate MySQL schema and credentials:

```dotenv
DB_CONNECTION=mysql
DB_DATABASE=scb_school_test
```

Never run destructive tests against development, staging, or production databases. Add an environment guard that rejects `migrate:fresh`, `db:wipe`, or destructive seed operations outside approved local/testing environments.

## 5. Database-first build sequence

The required sequence is:

### Milestone 0A — Reference inspection

- Read this file, the MySQL amendment, original PRD, database design, and prototype.
- Inventory public and admin screens.
- Confirm the exact logo and approved visual tokens.
- Record ambiguities without inventing school facts.

### Milestone 0B — Database design before implementation

Before creating UI components, controllers, Filament resources, or business CRUD:

1. Review `DATABASE_DESIGN.md`.
2. Produce:
   - `docs/database-design.md`;
   - `docs/erd.md`;
   - `docs/data-dictionary.md`;
   - `docs/mysql-index-plan.md`;
   - `docs/migration-order.md`;
   - `docs/seed-plan.md`.
3. Confirm table boundaries, relationships, nullability, deletion rules, uniqueness, indexes, status values, and content-publication rules.
4. Confirm every prototype data region has a MySQL source.
5. Record unresolved decisions in `docs/decisions/`.
6. Stop and report the milestone before proceeding.

### Milestone 0C — MySQL foundation

- Configure the application for MySQL.
- Create migrations in dependency-safe order.
- Add models, casts, relationships, factories, and seeders.
- Create development and testing schemas.
- Run `php artisan migrate:fresh --seed` against MySQL.
- Add a database smoke test.
- Prove no SQLite configuration remains.

### Milestone 1 — Public UI using MySQL data

- Build the complete public UI to match the prototype.
- Controllers, actions, query services, scopes, or view models must retrieve all displayed operational content from MySQL.
- Seeded development content is allowed only because it is stored in MySQL.
- Do not use fixture arrays to render pages.

### Milestone 2 — Filament UI using MySQL data

- Build the customized Filament panel.
- Tables, forms, widgets, badges, filters, dashboards, counts, and recent activity must use Eloquent records from MySQL.
- Do not use mocked collections or static dashboard metrics.

### Milestone 3 — Visual parity gate

- Compare every public and admin screen at desktop and mobile widths.
- Fix visual drift without disconnecting components from MySQL.
- Produce `docs/design-parity-report.md`.

### Milestone 4 onward — Functional chunks

Implement the PRD chunks in order. Each chunk must include migrations when needed, Eloquent integration, policies, UI, tests, documentation, and a clean commit.

## 6. Database-backed content rule

The following must come from MySQL:

- school identity and contact settings;
- header and footer content;
- menus and menu items;
- home-page sections;
- every managed page and page block;
- news, categories, tags, and announcements;
- events and event categories;
- programmes, departments, and staff;
- galleries and gallery ordering;
- media metadata and publication permissions;
- downloads and categories;
- FAQs and categories;
- admissions enquiries;
- contact messages;
- internal enquiry notes;
- users, roles, and permissions;
- dashboard totals and recent activity;
- redirects;
- audit logs;
- publication status and schedules;
- SEO metadata;
- media consent and safeguarding state.

Blade files may define markup and presentation structure. They may not contain the operational records that administrators are expected to manage.

Application enums may define allowed status names, but the actual record status must be stored in MySQL.

## 7. Media storage rule

Do not store large image or document binaries as MySQL BLOB fields.

Use Laravel storage or an S3-compatible object store for binary files. Store all of the following in MySQL:

- original and secure stored names;
- disk and path;
- MIME type;
- extension;
- size;
- image dimensions;
- checksum;
- alternative text;
- caption and credit;
- focal point;
- visibility;
- consent requirements and confirmation;
- publication restriction;
- uploader;
- generated variants;
- usage references.

Public access must be based on MySQL visibility and consent rules, not merely on whether a file path is guessable.

## 8. MySQL schema conventions

- Use InnoDB.
- Use `utf8mb4` with a documented collation.
- Use unsigned `BIGINT` primary keys unless a UUID is explicitly justified.
- Use foreign keys for real relationships.
- Index every foreign key.
- Use explicit unique constraints.
- Use `DECIMAL`, not floating point, for precise numeric values.
- Store timestamps in UTC and display them in the configured school timezone.
- Use nullable columns only when absence is meaningful.
- Prefer normalized tables for relationships.
- Use JSON only for bounded block configuration, revision snapshots, and typed settings; do not hide relational data in JSON.
- Use soft deletes where accidental deletion is harmful.
- Define `ON DELETE` behaviour intentionally; never accept framework defaults without review.
- Avoid MySQL `ENUM` when values are likely to evolve. Prefer constrained strings represented by PHP backed enums and validated at application boundaries.
- Keep slugs within index-safe lengths.
- Add composite indexes for actual listing, publication, and filtering queries.
- Use database transactions for multi-record operations.
- Avoid N+1 queries through eager loading and query review.

## 9. Query and rendering rules

- Public publication scopes must enforce status, publication time, archive state, soft deletion, and safeguarding rules.
- Draft, review, private, restricted, or unconfirmed-consent media must never be exposed by public queries.
- Use pagination for potentially large lists.
- Avoid `SELECT *` on high-volume or sensitive resources when a narrow projection is sufficient.
- Do not query in Blade templates.
- Keep controllers thin.
- Put reusable query logic in Eloquent scopes, actions, or precisely named query services.
- Cache may improve performance, but MySQL remains the authoritative source.
- Cache invalidation must happen after administrative changes.

## 10. Seed data rules

Seeders are required for local development and automated tests.

Seeders must:

- insert records into MySQL;
- be deterministic where tests depend on them;
- create roles and permissions;
- create a local-only super administrator;
- populate menus, settings, pages, blocks, categories, and representative content;
- reference the supplied school images through media records;
- clearly label unverified placeholder content;
- never invent official fees, contacts, results, accreditation, or statistics;
- avoid production credentials;
- be safe to disable or replace for production deployment.

## 11. Testing rules

All database-related tests must run against MySQL.

Required evidence:

```bash
php artisan about
php artisan migrate:fresh --seed
php artisan test
vendor/bin/pint --test
npm ci
npm run build
```

Also run a project-level prohibition check, excluding `vendor/` and `node_modules/`, for example:

```bash
grep -Rni --exclude-dir=vendor --exclude-dir=node_modules --exclude-dir=.git \
  -E "DB_CONNECTION\s*=\s*sqlite|:memory:|database\.sqlite|pgsql|postgres" .
```

Any intentional mention in documentation explaining the prohibition must be separated from active configuration and code.

Required tests include:

- MySQL connection smoke test;
- clean migration and seed test;
- foreign-key integrity tests;
- unique constraint tests;
- publication-scope tests;
- safeguarding and consent tests;
- role and policy tests;
- public forms persistence tests;
- Filament CRUD persistence tests;
- query-count or N+1 checks on key pages;
- testing-environment safety guard.

## 12. Visual non-deviation

- Use the approved UI/UX prototype as the visual source of truth.
- Keep the exact logo unchanged.
- Use the supplied images.
- Preserve page structure, colours, typography direction, spacing, responsive behaviour, component proportions, and image treatment.
- Do not replace the design with a generic template.
- Customize Filament to match the approved admin design.
- Do not use default Filament presentation as the final product.

## 13. Security and safeguarding

- Enforce authorization with policies and gates.
- Protect private enquiries and internal notes.
- Validate uploads by real MIME type, extension, size, and decoding.
- Do not publish child media when required consent is unconfirmed.
- Use secure randomized stored filenames.
- Strip unnecessary EXIF metadata from public derivatives.
- Rate-limit public forms.
- Audit privileged actions.
- Never expose secrets or local development credentials in production.

## 14. Chunk completion gate

A chunk is complete only when:

- migrations work on a clean MySQL schema;
- factories and seeders create valid MySQL records;
- public and admin interfaces read from MySQL;
- relevant policies are enforced;
- tests pass against MySQL;
- assets build;
- formatting passes;
- documentation and build log are updated;
- no fake runtime data remains;
- the milestone has a clear commit.

## 15. Final delivery proof

The final report must include:

- MySQL version;
- sanitized connection configuration;
- database name conventions;
- migration list and status;
- table inventory;
- ERD and data dictionary paths;
- index-review evidence;
- seed command and local credentials;
- test command outputs;
- confirmation that no SQLite or PostgreSQL runtime configuration exists;
- confirmation that every managed public and Filament screen reads from MySQL;
- backup and restoration instructions;
- confirmation that the exact logo remains unchanged.
