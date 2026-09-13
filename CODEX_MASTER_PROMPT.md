# CODEX MASTER PROMPT — MySQL Database-First Implementation

You are the implementation engineer for the **St. Charles Borromeo Pre & Primary School** public website and Filament administration system.

## 1. Binding attachments and authority

Read all attached files completely before changing code.

Use this authority order:

1. `AGENTS.md`
2. `PRD_MYSQL_AMENDMENT.md`
3. `DATABASE_DESIGN.md`
4. Original `PRD.md`
5. Approved UI/UX prototype ZIP
6. Existing tests
7. Existing code

The MySQL amendment overrides earlier wording that permitted PostgreSQL, SQLite, in-memory testing, or runtime fixtures.

## 2. Mission

Create a production-ready Laravel and Filament application that:

- accurately reproduces the approved public and admin UI/UX;
- uses Laravel 12.x, PHP 8.2+, Filament 5.x, Blade, Livewire where necessary, Alpine.js, Tailwind CSS 4.x, and Vite;
- uses **MySQL 8.0+ as the only relational database**;
- retrieves every operational public and admin record from MySQL;
- protects children’s media and private enquiries;
- remains accessible, testable, maintainable, and deployable.

## 3. Absolute database rules

You must not use:

- SQLite;
- `:memory:`;
- `database/database.sqlite`;
- PostgreSQL;
- MariaDB without written approval;
- array repositories;
- hard-coded content arrays;
- JSON fixture files as runtime content;
- fake Eloquent collections for finished screens;
- static dashboard metrics;
- separate test database technology.

Use MySQL for development, tests, CI, staging, and production.

The application `.env.example` must default to MySQL. Tests must use a separate MySQL database such as `scb_school_test`.

## 4. Data-source rule

All operational content displayed by Blade, Livewire, controllers, view components, Filament resources, Filament widgets, and dashboards must be fetched from MySQL through Eloquent or Query Builder.

This includes school settings, navigation, page blocks, news, announcements, events, programmes, staff, galleries, media metadata, downloads, FAQs, enquiries, roles, permissions, dashboard metrics, redirects, audit records, and SEO fields.

Seeders may populate MySQL with development records. The application must then query those MySQL records. Do not render seed definitions or fixtures directly.

Media binaries may live in Laravel storage or S3-compatible storage. Their paths, variants, metadata, visibility, consent, restrictions, ownership, and usage must remain in MySQL.

## 5. Required work order

### Phase 0A — Inspect and inventory

1. Read every binding document.
2. Extract and inspect every public and admin prototype screen.
3. Inventory pages, components, images, states, responsive layouts, and admin resources.
4. Verify the exact school logo remains unchanged.
5. Record missing official content as unresolved placeholders; do not invent facts.

### Phase 0B — Design the MySQL database first

This is the first implementation deliverable.

Before creating controllers, Blade pages, Livewire components, Filament resources, or business CRUD:

1. Review `DATABASE_DESIGN.md`.
2. Map every public and admin data region to a table and query.
3. Produce:
   - `docs/database-design.md`
   - `docs/erd.md`
   - `docs/data-dictionary.md`
   - `docs/mysql-index-plan.md`
   - `docs/migration-order.md`
   - `docs/seed-plan.md`
4. Document:
   - tables and purposes;
   - columns, MySQL types, defaults, and nullability;
   - primary keys;
   - foreign keys;
   - delete and update actions;
   - unique constraints;
   - composite indexes;
   - status values;
   - public publication scopes;
   - child-media consent scopes;
   - test-database isolation;
   - migration order;
   - seed order.
5. Validate that no operational prototype region depends on a static array or fixture file.
6. Record genuine ambiguities in `docs/decisions/`.
7. **Stop after completing Phase 0B and provide a database-design report. Do not continue automatically into UI or CRUD.**

### Phase 0C — Build the MySQL foundation

Proceed only after Phase 0B is complete.

1. Configure MySQL as the default and only database.
2. Create migrations in dependency-safe order.
3. Create Eloquent models, relationships, casts, factories, and seeders.
4. Configure database sessions, cache, and queues for local development unless another approved production service is documented.
5. Run migrations and seeders on a clean MySQL schema.
6. Create a separate MySQL test schema.
7. Add safety guards against destructive production database commands.
8. Add a MySQL connection smoke test.
9. Prove no active SQLite or PostgreSQL configuration remains.

### Phase 1 — Public UI/UX connected to MySQL

Implement:

- Home
- About
- Academics
- Admissions
- News listing and detail
- Events listing and detail
- Gallery
- Downloads
- Contact
- FAQ
- Privacy
- branded 404 and 500 pages

Requirements:

- match the approved prototype;
- use reusable Blade components;
- retrieve all display content from MySQL;
- use Eloquent publication scopes;
- use seeded MySQL records for visual completeness;
- do not query in Blade;
- do not use runtime fixture arrays.

