# Prototype Served Through Laravel

Date: 2026-08-03

## Context

The repository started as a fresh Laravel 12 application with only the default welcome page. The approved prototype ZIP was present and its hash matched the PRD. The user asked to continue, fix errors, revise markdown files, use the supplied school photographs, and show a Falconode "Designed by" credit on the website and admin panel.

## Decision

Serve the approved prototype screens through Laravel routes as an interim visual baseline before converting each screen into reusable Blade components and Filament resources.

## Rationale

- This immediately removes the default Laravel splash page.
- It keeps all public and admin screens visually aligned with the approved prototype.
- It lets the supplied Falconode credit appear consistently across public and admin screens.
- It avoids inventing a new design while the full Laravel/Filament implementation is still pending.

## Consequences

- The current admin panel is not a real authenticated Filament panel yet.
- The static prototype routes must be replaced screen by screen with Blade components and Filament resources.
- Admin route protection is a high-priority next chunk before any real data is exposed through `/admin/*`.
