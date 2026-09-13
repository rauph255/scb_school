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

### Superseded Blockers And Gaps

- At this point in the earlier setup, the checkout appeared to contain an empty `.git` directory; Git metadata is now present.
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
- Git metadata is present; a milestone commit is still pending.

### Continued: MySQL Fix And Milestone 1 Start

- Verified local MySQL access with MySQL `8.0.46`.
- Confirmed `scb_school` and `scb_school_test` exist.
- Confirmed `scb_app` has privileges on both schemas.
- Updated local `.env` to use the application MySQL user instead of MySQL root.
- Ran `php artisan migrate:fresh --seed` successfully against `scb_school`.
- Confirmed migration status shows all 9 migration files ran.
- Confirmed table inventory in `scb_school`: 45 tables, 11 pages, 12 page blocks, and 10 media records.
- Converted `/` from `PrototypePageController` to `HomePageController`.
- Added `resources/views/public/layout.blade.php` and `resources/views/public/home.blade.php`.
- Enriched home page seed block settings and footer menu seed data so homepage content, actions, stats, navigation, footer links, news, events, settings, and media render from MySQL records.
- Strengthened the homepage test to mutate a MySQL `page_blocks` record and assert the changed heading appears in the rendered home page.

### Verification After MySQL Fix

- `php artisan migrate:fresh --seed` passed against MySQL.
- `php artisan test` passed: 10 tests, 49 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- `php artisan about` confirms: Database `mysql`, Session `database`, Cache `database`, Queue `database`.
- `php artisan migrate:status` confirms all migration files ran.
- Project prohibition scan found no active SQLite/PostgreSQL configuration outside binding documentation that describes the prohibition.

### Remaining Gaps

- Public routes other than `/` still render static prototype HTML.
- Filament remains uninstalled; admin routes are still static prototype-baseline screens.
- Authentication and authorization for `/admin/*` are not implemented yet.
- The installed framework is Laravel 12.x and local PHP is 8.2.31, while the contract specifies Laravel 13.x and PHP 8.3+.
- Git metadata is present; a milestone commit is still pending.

### Continued: More Public MySQL Screens

- Added `LoadsPublicSiteData` to share public settings, menus, logo, page, and page-block loading.
- Added `PublicContentPageController` for MySQL-backed content pages.
- Converted `/about` to Blade with MySQL page blocks and public staff records.
- Converted `/academics` to Blade with MySQL page blocks and published programme records.
- Converted `/admissions` to Blade with MySQL page blocks, downloads, FAQs, and supplied media.
- Added `POST /admissions/enquiries` with validation, throttling, request metadata hashing, and MySQL persistence into `admission_enquiries`.
- Enriched deterministic seed data for About, Academics, Admissions, footer menus, and staff cards.
- Added tests that mutate MySQL records before rendering About, Academics, and Admissions pages to prove database-backed display.
- Added a public form persistence test for admission enquiries.

### Verification After More Public Screens

- `php artisan migrate:fresh --seed` passed against MySQL.
- `php artisan test` passed: 14 tests, 73 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Live HTTP checks passed for `/about`, `/academics`, and `/admissions` on `http://127.0.0.1:8020`.
- `php artisan route:list --except-vendor` lists 34 routes, including `POST /admissions/enquiries`.

### Superseded Remaining Gaps After This Pass

- Remaining public routes still rendering static prototype HTML: `/news`, `/news/young-learners-shine`, `/events`, `/events/parent-orientation`, `/gallery`, `/downloads`, `/contact`, `/faq`, and `/privacy`.
- Filament remains uninstalled; admin routes are still static prototype-baseline screens.
- Authentication and authorization for `/admin/*` are not implemented yet.
- The installed framework is Laravel 12.x and local PHP is 8.2.31, while the contract specifies Laravel 13.x and PHP 8.3+.
- Git metadata is present; a milestone commit is still pending.

### Continued: News And Events

- Converted `/news` to a Blade listing driven by published MySQL `posts`.
- Converted `/news/{post:slug}` to a Blade detail page with publication-scope enforcement.
- Converted `/events` to a Blade listing driven by published MySQL `events`.
- Converted `/events/{event:slug}` to a Blade detail page with publication-scope enforcement.
- Added seeded post and event records so public listing pages render representative MySQL content.
- Added Eloquent relationships for event featured media and programme downloads.
- Updated home page links to use slug-based news and event detail routes.
- Added tests that mutate MySQL post/event records and assert both listing and detail pages render those changed values.

### Verification After News And Events

- `php artisan migrate:fresh --seed` passed against MySQL.
- `php artisan test` passed: 16 tests, 87 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Live HTTP checks passed for `/news`, `/news/young-learners-shine`, `/events`, and `/events/parent-orientation` on `http://127.0.0.1:8020`.

### Superseded Remaining Gaps After News And Events

- Remaining public routes still rendering static prototype HTML: `/gallery`, `/downloads`, `/contact`, `/faq`, and `/privacy`.
- Filament remains uninstalled; admin routes are still static prototype-baseline screens.
- Authentication and authorization for `/admin/*` are not implemented yet.
- The installed framework is Laravel 12.x and local PHP is 8.2.31, while the contract specifies Laravel 13.x and PHP 8.3+.
- Git metadata is present; a milestone commit is still pending.

### Continued: Public Routes Complete

- Converted `/gallery` to a Blade page driven by published MySQL galleries and gallery items, with media filtered through public consent/visibility rules.
- Converted `/downloads` to a Blade listing driven by published MySQL downloads, categories, and file metadata.
- Converted `/contact` to a Blade page driven by public MySQL settings and page blocks.
- Added `POST /contact/messages` with validation, throttling, request metadata hashing, and MySQL persistence into `contact_messages`.
- Converted `/faq` to a Blade page driven by published MySQL FAQ records.
- Converted `/privacy` to a Blade page driven by MySQL page blocks.
- Added deterministic seed data for additional downloads, FAQs, and privacy/safeguarding page sections.
- Added tests for gallery, downloads, FAQ, privacy, and persisted contact messages.

### Verification After Public Routes Complete

- `php artisan migrate:fresh --seed` passed against MySQL.
- `php artisan test` passed: 18 tests, 105 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Live HTTP checks passed for `/gallery`, `/downloads`, `/contact`, `/faq`, and `/privacy` on `http://127.0.0.1:8020`.
- `php artisan route:list --except-vendor` lists 35 routes, including public admission and contact form POST endpoints.
- Project prohibition scan found no active SQLite/PostgreSQL configuration outside binding documentation that describes the prohibition.

### Remaining Gaps After Public Routes Complete

- Filament remains uninstalled; admin routes are still static prototype-baseline screens.
- Authentication and authorization for `/admin/*` are not implemented yet.
- Admin dashboard metrics and CRUD screens are not yet connected to MySQL-backed Filament resources.
- Visual parity report is still pending after admin implementation.
- The installed framework is Laravel 12.x and local PHP is 8.2.31, while the contract specifies Laravel 13.x and PHP 8.3+.
- Git metadata is present; a milestone commit is still pending.

### Continued: Filament Admin Slice

- Installed Filament `v5.7.5` and registered the generated admin panel provider.
- Removed the old static `/admin` prototype route group so Filament owns `/admin`.
- Configured the panel brand, favicon, and core color tokens using the approved school identity assets.
- Updated `User` to implement Filament panel access rules backed by MySQL roles and active-user state.
- Added the MySQL-backed `SchoolOverview` dashboard widget for pages, posts, events, enquiries, contact messages, and public media.
- Added Filament resources for Pages, News Posts, Events, Admission Enquiries, and Contact Messages.
- Reworked generated admin forms and tables so key status, category, scheduling, assignment, and publication controls are structured rather than raw text/ID fields.
- Kept admission enquiries and contact messages as review workflows without list-page create actions; public forms remain the source of those records.
- Updated public layout staff-portal link to the Filament login route.
- Updated feature tests to verify Filament login, dashboard access, dashboard metric values from MySQL, and resource list pages backed by seeded MySQL records.

### Verification After Filament Admin Slice

- `php artisan migrate:fresh --seed` passed against MySQL.
- `php artisan test` passed: 18 tests, 117 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- `php artisan about` confirms: Database `mysql`, Session `database`, Cache `database`, Queue `database`, Filament `v5.7.5`, Livewire `v4.3.5`.
- Active runtime/test configuration scan over `.env`, `.env.example`, `phpunit.xml`, `composer.json`, `package.json`, `config`, `bootstrap`, `app`, `routes`, `tests`, and `database` found no active SQLite/PostgreSQL references.

