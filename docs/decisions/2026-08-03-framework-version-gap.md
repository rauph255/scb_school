# Framework Version Gap

Date: 2026-08-03

## Context

The binding project documents require Laravel 13.x, PHP 8.3+, and Filament 5.x.

## Finding

The current installed application is a Laravel 12 skeleton:

- `composer.json` requires `laravel/framework: ^12.0`.
- Filament is not installed.
- The local PHP CLI reports PHP 8.2.31 for the dev server.

## Decision

Do not pretend the version requirement is satisfied. Keep documenting and configuring the project toward the required MySQL-first architecture, then upgrade Laravel/PHP and install Filament in a dedicated compatibility chunk.

## Impact

The current admin routes remain static prototype baseline screens. Real Filament resources, authentication, policies, and database-backed widgets remain pending.
