# Prototype Served Through Laravel — Superseded

- Opened: 2026-08-03
- Superseded: 2026-08-21

## Context

The repository started as a fresh Laravel 12 application with only the default welcome page. The approved prototype ZIP was present and its hash matched the PRD. The user asked to continue, fix errors, revise markdown files, use the supplied school photographs, and show a Falconode "Designed by" credit on the website and admin panel.

## Decision

Serve the approved prototype screens through Laravel routes as an interim visual baseline before converting each screen into reusable Blade components and Filament resources.

## Rationale

- This immediately removes the default Laravel splash page.
- It keeps all public and admin screens visually aligned with the approved prototype.
- It lets the supplied Falconode credit appear consistently across public and admin screens.
- It avoids inventing a new design while the full Laravel/Filament implementation is still pending.

## Resolution

- Public prototype routes were replaced by MySQL-backed Blade controllers and views.
- The administration experience is authenticated, authorized, and wired to MySQL-backed workflows.
- Approved administration HTML was promoted to `resources/admin-experience/`; `AdminExperienceController` renders it with server-side records and forms. Runtime code no longer reads from `reference/`.
- A protected MySQL-backed Filament panel is available at `/admin-core`.