### Remaining Gaps After Filament Admin Slice

- Remaining admin resources still need implementation: page blocks, gallery, downloads, staff, programmes, media, users, roles, settings, redirects, and audit logs.
- Filament screens still need deeper visual customization to match the approved admin prototype beyond brand/color/route/resource structure.
- Policies and fine-grained authorization need to be added around resource actions.
- Visual parity report is still pending after the broader admin implementation.
- The installed framework is Laravel 12.x and local PHP is 8.2.31, while the contract specifies Laravel 13.x and PHP 8.3+.
- Git metadata is present; a milestone commit is still pending.

### Continued: Admin UI First Reset

- Re-prioritized the visible admin panel to match the approved prototype before deeper function wiring.
- Moved the installed Filament panel from `/admin` to `/admin-core` so the internal MySQL-backed resource workbench remains available.
- Restored all visible approved admin prototype routes under `/admin`, including login, dashboard, pages, editors, news, events, gallery, downloads, staff, programmes, admissions, messages, media, users, roles, settings, and audit log.
- Updated public staff-portal links to `/admin/login`.
- Kept the approved prototype CSS/JS/assets as the active visible admin visual source.
- Updated admin tests so visible `/admin` asserts prototype UI parity while `/admin-core` still verifies internal Filament/MySQL resource access.

### Verification After Admin UI First Reset

- `php artisan route:list --except-vendor` lists visible prototype admin routes at `/admin/*` and internal Filament routes at `/admin-core/*`.
- Focused admin tests passed: 3 tests, 49 assertions.

### Remaining Gaps After Admin UI First Reset

- Visible `/admin` screens are UI-first and must now be wired to MySQL-backed actions and resources without visual drift.
- Internal Filament resources remain available at `/admin-core` but are not the visible final admin experience.
- Authentication, policies, form actions, CRUD persistence, and audit workflows need to be wired into the visible admin UI.
- Visual parity report is still pending after functional wiring.

### Continued: Staff Portal Split

- Separated the public "Staff portal" link from administrator login.
- Added `/staff-portal/login` for a staff-specific login surface.
- Added `/staff-portal` for a staff dashboard focused on calendar items, resource downloads, news contributions, and staff directory access.
- Kept `/admin` for administration UI/UX and `/admin-core` for the internal Filament workbench.
- Seeded a local teacher contributor account at `local.teacher@example.test`.
- Added tests proving the staff portal uses a different shell from admin and does not expose admin management navigation.

### Verification After Staff Portal Split

- `php artisan route:list --except-vendor` lists `/staff-portal/login`, `/staff-portal`, `/admin/*`, and `/admin-core/*` separately.
- Focused staff/admin route tests passed.

### Remaining Gaps After Staff Portal Split

- Staff portal authentication is still UI-first and needs real login/session handling.
- Staff portal contribution actions need to be wired to MySQL-backed workflows.
- Administrator functions must remain under admin-specific authorization, separate from teacher contributor access.

### Continued: Portal Authentication Split

- Added shared portal authentication handling for admin and staff login forms.
- Added role middleware so `/admin/*` requires administrator-oriented roles and `/staff-portal` requires `teacher-contributor`.
- Converted the staff login UI to post credentials to `/staff-portal/login`.
- Converted the approved admin prototype login UI to post credentials to `/admin/login` while preserving the prototype layout.
- Added logout routes for `/admin/logout` and `/staff-portal/logout`.
- Updated tests to verify unauthenticated redirects, successful staff/admin login, and wrong-role 403 protection.

### Verification After Portal Authentication Split

- Focused staff/admin authentication tests passed: 5 tests, 85 assertions.
- Full `php artisan test` passed: 20 tests, 174 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Portal Authentication Split

- Staff portal contribution actions still need MySQL-backed workflow implementation.
- Admin prototype screens are protected and visible, but CRUD actions still need wiring into the approved UI.
- Policies and audit logging need to be added for portal actions.

### Continued: Visible Admin MySQL Dashboard And Content Lists

- Kept the visible approved admin prototype shell under `/admin`.
- Replaced hard-coded dashboard metric values with MySQL-backed counts for published pages, upcoming events, new enquiries, contact messages, and public media.
- Replaced static dashboard "content requiring attention" rows with MySQL-backed managed content rows.
- Replaced static dashboard recent activity rows with MySQL-backed news, event, admission, and contact activity.
- Replaced visible `/admin/pages` table rows with MySQL-backed `pages` records.
- Replaced visible `/admin/news` table rows with MySQL-backed `posts` records.
- Replaced visible `/admin/events` table rows with MySQL-backed `events` records.
- Replaced visible `/admin/admissions` table rows with MySQL-backed admission enquiry records.
- Replaced visible `/admin/contact-messages` table rows with MySQL-backed contact message records.
- Replaced visible `/admin/downloads` table rows with MySQL-backed downloads and media metadata.
- Replaced visible `/admin/staff` table rows with MySQL-backed staff and department records.
- Replaced visible `/admin/programmes` table rows with MySQL-backed programme and department records.
- Replaced visible `/admin/media` cards with MySQL-backed media records.
- Replaced visible `/admin/users` table rows with MySQL-backed users and role labels.
- Replaced visible `/admin/settings` key fields with MySQL-backed site settings.
- Replaced visible `/admin/audit-log` table rows with MySQL-backed audit records.
- Added tests that mutate MySQL records before requesting visible admin pages to prove the prototype UI is rendering database values.

### Verification After Visible Admin MySQL Dashboard And Content Lists

- Focused visible admin route test passed after MySQL table injection: 1 test, 47 assertions.

### Remaining Gaps After Visible Admin MySQL Dashboard And Content Lists

- Visible admin forms still need create/edit/update/delete persistence.
- Remaining visible admin index screens need MySQL-backed rows: gallery and roles/permissions matrix.
- Policies and audit logging still need to wrap write actions.

### Continued: Visible Admin Gallery And Roles MySQL Wiring

- Replaced visible `/admin/gallery` prototype album rows with MySQL-backed galleries, categories, item counts, status labels, dates, and public/editor actions.
- Replaced visible `/admin/roles` static permission tabs with MySQL-backed roles, assigned-user counts, descriptions, and grouped permission checkboxes.
- Extended the visible admin feature test to mutate gallery, role, and permission records before requesting the prototype routes.

### Verification After Visible Admin Gallery And Roles MySQL Wiring

- Focused visible admin route test passed against MySQL: 1 test, 56 assertions.
- Full `php artisan test` passed: 20 tests, 214 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Visible Admin Gallery And Roles MySQL Wiring

- Visible admin forms still need create/edit/update/delete persistence.
- Policies and audit logging still need to wrap write actions.
- Staff portal contribution actions still need MySQL-backed workflow implementation.

### Continued: Visible Admin Page Editor Persistence

- Converted the approved `/admin/pages/editor` prototype screen into a real Laravel update workflow while preserving the prototype layout.
- Added a MySQL-backed page editor injection that loads the selected page, featured media, page blocks, publication state, search visibility, and SEO fields.
- Added `PATCH /admin/pages/{page}` for visible admin page updates.
- Added `PagePolicy` checks for `pages.update` and `pages.publish`.
- Added page update audit logging with old and new values.
- Updated visible admin page-list edit links to open the editor for the selected page slug.

### Verification After Visible Admin Page Editor Persistence

- Focused page editor test passed against MySQL: 1 test, 14 assertions.
- Full `php artisan test` passed: 21 tests, 228 assertions.
- `./vendor/bin/pint --test` passed after import ordering cleanup.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Visible Admin Page Editor Persistence

- Page block create/edit/delete controls are still visual only.
- Other visible admin editors still need create/edit/update/delete persistence.
- Policies and audit logging need to be expanded to the other write workflows.
- Staff portal contribution actions still need MySQL-backed workflow implementation.

### Continued: Visible Admin News Editor Persistence

