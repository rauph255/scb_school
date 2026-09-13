# MySQL Foundation Code Verified

Date: 2026-08-04

## Context

The prior build log described Phase 0C as the next milestone. The current codebase now contains migrations, models, factories, seed data, MySQL configuration tests, MySQL foundation tests, and a destructive-command guard.

## Historical Finding

The local test database was initially unreachable:

```text
SQLSTATE[HY000] [2002] Unknown error while connecting
```

Because tests use MySQL only, unavailable credentials must not cause a fallback to another database engine.

## Decision

Keep the MySQL-only test configuration. MySQL unavailability now fails the suite; it is never converted into a passing skip. A MySQL named lock prevents two destructive test processes from racing on `scb_school_test`, and tests reuse one seeded schema with per-test transactions.

## Next Step

The MySQL schemas are available to `scb_app`. The clean migration, seed, and test proof is run with:

```bash
php artisan migrate:fresh --seed
php artisan test
```

## Result

`php artisan migrate:fresh --seed` passed against `scb_school`.

On 2026-08-21, `migrate:fresh --seed` passed against MySQL 8.0.46 and the Laravel 13 suite passed with 63 tests and 1,190 assertions. All managed public screens now use MySQL-backed Blade.
