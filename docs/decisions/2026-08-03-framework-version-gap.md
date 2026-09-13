# Framework Version Contract — Owner-Approved Laravel 12 Target

- Opened: 2026-08-03
- Revised by owner: 2026-08-24

## Context

The owner has approved PHP 8.2+, Laravel 12.x, Filament 5.x, and MySQL 8.0+ as the binding compatibility target for cPanel deployment.

## Original Finding

The current installed application is a Laravel 12 skeleton:

- `composer.json` requires `laravel/framework: ^12.0`.
- Filament is not installed.
- The local PHP CLI reports PHP 8.2.31 for the dev server.

## Current Resolution

The dependency contract targets PHP `^8.2`, Laravel `12.64.0`, Laravel Tinker `^2.10.1`, PHPUnit `^11.5.50`, and Filament `5.7.5`. Composer emulates PHP 8.2.31 while resolving dependencies so the lockfile cannot silently select PHP 8.3/8.4-only packages.

Laravel 12's `ValidateCsrfToken` middleware protects the Filament panel and application forms. JSON session serialization and the restricted cache unserialization setting remain enabled.

## Evidence

- PHP 8.2.31 / Laravel 12.64.0 / Filament 5.7.5 is the required verification target.
- Full MySQL suite: 63 tests and 1,190 assertions passed before the final runtime-resource promotion; the focused post-promotion suite also passed.
- The deployment host must enable the PHP extensions listed in `docs/production-deployment.md`.