- Converted the approved `/admin/news/editor` prototype screen into a real Laravel update workflow while preserving the admin shell and editorial layout.
- Added a MySQL-backed news editor injection that loads the selected post, category, tags, featured media, publication state, summary, and story body.
- Added `PATCH /admin/news/{post}` for visible admin news story updates.
- Added `PostPolicy` checks through the existing `news.manage` permission.
- Added tag normalization/sync from the visible editor and audit logging with old and new values.
- Updated visible admin news-list edit links to open the editor for the selected story slug.

### Verification After Visible Admin News Editor Persistence

- Focused news editor test passed against MySQL: 1 test, 16 assertions.
- Full `php artisan test` passed: 22 tests, 244 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Visible Admin News Editor Persistence

- News create flow is still not wired; current visible editor updates an existing story.
- Other visible admin editors still need create/edit/update/delete persistence.
- Page block create/edit/delete controls are still visual only.
- Staff portal contribution actions still need MySQL-backed workflow implementation.

### Continued: Visible Admin Event Editor Persistence

- Converted the approved `/admin/events/editor` prototype screen into a real Laravel update workflow while preserving the admin shell and event layout.
- Added a MySQL-backed event editor injection that loads the selected event, category, featured media, date range, venue, summary, programme/body, and publication status.
- Added `PATCH /admin/events/{event}` for visible admin event updates.
- Added `EventPolicy` checks through the existing `events.manage` permission.
- Added event update audit logging with old and new values.
- Updated visible admin event-list edit links to open the editor for the selected event slug.

### Verification After Visible Admin Event Editor Persistence

- Focused event editor test passed against MySQL: 1 test, 14 assertions.
- Full `php artisan test` passed: 23 tests, 258 assertions.
- `./vendor/bin/pint --test` passed after provider formatting cleanup.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Visible Admin Event Editor Persistence

- Event create flow is still not wired; current visible editor updates an existing event.
- Gallery editor, staff/programme editors, downloads, users, roles, settings, admissions, and messages still need write workflows.
- Page block create/edit/delete controls are still visual only.
- Staff portal contribution actions still need MySQL-backed workflow implementation.

### Continued: Visible Admin Gallery Editor Persistence

- Converted the approved `/admin/gallery/editor` prototype screen into a real Laravel update workflow while preserving the admin shell and album layout.
- Added a MySQL-backed gallery editor injection that loads the selected album, status, real gallery items, captions, media metadata, and consent/approval hints.
- Added `PATCH /admin/gallery/{gallery}` for visible admin gallery updates.
- Added `GalleryPolicy` checks through a new `galleries.manage` permission seeded into MySQL.
- Added gallery update audit logging with old and new values.
- Updated visible admin gallery-list edit links to open the editor for the selected album slug.

### Verification After Visible Admin Gallery Editor Persistence

- Focused gallery editor test passed against MySQL: 1 test, 16 assertions.
- Full `php artisan test` passed: 24 tests, 274 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Visible Admin Gallery Editor Persistence

- Gallery image add/remove/reorder controls are still visual only.
- Create flows for news, events, and galleries are still not wired; current editors update existing records.
- Staff/programme editors, downloads, users, roles, settings, admissions, and messages still need write workflows.
- Page block create/edit/delete controls are still visual only.
- Staff portal contribution actions still need MySQL-backed workflow implementation.

### Continued: Visible Admin Settings Persistence

- Converted the approved `/admin/settings` prototype save button and editable fields into a real Laravel update workflow while preserving the tabbed admin layout.
- Added a MySQL-backed settings form for school name, motto, default SEO title, default SEO description, public email, telephone, and address.
- Added `PATCH /admin/settings` for visible admin settings updates.
- Added `SiteSettingPolicy` checks through the existing `settings.manage` permission.
- Added site settings audit logging with old and new values.

### Verification After Visible Admin Settings Persistence

- Focused settings test passed against MySQL: 1 test, 22 assertions.
- Full `php artisan test` passed: 25 tests, 296 assertions.
- `./vendor/bin/pint --test` passed after unused import cleanup.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Visible Admin Settings Persistence

- Gallery image add/remove/reorder controls are still visual only.
- Create flows for news, events, and galleries are still not wired; current editors update existing records.
- Staff/programme editors, downloads, users, roles, admissions, and messages still need write workflows.
- Page block create/edit/delete controls are still visual only.
- Staff portal contribution actions still need MySQL-backed workflow implementation.

### Continued: Visible Admin Enquiry And Message Status Workflows

- Converted the approved icon-button row actions on `/admin/admissions` into real MySQL-backed status update forms.
- Converted the approved icon-button row actions on `/admin/contact-messages` into real MySQL-backed status update forms.
- Added `PATCH /admin/admissions/{admissionEnquiry}/status` for admission enquiry follow-up states.
- Added `PATCH /admin/contact-messages/{contactMessage}/status` for contact message assignment/response states.
- Added `AdmissionEnquiryPolicy` and `ContactMessagePolicy` checks through new `admissions.manage` and `contacts.manage` permissions seeded into MySQL.
- Added audit logging for admission enquiry and contact message status changes.

### Verification After Visible Admin Enquiry And Message Status Workflows

- Focused status workflow test passed against MySQL: 1 test, 24 assertions.
- Full `php artisan test` passed: 26 tests, 320 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Visible Admin Enquiry And Message Status Workflows

- Admission/message detail views and internal notes remain to be built.
- Gallery image add/remove/reorder controls are still visual only.
- Create flows for news, events, and galleries are still not wired; current editors update existing records.
- Staff/programme editors, downloads, users, and roles still need write workflows.
- Page block create/edit/delete controls are still visual only.
- Staff portal contribution actions still need MySQL-backed workflow implementation.

### Continued: Visible Admin Download Status Workflow

- Converted the approved icon-button row actions on `/admin/downloads` into real MySQL-backed status update forms while preserving the table layout.
- Added `PATCH /admin/downloads/{download}/status` for download publication-state changes.
- Added `DownloadPolicy` checks through the existing `downloads.manage` permission.
- Added audit logging for download status changes with old and new values.

### Verification After Visible Admin Download Status Workflow

- Focused download status test passed against MySQL: 1 test, 15 assertions.
- Full `php artisan test` passed: 27 tests, 335 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Visible Admin Download Status Workflow

- Download upload/create/edit metadata flow is still not wired.
- Admission/message detail views and internal notes remain to be built.
- Gallery image add/remove/reorder controls are still visual only.
- Create flows for news, events, and galleries are still not wired; current editors update existing records.
- Staff/programme editors, users, and roles still need write workflows.
- Page block create/edit/delete controls are still visual only.
- Staff portal contribution actions still need MySQL-backed workflow implementation.

### Continued: Visible Admin Staff And Programme Workflows

- Converted the approved icon-button row actions on `/admin/staff` into real MySQL-backed visibility update forms.
- Converted the approved icon-button row actions on `/admin/programmes` into real MySQL-backed status update forms.
- Added `PATCH /admin/staff/{staffMember}/visibility` for public/private/inactive staff visibility changes.
- Added `PATCH /admin/programmes/{programme}/status` for programme publication-state changes.
- Added `StaffMemberPolicy` and `ProgrammePolicy` checks through new `staff.manage` and `programmes.manage` permissions seeded into MySQL.
- Added audit logging for staff visibility and programme status changes.

### Verification After Visible Admin Staff And Programme Workflows

- Focused staff/programme workflow test passed against MySQL: 1 test, 24 assertions.
- Full `php artisan test` passed: 28 tests, 359 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Visible Admin Staff And Programme Workflows

- Staff/programme create/edit metadata screens are still not wired.
- Download upload/create/edit metadata flow is still not wired.
- Admission/message detail views and internal notes remain to be built.
- Gallery image add/remove/reorder controls are still visual only.
- Create flows for news, events, and galleries are still not wired; current editors update existing records.
- Users and roles still need write workflows.
- Page block create/edit/delete controls are still visual only.
- Staff portal contribution actions still need MySQL-backed workflow implementation.

### Continued: Visible Admin User Status Workflow

- Converted the approved icon-button row actions on `/admin/users` into real MySQL-backed account status update forms.
- Added `PATCH /admin/users/{user}/status` for enabling/disabling user accounts.
- Added `UserPolicy` checks through the existing `users.manage` permission.
- Added a self-disable guard so an authenticated admin cannot disable their own active account from the visible users screen.
- Added audit logging for user status changes with old and new values.

### Verification After Visible Admin User Status Workflow

- Focused user status workflow test passed against MySQL: 1 test, 14 assertions.
- Full `php artisan test` passed: 29 tests, 373 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Visible Admin User Status Workflow

