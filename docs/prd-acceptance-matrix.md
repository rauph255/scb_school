# PRD Acceptance Matrix

Last reviewed: 2026-08-22

This matrix maps the first-release acceptance criteria in `PRD.md` to implementation evidence. Deployment-specific approvals remain launch gates rather than repository defects.

| PRD acceptance area | Repository status | Evidence |
|---|---|---|
| Public and administration screens | Complete | Route inventory and response coverage in `tests/Feature/ExampleTest.php`; screen inventory in `docs/design-parity-report.md` |
| Responsive design parity | Automated safeguards complete; human sign-off required | Responsive navigation, gallery, image and login tests; viewport checklist in `docs/design-parity-report.md` |
| Exact original logo | Complete | Protected media record and SHA-256 test: `2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b` |
| MySQL-managed routine content | Complete | Eloquent-backed public/admin controllers, policies and CRUD persistence tests |
| Server-side permissions | Complete | Policies in `app/Policies/`, role tests and `docs/permissions-matrix.md` |
| Publication privacy | Complete | Published scopes and 404/search/sitemap tests for non-public records |
| Child-media safeguarding | Complete | Consent/restriction scopes, protected private storage, gallery publication checks and media tests |
| Admissions and contact workflows | Complete | Validation, honeypots, rate limits, MySQL persistence, queued notifications, assignment, notes, safe export and reply tracking |
| Search and SEO | Complete | Published-content search, sitemap, robots, canonical/social metadata and structured-data tests |
| Clean database build | Complete | MySQL 8 clean migration/seed and foundation test suite |
| Automated tests and assets | Complete | Final test, Pint, Composer and Vite gates recorded in `docs/production-readiness-report.md` |
| Operating documentation | Complete | Database documentation, deployment/restore guide, permissions matrix, safeguarding checklist, reference inventory and build log |
| Verified public facts | Launch-owner review required | Seed data avoids unverified official claims; final owner review uses `docs/content-safeguarding-launch-checklist.md` |
| Sensitive information protection | Complete | Private enquiry policies, safe exports, non-public internal notes, media authorization and production artifact tests |
| Final acceptance evidence | Repository evidence complete; screenshots and production infrastructure pending | This matrix, `docs/production-readiness-report.md`, and `docs/design-parity-report.md` |

## Current Responsive Remediation

- All operational public photographs now use the shared responsive-image component.
- Seeded public photographs have WebP derivatives at the applicable 480, 960 and 1600 pixel widths.
- The public gallery supports album/category filtering, semantic captions, keyboard activation, a modal dialog, Escape close, focus return and layouts for one, two and three columns.
- Both administrator and staff login screens use the MySQL-authorized school aerial photograph and expose the text-only Falconode credit.

