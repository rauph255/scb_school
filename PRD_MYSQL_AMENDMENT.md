# PRD MYSQL AMENDMENT — Binding Override

**Product:** St. Charles Borromeo Pre & Primary School Website and Administration System  
**Status:** Binding amendment  
**Scope:** Database engine, persistence, implementation order, testing, and runtime content

## 1. Override rule

This amendment overrides any earlier requirement that:

- permits PostgreSQL as an alternative;
- permits SQLite for development or tests;
- permits an in-memory database;
- permits public or admin screens to run from fixtures, arrays, JSON files, or fake repositories;
- postpones database design until after UI implementation.

All non-conflicting original PRD requirements remain binding.

## 2. Database technology

The application must use **MySQL 8.0+ exclusively**.

This rule applies to:

- local development;
- automated testing;
- CI;
- staging;
- production;
- scheduled jobs;
- queues that use a database backend;
- sessions that use a database backend;
- administrative reporting.

SQLite and PostgreSQL are not approved fallback engines.

## 3. Database-first implementation order

The initial milestone is database architecture, not application UI.

Codex must first deliver and internally validate:

1. domain model;
2. ERD;
3. data dictionary;
4. table definitions;
5. relationship rules;
6. foreign-key actions;
7. uniqueness constraints;
8. index strategy;
9. migration order;
10. seed strategy;
11. MySQL test-schema strategy;
12. data ownership and safeguarding rules.

Only after that milestone may Codex implement migrations and seeders. Public and admin UI implementation follows after the MySQL foundation works.

## 4. Runtime data-source requirement

Every operational record displayed by the public website or Filament panel must be retrieved from MySQL through Eloquent or Laravel Query Builder.

This includes:

- navigation;
- footer;
- school identity;
- pages and page blocks;
- news;
- announcements;
- events;
- programmes;
- departments;
- staff;
- media metadata;
- galleries;
- downloads;
- FAQs;
- enquiries;
- users;
- permissions;
- settings;
- dashboard metrics;
- audit history;
- SEO fields;
- redirects.

Do not use runtime fixture files or static arrays for these records.

## 5. Seeded UI data

Codex may seed representative development records into MySQL so the UI can be visually complete before all workflows are finalized.

The rendered UI must query the seeded MySQL records. It must not import fixtures directly into Blade, Livewire, Filament widgets, controllers, or JavaScript.

## 6. Media exception

Large binary files belong in Laravel storage or an S3-compatible object store. This is not an exception to the database-backed application rule.

MySQL remains authoritative for:

- file location;
- file metadata;
- ownership;
- alternative text;
- caption;
- dimensions;
- generated variants;
- visibility;
- child-media consent;
- publication restrictions;
- usage references.

## 7. Testing

Tests must use a separate MySQL database. The test configuration must not use SQLite or `:memory:`.

CI must provision MySQL, run clean migrations, seed required reference data, execute tests, and destroy the disposable database after the job.

## 8. Acceptance additions

The project is not accepted unless:

1. MySQL is the only configured relational engine.
2. A clean MySQL schema can be migrated and seeded.
3. All public content regions fetch from MySQL.
4. All Filament tables, forms, widgets, and dashboard metrics fetch from MySQL.
5. No active SQLite or PostgreSQL configuration exists.
6. No runtime fixture arrays or JSON content stores remain.
7. The database design, ERD, dictionary, migration order, and index plan are documented.
8. Publication and safeguarding rules are enforced in database-backed queries.