- User invite/create/edit role assignment workflow is still not wired.
- Roles permission save workflow is still not wired.
- Staff/programme create/edit metadata screens are still not wired.
- Download upload/create/edit metadata flow is still not wired.
- Admission/message detail views and internal notes remain to be built.
- Gallery image add/remove/reorder controls are still visual only.
- Page block create/edit/delete controls are still visual only.
- Staff portal contribution actions still need MySQL-backed workflow implementation.

### Continued: Visible Admin Role Permission Workflow

- Converted the MySQL-rendered `/admin/roles` permission matrix into real role-specific permission sync forms.
- Added `PATCH /admin/roles/{role}/permissions` for syncing `permission_role` records.
- Added `RolePolicy` checks through a new `roles.manage` permission seeded into MySQL.
- Added audit logging for role permission changes with old and new permission slug lists.

### Verification After Visible Admin Role Permission Workflow

- Focused role permission workflow test passed against MySQL: 1 test, 15 assertions.
- Full `php artisan test` passed: 30 tests, 388 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Visible Admin Role Permission Workflow

- User invite/create/edit role assignment workflow is still not wired.
- Staff/programme create/edit metadata screens are still not wired.
- Download upload/create/edit metadata flow is still not wired.
- Admission/message detail views and internal notes remain to be built.
- Gallery image add/remove/reorder controls are still visual only.
- Page block create/edit/delete controls are still visual only.
- Staff portal contribution actions still need MySQL-backed workflow implementation.

### Continued: Staff Portal Contribution Workflow

- Added a staff-only contribution form to `/staff-portal` without exposing admin navigation or admin controls.
- Added `POST /staff-portal/contributions` behind the existing `teacher-contributor` staff portal middleware.
- Staff submissions create MySQL-backed `posts` records in `review` status with the current staff user as author/creator/updater.
- Staff review contributions remain hidden from public `/news` until an administrator publishes them.
- Added audit logging for staff contribution submissions.
- Updated the staff dashboard to show the signed-in staff member's pending draft/review contributions from MySQL.

### Verification After Staff Portal Contribution Workflow

- Focused staff contribution test passed against MySQL: 1 test, 11 assertions.
- Full `php artisan test` passed: 31 tests, 400 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Staff Portal Contribution Workflow

- User invite/create/edit role assignment workflow is still not wired.
- Staff/programme create/edit metadata screens are still not wired.
- Download upload/create/edit metadata flow is still not wired.
- Admission/message detail views and internal notes remain to be built.
- Gallery image add/remove/reorder controls are still visual only.
- Page block create/edit/delete controls are still visual only.

### Continued: Visible Admin Page Block Visibility Workflow

- Converted page-block controls inside the approved `/admin/pages/editor` prototype into real MySQL-backed visibility update forms.
- Added `PATCH /admin/page-blocks/{pageBlock}/visibility` for enabling/disabling page blocks.
- Reused the existing `pages.update` policy through the block's owning page.
- Added audit logging for page block visibility changes.
- Confirmed public pages respect hidden blocks through the existing `PageBlock::visible()` scope.

### Verification After Visible Admin Page Block Visibility Workflow

- Focused page block visibility test passed against MySQL: 1 test, 14 assertions.
- Full `php artisan test` passed: 32 tests, 414 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Visible Admin Page Block Visibility Workflow

- Full page block create/edit/delete/reorder remains to be wired.
- User invite/create/edit role assignment workflow is still not wired.
- Staff/programme create/edit metadata screens are still not wired.
- Download upload/create/edit metadata flow is still not wired.
- Admission/message detail views and internal notes remain to be built.
- Gallery image add/remove/reorder controls are still visual only.

### Continued: Visible Admin Media Publication Workflow

- Converted the `/admin/media` prototype grid action buttons into real MySQL-backed publication forms.
- Added `PATCH /admin/media/{media}/publication` for approving and restricting media records.
- Added `MediaPolicy` authorization through the seeded `media.approve` permission.
- Approval sets media public, confirms required consent, clears publication restrictions, and keeps binary files in storage rather than MySQL.
- Restriction makes media private, records the publication restriction, and preserves the original media metadata.
- Added audit logging for media publication updates with old and new safeguarding values.

### Verification After Visible Admin Media Publication Workflow

- Focused media publication workflow test passed against MySQL: 1 test, 23 assertions.
- Full `php artisan test` passed: 33 tests, 437 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Visible Admin Media Publication Workflow

- Media upload, metadata editing, replacement, and deletion workflows remain to be wired.
- Full page block create/edit/delete/reorder remains to be wired.
- User invite/create/edit role assignment workflow is still not wired.
- Staff/programme create/edit metadata screens are still not wired.
- Download upload/create/edit metadata flow is still not wired.
- Admission/message detail views and internal notes remain to be built.
- Gallery image add/remove/reorder controls are still visual only.

### Continued: Visible Admin User Role Workflow

- Converted the `/admin/users` Role column into MySQL-backed role assignment forms while preserving the approved table layout and row action controls.
- Added `PATCH /admin/users/{user}/roles` to sync the `role_user` pivot table from selected MySQL roles.
- Added `UserPolicy::updateRoles` authorization through the existing `users.manage` permission.
- Blocked administrators from changing their own roles through the visible admin workflow.
- Added audit logging for user role changes with old and new role slug lists.

### Verification After Visible Admin User Role Workflow

- Focused user role workflow test passed against MySQL: 1 test, 15 assertions.
- Full `php artisan test` passed: 34 tests, 452 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Visible Admin User Role Workflow

- User invite/create/edit profile workflow is still not wired.
- Media upload, metadata editing, replacement, and deletion workflows remain to be wired.
- Full page block create/edit/delete/reorder remains to be wired.
- Staff/programme create/edit metadata screens are still not wired.
- Download upload/create/edit metadata flow is still not wired.
- Admission/message detail views and internal notes remain to be built.
- Gallery image add/remove/reorder controls are still visual only.

### Continued: Visible Admin Staff And Programme Metadata Workflow

- Converted `/admin/staff` table metadata fields into MySQL-backed inline edit controls for name, role, department, and display order.
- Added `PATCH /admin/staff/{staffMember}/metadata` with `staff.manage` policy enforcement and audit logging.
- Converted `/admin/programmes` table metadata fields into MySQL-backed inline edit controls for name, level, and summary.
- Added `PATCH /admin/programmes/{programme}/metadata` with `programmes.manage` policy enforcement, updater tracking, and audit logging.
- Kept existing visibility/status controls in the approved row action pattern.

### Continued: Visible Admin Gallery Item Workflow

- Extended `/admin/gallery/editor` so gallery item captions, featured state, and display order are editable in the existing gallery save form.
- Extended `AdminGalleryController::update` to validate and persist gallery item changes to `gallery_items`.
- Added old/new gallery item snapshots to the existing gallery audit log entry.

### Verification After Staff/Programme Metadata And Gallery Item Workflows

- Focused staff/programme metadata workflow test passed against MySQL: 1 test, 33 assertions.
- Focused gallery editor workflow test passed against MySQL: 1 test, 23 assertions.
- Full `php artisan test` passed: 35 tests, 492 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Staff/Programme Metadata And Gallery Item Workflows

- User invite/create/edit profile workflow is still not wired.
- Media upload, metadata editing, replacement, and deletion workflows remain to be wired.
- Full page block create/edit/delete/reorder remains to be wired.
- Download upload/create/edit metadata flow is still not wired.
- Admission/message detail views and internal notes remain to be built.
- Gallery image add/remove/delete controls remain to be wired.

### Continued: Visible Admin Download Metadata Workflow

- Converted `/admin/downloads` document title, description, category, and version fields into MySQL-backed inline edit controls.
- Added `PATCH /admin/downloads/{download}/metadata` with `downloads.manage` policy enforcement.
- Added unique slug regeneration when download titles change.
- Preserved the approved table/action shape: preview, save metadata, then publish/archive.
- Added audit logging for download metadata changes with old and new field snapshots.

### Continued: Visible Admin Enquiry And Message Notes Workflow

