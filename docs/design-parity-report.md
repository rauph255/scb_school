# Design Parity Report

- Last updated: 2026-08-22
- Status: automated parity safeguards pass; final browser screenshot sign-off is required before public launch.

## Source of Truth

- Approved archive SHA-256: `45a8095cfb10247092535cc3f79a84ad6343d321e3a09c2add1a9652fcfab3e4`.
- Extracted reference: `reference/scb_uiux_prototype/`.
- Immutable school logo SHA-256: `2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b`.
- Public presentation is implemented in `resources/views/public/` and shared partials.
- Approved administration templates are deployed from `resources/admin-experience/` and enhanced server-side by `AdminExperienceController`.

## Verified Safeguards

- The public, admin, and staff route suites render successfully from MySQL data.
- Responsive navigation, hero carousel, recent-news alert, article image gallery, modal/dialog controls, filters, validation messages, and administration actions have functional response coverage.
- The media library retains the approved admin shell while using a minimal preview-first card layout; advanced metadata, replacement, deletion and consent controls remain collapsed until requested.
- Runtime responses are tested to reject unresolved `href="#"` controls on key screens.
- The public gallery now uses a repeatable three/two/one-column layout rather than item-count-specific tracks, so albums remain usable beyond nine photographs on desktop, tablet and mobile.
- Gallery album/category controls, semantic captions, keyboard activation, Escape close and focus restoration are covered by response/source tests.
- Administrator and staff login screens use the approved school aerial photograph with responsive sources; the administrator Falconode credit remains visible below the form at every breakpoint.
- Every operational public photograph is routed through the shared responsive-image component and seeded photographs now have applicable 480/960/1600 WebP derivatives.
- Direct public copies of school photography and prototype-source images were removed. Displayed media uses consent-aware MySQL records and controlled media routes.
- The exact school logo hash is enforced by an automated production-readiness test and remains unchanged in all three protected copies.
- Production Blade compilation and Vite asset compilation pass.
- The visible administration experience no longer depends on the design-reference folder at runtime.

## Screen Inventory

Public screens: home, about, academics, admissions, news list/detail, events list/detail, gallery, downloads, contact, FAQ, privacy, generic managed pages, 404, and 500.

Administration screens: login, dashboard, pages/editor, news/editor, events/editor, gallery/editor, downloads, staff, programmes, admissions list/detail, contact-message list/detail, media, users, roles, settings, audit-log list/detail, and account profile.

Staff screens: login and dashboard/contribution workflow.

## Final Screenshot Gate

The release owner must compare the production build against the approved prototype with real approved content at these minimum viewports:

| Class | Width × height |
|---|---:|
| Mobile portrait | 390 × 844 |
| Tablet portrait | 768 × 1024 |
| Desktop | 1440 × 1000 |

For every screen, verify typography direction, colour tokens, spacing, container widths, responsive stacking, navigation state, image crop/focal point, form states, long-content wrapping, empty states, and keyboard focus visibility. Record screenshots and any accepted differences in the release ticket.

Required gallery checks: all-photo and album filters, category selection where present, caption wrapping, one/two/three-column transitions, portrait and landscape crops, full-image containment in the lightbox, Escape close, backdrop close and focus return.

This gate cannot be truthfully marked complete from server-side tests alone. No visual deviation is approved by this report; it documents the remaining human/browser acceptance step.
