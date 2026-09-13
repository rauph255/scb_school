# Production Readiness Report

Assessment date: 2026-08-23

## Outcome

The application code and dependency locks are production-oriented and satisfy the binding PHP/Laravel/Filament/MySQL technology contract. Automated application, database, formatting, build, and dependency-security gates pass. Deployment still requires production secrets/infrastructure, the documented PHP extensions, and formal browser screenshot sign-off.

## Resolved Defects

1. Replaced state-changing GET logout links with CSRF-protected POST forms for administrator and staff sessions.
2. Removed direct public storage delivery for managed media and downloads. Public access now checks MySQL publication, consent, restriction, and file state before streaming; downloads increment their MySQL count atomically.
3. Moved managed published binaries to private storage and removed public copies of school/prototype child imagery.
4. Removed static fallback photography from public Blade screens and resolved display media through consent-filtered Eloquent records.
5. Added form throttling, honeypots, missing admissions fields, deterministic email normalization, and queued non-sensitive notifications.
6. Removed the committed PHPUnit database password and ignored `.env.testing`.
7. Blocked representative development seeding and destructive database commands outside approved local/testing schemas.
8. Replaced the non-functional password link with a token-based, throttled, audited password reset flow.
9. Added MySQL test-suite locking and per-test transactions; MySQL connection failure now fails rather than falsely passing through skips.
10. Promoted administration templates from the design-reference directory into production resources and removed visible prototype/development labels and sample login credentials.
11. Locked the owner-approved compatibility stack to PHP `^8.2`, Laravel 12.64.0, Filament 5.7.5, Tinker 2, and PHPUnit 11, with CSRF protection, JSON sessions, and safe cache unserialization defaults.
12. Patched the high-severity transitive `nanoid` advisory and confirmed zero npm and Composer advisories.
13. Added responsive WebP conversion for JPEG/PNG/WebP uploads, metadata stripping through re-encoding, a 2,560-pixel master limit, 480/960/1600 variants, private binary storage, and immutable public image caching.
14. Added Word, Excel, OpenDocument, and PDF upload/download workflows with MySQL publication records and private streamed delivery.
15. Added queued, tracked admission/contact email replies with delivery history, retry state, audit records, and automatic responded status.
16. Added authorised FAQ verification; edited answers return to draft and only verified, due answers reach public queries and FAQ structured data.
17. Added site-wide published-content search, XML sitemap, environment-aware robots rules, canonical/Open Graph/Twitter metadata, School/WebSite/Article/Event/FAQ structured data, skip navigation, accessible controls, and consent-gated Plausible analytics.
18. Added named rate limits, request correlation IDs, log context, safe redirect validation, branded 403/404/419/429/500/503 responses, and compact professional Falconode credits.
19. Corrected uploaded-image delivery to use same-origin relative media URLs, preventing a stale or wrong-scheme `APP_URL` from breaking public and administrator previews while retaining absolute social metadata URLs.
20. Rebuilt the media library as a responsive preview-first workflow with server-side search, type/status/order filters, collapsed metadata and consent controls, document tiles, and only the publication action relevant to each asset.
21. Selected six owner-supplied graduation photographs, stripped EXIF metadata through WebP re-encoding, generated 18 responsive derivatives, registered consent-aware MySQL records, and added a MySQL-backed graduation gallery and news article.
22. Added a responsive, dismissible latest-news alert sourced from recently published MySQL posts and standardized every public/admin/staff/error/Filament credit as text-only “Powered by Falconode (T) Ltd”.
23. Replaced the item-count-dependent public gallery mosaic with stable desktop/tablet/mobile layouts, added album/category filtering, semantic captions, an accessible focus-restoring lightbox, and an explicit empty result state.
24. Routed the remaining public and staff-login photographs through responsive image markup, generated 32 missing WebP derivatives for seeded school photographs, and placed the MySQL-authorized school aerial view on both login experiences.
25. Made the administrator login credit insertion deterministic and positioned the text-only Falconode link in normal form flow so it remains visible on short, mobile and desktop viewports.
26. Completed the news and event featured-image workflow with MySQL library selection, transactional reviewed uploads, live previews, responsive WebP variants, consent metadata, audit records, permission checks, and publication guards that reject unapproved or missing media.

## Platform and Database Evidence

| Item | Verified value |
|---|---|
| PHP | 8.2.31 compatibility target; project requires `^8.2` |
| Laravel | 12.64.0 |
| Filament | 5.7.5 |
| PHPUnit | 11.5.56 |
| MySQL | 8.0.46-0ubuntu0.22.04.2 |
| Development schema | `scb_school` |
| Test schema | `scb_school_test` |
| Database driver | `mysql` |
| Session/cache/queue | `database` / `database` / `database` |
| Migration count | 12, all ran after a clean MySQL rebuild |
| Application table count | 46 per schema |

The sanitized connection convention is host `127.0.0.1`, port `3306`, database `scb_school`, and a least-privilege application user. Passwords are environment secrets and are not present in tracked test configuration.

Authoritative schema documentation:

- `docs/database-design.md`
- `docs/erd.md`
- `docs/data-dictionary.md`
- `docs/mysql-index-plan.md`
- `docs/migration-order.md`
- `docs/seed-plan.md`

## Verification Evidence

The final command set includes:

```text
php artisan migrate:fresh --seed --force    PASS (MySQL 8.0.46, 12 migrations)
php8.2 artisan test                        PASS (76 tests, 1,447 assertions)
vendor/bin/pint --test                      PASS
php artisan view:cache                      PASS
php artisan optimize                        PASS (config/events/routes/views/Filament cacheable)
npm ci                                      PASS
npm audit                                   PASS (0 vulnerabilities)
npm run build                               PASS (Vite 7.3.6)
composer validate                           PASS
composer audit --locked                     PASS (0 advisories)
git diff --check                            PASS
```

The suite covers MySQL connection/migrations, foreign keys and uniqueness, publication scopes, media consent/safeguarding, news/event media-library attachment and reviewed uploads, role/policy enforcement, public form persistence, visible administration CRUD persistence, query bounds, destructive-command guards, logout CSRF, password reset, production artifacts, email normalization, queued/tracked replies, FAQ verification, responsive image conversion, graduation media/content/alert rendering, media-library filtering, search/SEO/accessibility/analytics wiring, rate limiting, request IDs, and private media/download delivery.

PRD operating deliverables are indexed by `docs/prd-acceptance-matrix.md`, `docs/permissions-matrix.md`, and `docs/content-safeguarding-launch-checklist.md`.

## Production Configuration

`.env.production.example` disables debug mode, enables encrypted HTTPS-only sessions, selects MySQL/database drivers, and leaves credentials, keys, hostnames, and mail configuration blank for secret injection. The representative seeder throws in production and must not be used to create production accounts.

Binary media uses Laravel/S3-compatible storage; MySQL stores authoritative metadata, consent, visibility, checksums, variants, and usage references. The exact school logo remains unchanged with SHA-256 `2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b`.

## Deployment Gates Outside the Repository

- Install/enable the required PHP 8.2 modules on the deployment host.
- Populate production secrets, APP URL, SMTP sender, approved notification recipient, and least-privilege MySQL credentials.
- Configure HTTPS/web server, PHP process manager, queue workers, scheduler, monitoring, and backups.
- Complete the browser screenshot comparison in `docs/design-parity-report.md` with approved content.
- Create the release commit/tag after the owner reviews the existing dirty worktree; this task did not overwrite or auto-commit pre-existing user changes.

Deployment, backup, and restore procedures are in `docs/production-deployment.md`.