- Added internal note forms to `/admin/admissions` and `/admin/contact-messages` rows.
- Added `POST /admin/admissions/{admissionEnquiry}/notes` and `POST /admin/contact-messages/{contactMessage}/notes`.
- Saved notes to the existing MySQL `admission_enquiry_notes` and `contact_message_notes` tables.
- Marked notes sensitive by default and assigned unassigned records to the acting administrator when a note is added.
- Added audit logging for note creation without exposing note body in the audit summary.
- Displayed the latest internal note snippet from MySQL on the visible admin lists.

### Verification After Download Metadata And Internal Notes Workflows

- Focused download workflow tests passed against MySQL: 2 tests, 34 assertions.
- Focused enquiry/message notes workflow test passed against MySQL: 1 test, 24 assertions.
- Full `php artisan test` passed: 37 tests, 535 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Download Metadata And Internal Notes Workflows

- User invite/create/edit profile workflow is still not wired.
- Media upload, metadata editing, replacement, and deletion workflows remain to be wired.
- Full page block create/edit/delete/reorder remains to be wired.
- Download binary upload/replacement flow remains to be wired.
- Admission/message full detail views remain to be built.
- Gallery image add/remove/delete controls remain to be wired.

### Continued: Visible Admin User Invite And Profile Workflow

- Converted the `/admin/users` invite action into a MySQL-backed user creation form on the approved users screen.
- Added `POST /admin/users` with `users.manage` policy enforcement.
- Created active users with hashed temporary passwords and optional role assignment in the `role_user` pivot table.
- Converted user name/email cells into profile edit controls.
- Added `PATCH /admin/users/{user}/profile` with audit logging for account profile changes.
- Preserved separate row actions for profile save, role save, and enable/disable controls.

### Continued: Visible Admin Media Metadata Workflow

- Converted `/admin/media` media names, alt text, captions, and credits into MySQL-backed inline metadata controls.
- Added `PATCH /admin/media/{media}/metadata` with `media.approve` policy enforcement.
- Preserved the existing approve/restrict publication workflow and added a separate metadata-save action.
- Added audit logging for media metadata changes without storing binary file content in MySQL.

### Verification After User Invite/Profile And Media Metadata Workflows

- Focused user workflow tests passed against MySQL: 3 tests, 55 assertions.
- Focused media workflow tests passed against MySQL: 2 tests, 43 assertions.
- Full `php artisan test` passed after formatting cleanup: 39 tests, 581 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After User Invite/Profile And Media Metadata Workflows

- Media binary upload, replacement, and deletion workflows remain to be wired.
- Full page block create/edit/delete/reorder remains to be wired.
- Download binary upload/replacement flow remains to be wired.
- Admission/message full detail views remain to be built.
- Gallery image add/remove/delete controls remain to be wired.

### Continued: Visible Admin Page Block Builder Workflow

- Converted the approved collapsed page-block cards in `/admin/pages/editor` into complete MySQL-backed block editor controls.
- Added block creation, common content and structured JSON settings editing, approved-image selection, publication windows, and enabled-state controls.
- Added transactional move-up/move-down ordering, duplication, and deletion workflows while preserving deterministic `sort_order` values.
- Added `POST /admin/pages/{page}/blocks`, `PATCH /admin/page-blocks/{pageBlock}`, `PATCH /admin/page-blocks/{pageBlock}/order`, `POST /admin/page-blocks/{pageBlock}/duplicate`, and `DELETE /admin/page-blocks/{pageBlock}`.
- Enforced the existing `pages.update` policy on every block mutation and added audit records for create, update, reorder, duplicate, and delete actions.
- Restricted public page and page-block media eager loading to `Media::publiclyVisible()` so private, restricted, or unconfirmed-consent media cannot be exposed through an existing page association.
- Added visible validation/status feedback to the approved page editor without replacing its prototype layout.

### Verification After Page Block Builder Workflow

- Focused visible-admin page workflow tests passed against MySQL: 3 tests, 70 assertions.
- Full `php artisan test` passed against MySQL: 40 tests, 623 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed with Vite 7.3.6.
- `php artisan about --only=environment,drivers` confirmed the MySQL database driver and database-backed cache, queue, and session drivers.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.

### Remaining Gaps After Page Block Builder Workflow

- Media binary upload, replacement, and deletion workflows remain to be wired.
- Download binary upload/replacement flow remains to be wired.
- Admission/message full detail views remain to be built.
- Gallery image add/remove/delete controls remain to be wired.

### Continued: Secure Visible Admin Media Binary Workflow

- Converted the approved `/admin/media` upload action into a real multipart upload panel without replacing the prototype media-grid design.
- Added private-first managed storage under randomized `media/YYYY/MM` paths while retaining authoritative file metadata in MySQL.
- Added real MIME and extension validation for JPEG, PNG, WebP, and PDF files, with SVG uploads rejected.
- Added image decoding and GD re-encoding before storage to strip embedded metadata, plus persisted dimensions, sanitized file size, and SHA-256 checksum values.
- Added authenticated media preview responses so private review assets are visible to authorized administrators without receiving a public URL.
- Added explicit publication moves from private to public Laravel storage and restriction moves back to private storage.
- Changed consent publication handling so approval cannot silently confirm consent; media requiring consent must have a confirmed reference before publication.
- Added replacement uploads that remove the previous managed binary, clear generated variants, reset child-media consent confirmation, and return the record to private review.
- Added recoverable soft deletion that removes managed public exposure, keeps the original binary on private storage, and deletes generated public variants.
- Enforced protected-asset policy checks so the exact school logo cannot be replaced, restricted, or deleted through routine media actions.
- Replaced direct public path construction with `Media::publicUrl()` and applied public media scopes to page, post, event, programme, staff, gallery, and download queries.
- Added `POST /admin/media`, `GET /admin/media/{media}/preview`, `PATCH /admin/media/{media}/replacement`, and `DELETE /admin/media/{media}`.
- Added the Laravel public storage link at `public/storage` for approved managed assets.

### Verification After Secure Media Binary Workflow

- Focused visible-admin media workflow tests passed against MySQL: 3 tests, 142 assertions.
- Full `php artisan test` passed against MySQL: 41 tests, 722 assertions.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed with Vite 7.3.6.
- `git diff --check` passed.
- Active runtime/test configuration scan found no SQLite/PostgreSQL references.
- Live `/admin/media` returned the expected HTTP 302 redirect to `/admin/login` for an unauthenticated request.

### Remaining Gaps After Secure Media Binary Workflow

- Download binary upload/replacement flow remains to be wired.
- Admission/message full detail views remain to be built.
- Gallery image add/remove/delete controls remain to be wired.

### Continued: Secure Visible Admin Download Binary Workflow

- Converted the approved `/admin/downloads` upload action into a real multipart PDF upload panel while preserving the prototype table and action layout.
- Added `POST /admin/downloads` to create a draft download and its private media record transactionally in MySQL.
- Restricted download uploads and replacements to validated PDF files up to 20 MB, with real MIME inspection and randomized managed storage names.
- Added authoritative MySQL metadata for original and stored names, storage disk and path, MIME type, extension, size, checksum, publication restrictions, uploader, and download ownership.
- Added authenticated previews for private download documents through the existing media preview route.
- Extended publication so a managed PDF moves from private to public Laravel storage only when the download is published, and moves back to private storage when its final published download is withdrawn.
- Added `PATCH /admin/downloads/{download}/file` for replacement uploads; replacing a published document moves the superseded binary back to private storage and returns the download to draft review.
- Added editable publication dates alongside the existing title, description, category, and version controls.
- Enforced `downloads.manage` for create and replacement actions and added audit records for upload, creation, publication changes, and file replacement.
- Kept public `/downloads` queries restricted to published downloads with public, unrestricted media; draft and replaced documents do not receive public links.

### Verification After Secure Download Binary Workflow

- Focused visible-admin download workflow tests passed against MySQL: 3 tests, 130 assertions.
- Full `php artisan test` passed against MySQL: 42 tests, 818 assertions.
- `php artisan migrate:fresh --seed` completed successfully against the local MySQL development schema.
- `php artisan route:list --name=admin.downloads` confirmed all 5 download routes.
- `./vendor/bin/pint --test` passed.
- `npm run build` passed with Vite 7.3.6.
- `git diff --check` passed.
- `php artisan about --only=environment,drivers` confirmed MySQL plus database-backed cache, queue, and session drivers.
- Active runtime/test configuration and file scans found no SQLite or PostgreSQL references or `database.sqlite` file.

### Remaining Gaps After Secure Download Binary Workflow