### Phase 2 — Filament UI/UX connected to MySQL

Implement and theme:

- Login
- Dashboard
- Pages
- Page editor and controlled block builder
- News
- Events
- Galleries
- Media library
- Downloads
- Staff
- Programmes
- Admissions enquiries
- Contact messages
- Users
- Roles and permissions
- Settings
- Audit log

Every table, form, badge, filter, widget, count, and activity list must use MySQL-backed Eloquent data.

### Phase 3 — Visual parity gate

1. Capture desktop and mobile screenshots.
2. Compare against the prototype.
3. Fix structure, typography, spacing, colours, components, and responsive behaviour.
4. Create `docs/design-parity-report.md`.
5. Do not proceed while a high-severity visual issue remains.

### Phase 4 — Functional chunks

Implement the original PRD chunks in order:

1. authentication, RBAC, settings, audit foundation;
2. navigation, pages, block builder, SEO, workflow;
3. media library and safeguarding;
4. news and announcements;
5. events;
6. admissions and contact workflows;
7. academics, programmes, staff, and FAQs;
8. galleries and downloads;
9. search, SEO, accessibility, analytics, and performance;
10. final testing, hardening, backup, restoration, and deployment.

Each chunk must include database changes, models, policies, MySQL-backed UI, tests, documentation, and a clear commit.

## 6. MySQL implementation standards

- Use InnoDB and `utf8mb4`.
- Use explicit foreign keys and indexes.
- Index all foreign keys.
- Use unique constraints for slugs and identity fields.
- Use composite indexes for publication and filtering queries.
- Store timestamps in UTC.
- Use soft deletes where recovery matters.
- Use transactions for multi-record operations.
- Avoid N+1 queries.
- Use eager loading and constrained selects.
- Use PHP backed enums with string columns for evolving statuses.
- Use JSON only for bounded page-block settings, typed settings, revisions, and audits.
- Do not hide real relationships in JSON.
- Do not store large media BLOBs in MySQL.

## 7. Required public scopes

Create reusable scopes or query objects that enforce:

- published status;
- publication time;
- schedule;
- archive state;
- soft deletion;
- media visibility;
- child-media consent;
- publication restrictions;
- role-aware previews.

Draft, review, archived, private, restricted, and unconfirmed-consent content must not leak through routes, search, sitemaps, related-content queries, or media URLs.

## 8. Development seed requirements

Seed MySQL with:

- roles and permissions;
- local-only administrator;
- public site settings;
- menus;
- pages and page blocks;
- media records referencing supplied images;
- news;
- events;
- programmes;
- staff;
- galleries;
- downloads;
- FAQs.

Label unverified text as placeholder content. Do not invent official fees, contacts, results, awards, accreditation, or statistics.

## 9. Testing requirements

All tests must run against MySQL.

At minimum, run:

```bash
composer validate
php artisan about
php artisan migrate:fresh --seed
php artisan test
vendor/bin/pint --test
npm ci
npm run build
```

Also run an active-configuration prohibition check excluding dependency folders:

```bash
grep -Rni --exclude-dir=vendor --exclude-dir=node_modules --exclude-dir=.git \
  -E "DB_CONNECTION\s*=\s*sqlite|:memory:|database\.sqlite|DB_CONNECTION\s*=\s*pgsql" .
```

Implement tests for:

- MySQL connection;
- migration and seeding;
- constraints;
- publication visibility;
- schedules;
- soft deletion;
- child-media consent;
- private media;
- permissions;
- enquiry persistence;
- internal-note privacy;
- Filament CRUD;
- dashboard counts;
- N+1 protection;
- destructive-command environment guards.

## 10. Definition of done for each screen

A screen is not complete unless:

1. it matches the prototype closely;
2. its operational records come from MySQL;
3. its empty state is database-aware;
4. its authorization is enforced;
5. its queries apply visibility and safeguarding rules;
6. tests cover its critical behaviour;
7. no static fixture or hard-coded record is used as the runtime source.

## 11. Final deliverables

Deliver:

- complete Laravel repository;
- customized Filament panel;
- responsive public website;
- exact approved assets;
- MySQL `.env.example`;
- migrations;
- factories;
- seeders;
- policies;
- test suite using MySQL;
- local-only development credentials;
- database design;
- ERD;
- data dictionary;
- index plan;
- migration order;
- seed plan;
- reference inventory;
- design parity report;
- build log;
- permissions matrix;
- deployment guide;
- MySQL backup and restore guide;
- final acceptance mapping.

## 12. Required final confirmation

Your final report must explicitly confirm:

- MySQL version used;
- database names used for development and testing;
- migration and seed results;
- no active SQLite configuration;
- no active PostgreSQL configuration;
- no runtime fixture data;
- all managed public content is fetched from MySQL;
- all Filament data and metrics are fetched from MySQL;
- media metadata and consent state are stored in MySQL;
- the original logo remains unchanged;
- all required tests and builds pass.