- Admission and contact-message full detail views remain to be built.
- Gallery image add/remove/delete controls remain to be wired.

### Continued: Protected Admission And Contact Detail Workflows

- Added distinct admission-enquiry and contact-message detail workspaces using the approved admin shell, exact logo, navigation, palette, panels, and responsive behavior.
- Displayed complete MySQL submission fields, assignee, consent state, response timestamps, and every internal note with its staff author while excluding source IP and user-agent hashes.
- Added policy-gated list and detail access through `admissions.view` and `contacts.view`; admin-role membership alone no longer exposes private records.
- Added explicit MySQL-backed assignment controls restricted to active users with the matching manage permission.
- Preserved deliberate assignments when quick status actions advance a record and retained automatic self-assignment only for unassigned work.
- Changed list preview icons into real protected detail links while preserving existing quick status and note actions.
- Added permission-gated CSV exports using `admissions.export` and the new `contacts.export` permission.
- Added streamed, bounded-memory exports that exclude request hashes and internal notes, neutralize spreadsheet-formula prefixes, and audit each export with its record count.
- Added assignment audit records and preserved existing status and note audit trails.
- Removed private guardian and sender activity from the shared admin dashboard when the acting user lacks the corresponding view permission.

### Verification After Protected Detail Workflows

- Focused admission/contact workflow tests passed against MySQL: 3 tests, 138 assertions.
- Full `php artisan test` passed against MySQL: 43 tests, 908 assertions.
- `php artisan migrate:fresh --seed` completed successfully against the local MySQL development schema.
- Route checks confirmed 6 admission and 6 contact-message workflow routes.
- `./vendor/bin/pint --test` passed.
- `php artisan view:cache` compiled all Blade templates successfully.
- `npm run build` passed with Vite 7.3.6.
- `git diff --check` passed.
- `php artisan about --only=environment,drivers` confirmed MySQL plus database-backed cache, queue, and session drivers.
- Active runtime/test configuration and file scans found no SQLite or PostgreSQL references or `database.sqlite` file.

### Remaining Gap After Protected Detail Workflows

- Gallery image add/remove/delete controls remain to be wired.

### Continued: Safe MySQL-Backed Gallery Image Workflow

- Converted the approved `+ Add images` gallery-editor control into a real MySQL-backed media selector while preserving the existing gallery panel and responsive media grid.
- Added `POST /admin/gallery/{gallery}/items`, `DELETE /admin/gallery/{gallery}/items/{galleryItem}`, and `DELETE /admin/gallery/{gallery}/items/{galleryItem}/media`.
- Added image-only attachment validation, duplicate protection, deterministic ordering, caption persistence, and transactional `media_usages` records.
- Replaced independent featured checkboxes with one cover-image radio group and synchronized `gallery_items.is_featured` with `galleries.cover_media_id`.
- Added safe detach behavior that retains the shared media record, removes its usage record, resequences the album, and selects a valid replacement cover.
- Added guarded media deletion that requires both gallery-management and media-approval permissions, rejects protected assets, blocks deletion while any external content reference remains, moves managed public files to private storage, deletes variants, and soft-deletes media metadata.
- Applied the same external-usage guard to deletion from the general media library so gallery and media entry points enforce one integrity rule.
- Added publication checks that reject empty galleries and any attached image lacking alt text, public approval, unrestricted visibility, or confirmed consent where required.
- Added row locks and repeat-in-transaction usage checks around attachment and deletion to prevent concurrent duplicate links or broken references.
- Extended deterministic seed data so every seeded gallery item has a matching MySQL `media_usages` record.
- Added audit records for gallery image attachment, detachment, and media deletion.

### Verification After Gallery Image Workflow

- Focused gallery tests passed against MySQL: 4 tests, 81 assertions.
- Related media workflow regression tests passed against MySQL: 3 tests, 142 assertions.
- Full `php artisan test --stop-on-failure` passed against MySQL: 45 tests, 959 assertions.
- `php artisan migrate:fresh --seed` completed successfully against `scb_school`.
- `php artisan db:show` confirmed MySQL `8.0.46`, 45 development tables, and 45 isolated testing tables.
- `php artisan route:list --name=admin.gallery` confirmed all 6 gallery routes.
- `vendor/bin/pint --test`, `php artisan view:cache`, `npm run build`, and `git diff --check` passed.
- Active runtime/test configuration scans found no SQLite/PostgreSQL references and no SQLite database file.
- Authenticated live HTTP verification returned 200 for `/admin/gallery/editor?gallery=school-life-gallery` on `http://127.0.0.1:8022` and confirmed all new workflow controls render.

### Remaining Required Work After Gallery Image Workflow

- No required functional feature from the tracked implementation inventory remains unwired.
- Platform alignment remains separate from functional delivery: the current runtime is PHP `8.2.31` with Laravel `12.64.0`, while the binding contract targets PHP `8.3+` and Laravel `13.x`.

### Continued: Complete Visible Interaction And Experience Audit

- Audited the rendered public website, custom administration panel, distinct staff portal, internal Filament panel, and approved prototype controls for dead links, decorative buttons, misleading disabled controls, and missing interaction states.
- Confirmed there is no parent portal route, navigation item, controller, or runtime screen; `/parent-portal` now has explicit regression and live 404 evidence while the separate staff portal remains intact.
- Replaced the remaining decorative admin create actions with policy-gated MySQL workflows for pages, news, events, galleries, staff profiles, programmes, and restricted roles; every workflow records an audit entry and starts in draft/private/restricted state.
- Added real audit-log detail and CSV export actions, real event calendar `.ics` downloads, direct public/staff document links, public news/download search, category filtering, pagination, custom managed-page routing, and accurate empty states.
- Fixed generated news and event links to use their slug binding values rather than numeric IDs, preventing home/list/detail 404 responses.
- Disabled public preview actions for unpublished admin records while retaining real edit actions and accessible labels.
- Converted admin and staff search fields, table filters, row selection, quick-create navigation, gallery keyboard controls, mobile navigation state, cookie preferences, and compact create panels into functional interactions.
- Replaced hard-coded staff identity with the authenticated MySQL user and restricted staff resources to public-disk, consent-cleared media.
- Replaced the admin login dead password link with a database-configured support email action.
- Added the exact St. Charles Borromeo crest loader to the public site, custom admin, staff portal, error pages, and internal Filament panel.
- Added reduced-motion-aware entrance, hover, and hero-media animation while preserving the approved prototype layout and exact school logo.
- Replaced runtime `Designed by` presentation with a larger `Powered by` Falconode treatment using a white surface, gold border, crimson outline, and stronger logo sizing.
- Removed all runtime `href="#"` controls and normalized visible admin filters to the statuses used by each MySQL resource.
- Preserved the current PHP `8.2.31`, Laravel `12.64.0`, and Filament `5.7.5` project versions as explicitly requested for this pass.

### Verification After Visible Interaction And Experience Audit

- Full `php artisan test --stop-on-failure` passed against `scb_school_test`: 49 tests, 1,101 assertions.
- `vendor/bin/pint --test` passed.
- `php artisan view:cache` compiled all Blade templates successfully.
- `npm ci` installed the lockfile dependencies and reported 0 vulnerabilities.
- `npm run build` passed with Vite `7.3.6`.
- JavaScript syntax checks passed for every file under `public/assets`.
- `php artisan db:show` confirmed MySQL `8.0.46`, the `scb_school` development schema, the isolated `scb_school_test` schema, and 45 tables in each schema.
- `php artisan migrate:status` confirmed all 9 migrations are applied to `scb_school`.
- Active configuration/source scans found no SQLite or PostgreSQL runtime connection and no `database/database.sqlite` file.
- The existing Laravel server remains available at `http://127.0.0.1:8022`; live checks returned 200 for `/` and `/admin/login`, and 404 for `/parent-portal`.

### Remaining Required Work After Visible Interaction Audit

- No visible UI or UX feature from the approved prototype remains intentionally decorative or unwired.
- Platform-version alignment remains outside this user-directed pass; the current project versions were deliberately retained.

### Continued: Production Media, Alerts, Account Security, And UI Polish

- Reviewed the five newly supplied photographs and selected four production-quality assets; omitted 104.png because its visible magenta frame and lower presentation quality did not fit the approved design.
- Created metadata-stripped WebP derivatives for the campus aerial, tree-planting activity, pupil recognition, and cultural performance, then registered them as consent-aware MySQL media records.
- Added a MySQL-configured, keyboard-accessible, touch-enabled, reduced-motion-aware home carousel and used the recognition/performance images in supporting sections and the managed gallery.
- Fixed the Apply now navigation action with stable sizing, responsive placement, and a clear directional icon.
- Replaced symbolic admin/staff navigation and row controls with local Lucide icons plus restrained green, blue, gold, and crimson action states.
- Removed the Falconode border/outline treatment, refined the white-surface alignment, and linked every runtime Falconode logo to https://www.falconode.net.
- Added permission-filtered unread admission/message alerts backed by first_viewed_at and viewed_by; opening an authorised detail view transactionally clears the corresponding alert and creates a non-sensitive audit record.
- Added secure self-profile editing for admin and staff users with current-password confirmation, unique email validation, strong optional passwords, other-session revocation, and password-safe audit metadata.
- Removed login credential autofill and public development-facing placeholder copy.
- Kept unapproved document records in draft/private state, blocked publishing when the actual PDF is missing, and prevented missing files from generating public URLs.
- Added anti-sniffing, same-origin framing, strict referrer, restricted browser-permission, and production HTTPS transport headers.
- Added .env.production.example with debug disabled, encrypted secure sessions, warning-level logs, and MySQL-only persistence while leaving the active local project configuration running.

### Verification After Production Hardening

- Full php artisan test --compact passed against scb_school_test: 57 tests, 1,198 assertions.
- Focused production-readiness coverage passed: approved/omitted media, carousel source, credential leakage, unread lifecycle, permission filtering, self-profile security, missing-document publication, and response headers.
- vendor/bin/pint --test, php artisan view:cache, node --check public/assets/js/scb-experience.js, and git diff --check passed.
- npm ci completed from the lockfile and reported 0 vulnerabilities; npm run build passed with Vite 7.3.6.
- composer audit --locked --no-interaction reported no security vulnerability advisories.
- Non-destructive php artisan migrate --seed --force applied the unread-state migration and refreshed the current MySQL development content.
- MySQL 8.0.46 is active; scb_school and scb_school_test each contain 45 application tables, and all 10 migrations are applied to development.
- Runtime/source scans found no active SQLite or PostgreSQL connection and no database/database.sqlite file.
- Live HTTP checks returned 200 for the homepage, contact page, staff login, admin login, selected WebP image, and local Lucide bundle.
- The updated Laravel server is running at http://127.0.0.1:8023.

## 2026-08-21 — Laravel 13 Production Readiness Remediation

### Defects Corrected

- Replaced administrator/staff GET logout with CSRF-protected POST logout.
- Added private, consent-aware media and download streaming; removed public child/prototype imagery and direct managed-file exposure.
- Added atomic download counts, form honeypots, missing admissions fields, email normalization, queued enquiry notifications, and a complete token password-reset flow.
- Removed the PHPUnit database password, blocked representative production seeding, expanded destructive-command guards, and made MySQL unavailability fail tests.
- Serialized tests with a MySQL advisory lock and reused a seeded schema with per-test transactions, reducing the suite from minutes to seconds without changing the MySQL-only contract.
- Promoted approved administration HTML into `resources/admin-experience/`, renamed the runtime adapter to `AdminExperienceController`, and removed visible prototype/development labels and sample credentials.
- Upgraded to PHP `^8.4`, Laravel `13.26.1`, Filament `5.7.6`, Tinker `3.0.2`, and PHPUnit `12.5.33`.
- Adopted Laravel 13 origin-aware request-forgery protection, JSON session serialization, and disabled arbitrary cache object unserialization.
- Patched the transitive high-severity `nanoid` advisory and regenerated the npm lockfile.
- Added production deployment, backup/restore, readiness, and design-parity gate documentation.

### Final Verification

- `php artisan migrate:fresh --seed --force`: passed on MySQL `8.0.46`; all 10 migrations ran and 45 application tables were seeded.
- `php artisan test --compact`: 64 tests passed with 1,206 assertions on `scb_school_test` under PHP `8.4.22` and Laravel `13.26.1`.
- Focused production readiness: 15 tests passed with 146 assertions.
- `vendor/bin/pint --test`: passed.
- `php artisan view:cache`: passed.
- `php artisan optimize`: passed, including production route caching after removal of route-action closures.
- `composer validate`: passed.
- `composer audit --locked --no-interaction`: no vulnerability advisories.
- `npm ci`: passed after the lockfile security patch.
- `npm audit`: zero vulnerabilities.
- `npm run build`: passed with Vite `7.3.6`.
- `git diff --check`: passed.
- MySQL/database-backed cache, queue, and session drivers confirmed by `php artisan about`.
- The exact school logo SHA-256 remains `2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b`.

### External Release Gates

- The deployment/workstation owner must install the required PHP 8.4 extensions system-wide; sudo installation was not available in this session, so the same official extension packages were loaded from an isolated temporary directory for verification.
- Production secrets, SMTP, HTTPS process configuration, workers, scheduler, monitoring, and backups must be supplied by the deployment environment.
- Formal desktop/tablet/mobile screenshot comparison remains the release-owner sign-off documented in `docs/design-parity-report.md`.

## 2026-08-22 — Content Operations, Discovery, Media, And Resilience

### Delivered

- Added reduced-motion-aware homepage carousel and staged hero entrance animation.
- Converted uploaded raster images to metadata-stripped WebP masters and responsive variants while keeping binaries out of MySQL and private storage paths authoritative through MySQL metadata.
- Expanded managed documents to PDF, Word, Excel, and OpenDocument upload/download workflows.
- Added queued and audited email replies for admission enquiries and contact messages, including delivery history and retry/failure state.
- Added an authorised FAQ review gate; any answer edit withdraws verification until an authorised publisher verifies it again.
- Added site search, XML sitemap, robots rules, canonical/social tags, structured data, accessible skip/navigation/dialog states, consent-controlled analytics, request IDs, named throttles, safe redirect validation, and branded error pages.
- Repositioned Falconode attribution as a compact “Website by” credit and removed development/framework-facing interface copy.

### Verification

- Full MySQL suite passed under PHP 8.4: 69 tests and 1,259 assertions.
- New regression coverage proves FAQ verification, tracked reply delivery, WebP conversion/variants, search/sitemap/SEO/accessibility/analytics wiring, request IDs, and branded 429 handling.
- Both new migrations applied successfully to `scb_school`; all 12 migrations report `Ran`.
- `vendor/bin/pint --test`, JavaScript syntax checks, `git diff --check`, Composer validation, route/config/view optimization, and the Vite production build passed.
- Composer and npm advisory audits found no known vulnerabilities.
- MySQL `8.0.46` reports 46 tables in each of `scb_school` and `scb_school_test`; the active driver remains MySQL with database-backed sessions, cache, and queues.
- The active-code prohibition scan found no SQLite or PostgreSQL runtime configuration.
- The exact logo remains unchanged at SHA-256 `2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b`.

## 2026-08-22 — Graduation Content, Media Usability, And Final Polish

### Delivered

- Inspected all 50 images in the owner-shared graduation Drive folder and selected six photographs covering the hall, guests, ceremonial welcome and representative graduates.
- Re-encoded the selected originals into metadata-stripped WebP masters capped at 2,560 pixels and created 480/960/1600 variants. MySQL now contains six public, consent-aware masters and 18 responsive variant records; the binaries remain in private Laravel storage.
- Published the MySQL-backed story “St. Charles Borromeo Celebrates the 2026 Graduation” with the owner-supplied facts: 15 August 2026, St. Charles Borromeo Hall, and guest of honour Solomon Itunda, Mbeya District Commissioner.
- Added five supporting article images through `media_usages`, a six-item Graduation 2026 gallery, and graduation imagery in the homepage carousel.
- Changed public and admin media URLs to same-origin relative routes so incorrect `APP_URL` host/scheme settings cannot break image display. Social metadata explicitly converts these paths back to canonical absolute URLs.
- Rebuilt the visible media library with server-side search, type/status/order filters, document previews, a preview-first responsive grid, relevant publication actions, and collapsed metadata, replacement, deletion and safeguarding controls.
- Added a dismissible responsive latest-news alert sourced from MySQL posts published during the previous 14 days.
- Converted remaining public operational images to responsive image components with intrinsic dimensions, lazy loading and responsive source sets.
- Standardized all rendered Falconode attribution—including public, custom admin, staff, errors and Filament—to text-only “Powered by Falconode (T) Ltd” linked to `https://www.falconode.net`; no Falconode logo is rendered.

### Verification

- Full MySQL suite: 71 tests passed with 1,371 assertions.

## 2026-08-22 — Responsive gallery and login remediation

- Replaced public gallery tracks that collapsed after the ninth item with stable three, two and one-column layouts.
- Added MySQL-backed album/category filtering, semantic captions, accessible lightbox focus behavior and responsive full-image containment.
- Generated applicable 480/960/1600 WebP derivatives for the twelve non-graduation photographs used by seeded public content.
- Converted the remaining raw operational public image tags to the shared responsive-image component.
- Changed both login views to the approved school aerial photograph and made the administrator Falconode credit deterministic and visible in normal form flow.
- Added the PRD acceptance matrix, permissions matrix and content/safeguarding launch checklist required by the delivery documentation.
- Graduation regression coverage verifies six WebP masters, 18 stored variants, consent/publication state, five article usages, six gallery items, relative URLs, public master/variant delivery, alert content and minimal media filters.
- The local `scb_school` schema was idempotently reseeded and reports the published graduation post, six graduation media records, 18 variant records and six gallery items.
- `vendor/bin/pint --test`, Blade/config/event/route/Filament optimization, JavaScript syntax checks, Composer validation, Vite 7.3.6 production build and `git diff --check` passed.
- `npm ci`, npm audit and Composer audit passed with zero known vulnerabilities.
- Optimized graduation assets contain no EXIF, Canon or Lightroom markers.
- The exact school logo remains unchanged at SHA-256 `2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b`.

## 2026-08-23 — News and event featured-image workflow

### Delivered

- Fixed the custom news and event editors so `featured_media_id` is submitted, validated, persisted to MySQL, and included in audit snapshots.
- Added a simple media-library picker with current-image preview and Public, In review, Consent needed, and Protected state labels.
- Added inline JPEG, PNG, and WebP upload for news and events. Uploads require alternative text and a safeguarding confirmation, support consent metadata, are optimized into private WebP masters and responsive variants, and attach to the saved draft in one transaction.
- Prevented published or scheduled content from using missing, private, restricted, deleted, unconfirmed-consent, non-image, or missing-binary media.
- Replaced decorative news consent checkboxes with the actual MySQL media review state and an operational media-library link.
- Restricted the internal Filament featured-image selectors to approved public images and restored the missing Scheduled option for Filament news records.
- Added policy checks when opening the news and event editors and when selecting or uploading media.

### Verification

- Full MySQL suite passed: 73 tests and 1,410 assertions.
- New focused coverage passed for library attachment, news upload, event upload, optimized storage, consent metadata, draft attachment, audit logging, and premature-publication rejection.
- `vendor/bin/pint --test`, JavaScript syntax checking, Composer validation, `git diff --check`, Blade/config/route cache compilation, and the Vite production build passed.
- `npm ci` installed the lockfile and reported zero vulnerabilities; the locked Composer audit reported no security advisories.
- MySQL `8.0.46` reports 46 tables in each of `scb_school` and `scb_school_test`; all 12 migrations are applied.
- Active configuration and source scans found no SQLite or PostgreSQL runtime configuration.
- The exact logo remains unchanged at SHA-256 `2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b`.

## 2026-08-23 — Media library and gallery preview remediation

### Delivered

- Fixed authenticated previews for seeded images stored on Laravel's private `local` disk; the media library and gallery editor no longer resolve those records through the public filesystem and return 404.
- Added private, non-cacheable preview responses with MIME locking and `nosniff`; images and PDFs open inline while office documents download safely.
- Added explicit missing-file states to media cards and gallery items, removed broken preview actions, and excluded unavailable files from gallery and news/event media pickers.
- Prevented missing binaries from being approved, attached to galleries, or published through a gallery, and omitted unavailable gallery items from public cards, counts, filters, and lightboxes.
- Added regression coverage for the real seeded private-storage layout, gallery editor preview URLs, missing admin previews, publication rejection, and public-gallery omission.

### Verification

- Full MySQL suite passed: 75 tests and 1,432 assertions; its guarded bootstrap ran `migrate:fresh --seed` only against `scb_school_test`.
- `vendor/bin/pint --test`, `npm ci`, `npm run build`, JavaScript syntax checking, strict Composer validation, and `git diff --check` passed; npm reported zero vulnerabilities.
- Laravel reports MySQL as the active database with database-backed session, cache, and queue drivers; MySQL `8.0.46` has 46 application tables and all 12 migrations report `Ran`.
- Active configuration and source scans found no SQLite or PostgreSQL runtime configuration.
- The exact logo remains unchanged at SHA-256 `2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b`.

## 2026-08-24 — Upload limits and cPanel release

### Delivered

- Raised the web upload ceiling to 21 MB with a 25 MB POST ceiling so the 10 MB image and 20 MB document workflows are not rejected by PHP before Laravel validation runs.
- Added browser-side size feedback, required alternative text for image uploads, and a branded HTTP 413 response.
- Added a cPanel-safe split deployment: the Laravel application remains at `~/scb_app`, while only web-safe files are copied into `public_html`.
- Added a reproducible release builder and a cPanel installation guide covering PHP 8.4, actual MySQL 8.0+, environment setup, migrations, permissions, cron, queues, uploads, and private-media transfer.
- Built `releases/scb-cpanel-20260824-095647.zip` without `.env`, local uploads, databases, tests, factories, seeders, Node dependencies, or internal development instructions.

### Verification

- Full MySQL suite passed: 76 tests and 1,446 assertions.
- `vendor/bin/pint --test`, Composer validation, PHP/JavaScript/shell syntax checks, and the Vite 7.3.6 production build passed.
- The 71,797,589-byte release ZIP passed archive integrity, embedded SHA-256 manifest, production Composer platform, secret-pattern, runtime-data, and public/private-layout checks.
- Release SHA-256: `2f10b82790328b849442f7958272872c5ba285ca7fc2236e183373d9e9fed533`.
- Active configuration contains no SQLite or PostgreSQL runtime connection, and the exact school logo remains unchanged at SHA-256 `2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b`.

## 2026-08-24 — Owner-approved Laravel 12 compatibility restoration

### Delivered

- Revised the binding contract to PHP 8.2+, Laravel 12.x, Filament 5.x, and MySQL 8.0+ after explicit owner approval.
- Restored the previously used application stack: Laravel 12.64.0, Filament 5.7.5, and a PHP 8.2.31 Composer platform target; MySQL remains 8.0.46.
- Downgraded Laravel Tinker to 2.11.1, PHPUnit to 11.5.56, Pint to 1.30.3, Symfony to its PHP-8.2-compatible 7.4 line, and the remaining transitive dependencies selected by Composer.
- Replaced Laravel 13's `PreventRequestForgery` references with Laravel 12's `ValidateCsrfToken` middleware while preserving CSRF coverage for application and Filament forms.
- Updated production, cPanel, reference, readiness, and database documentation for the restored platform.

### Verification

- The actual `php8.2` binary reports PHP 8.2.31 and Laravel 12.64.0; Composer platform checks pass with all required extensions.
- Reinstalled the local dependency tree from the Laravel 12 lock file and confirmed that both the default CLI and `php8.2` boot the existing workspace as Laravel 12.64.0.
- The full PHP 8.2/MySQL suite passed: 76 tests and 1,447 assertions.
- Laravel route discovery, Composer strict validation, Pint, PHP/JavaScript/shell syntax checks, and the Vite production build passed.
- Built and audited `releases/scb-cpanel-php82-20260824-145906.zip` (71,600,679 bytes); archive integrity, embedded checksums, PHP 8.2 production platform requirements, secret exclusions, private/public layout, and compiled-asset parity passed.
- PHP 8.2 cPanel release SHA-256: `adecf39dab7d22d7821fc8e002d5575d7c84e1a2b73af5ecc7417efa9919f122`.
- MySQL 8.0.46 reports all 12 migrations as `Ran` and 46 application tables in each of `scb_school` and `scb_school_test`; Composer and npm audits report no known vulnerabilities.
- MySQL remains the sole relational runtime and test driver; no SQLite or PostgreSQL active configuration was introduced.
