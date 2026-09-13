# Reference Inventory

Last verified: 2026-08-21

## Verified Inputs

| Input | Location | SHA-256 | Status |
|---|---|---:|---|
| UI/UX prototype ZIP | `st_charles_borromeo_uiux_prototype.zip` | `45a8095cfb10247092535cc3f79a84ad6343d321e3a09c2add1a9652fcfab3e4` | Verified |
| Immutable school logo | `assets/logo-original.jpg` | `2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b` | Verified |
| Extracted prototype logo | `reference/scb_uiux_prototype/assets/images/logo-original.jpg` | `2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b` | Verified |

## Prototype Screens

| Reference screen | Current Laravel route | Future implementation target |
|---|---|---|
| `index.html` | `/` | Implemented Blade home page with reusable public layout and MySQL-backed Eloquent data |
| `about.html` | `/about` | Implemented Blade about page with MySQL page blocks and staff |
| `academics.html` | `/academics` | Implemented Blade academics page with MySQL page blocks and programmes |
| `admissions.html` | `/admissions` | Implemented Blade admissions page and persisted MySQL enquiry workflow |
| `news.html` | `/news` | Implemented Blade news listing with published MySQL posts |
| `news-detail.html` | `/news/{post:slug}` | Implemented Blade news detail with published MySQL post lookup |
| `events.html` | `/events` | Implemented Blade event listing with published MySQL events |
| `event-detail.html` | `/events/{event:slug}` | Implemented Blade event detail with published MySQL event lookup |
| `gallery.html` | `/gallery` | Implemented Blade gallery with published MySQL galleries and consent-filtered media |
| `downloads.html` | `/downloads` | Implemented Blade downloads listing with published MySQL downloads |
| `contact.html` | `/contact` | Implemented Blade contact details and persisted MySQL contact-message workflow |
| `faq.html` | `/faq` | Implemented Blade FAQ accordions with published MySQL FAQ records |
| `privacy.html` | `/privacy` | Implemented Blade privacy and safeguarding page with MySQL page blocks |
| Staff portal login | `/staff-portal/login` | Implemented separate staff login UI with teacher-contributor authentication |
| Staff portal dashboard | `/staff-portal` | Implemented separate protected staff dashboard UI with MySQL-backed events, downloads, news, and directory records |
| `admin/login.html` | `/admin/login` | Implemented visible approved prototype login UI with administrator authentication |
| `admin/dashboard.html` | `/admin`, `/admin/dashboard` | Implemented visible approved prototype dashboard UI |
| `admin/pages.html` | `/admin/pages` | Implemented visible approved prototype Pages UI |
| `admin/page-editor.html` | `/admin/pages/editor` | Implemented visible approved prototype page editor UI |
| `admin/news.html` | `/admin/news` | Implemented visible approved prototype News UI |
| `admin/news-editor.html` | `/admin/news/editor` | Implemented visible approved prototype news editor UI |
| `admin/events.html` | `/admin/events` | Implemented visible approved prototype Events UI |
| `admin/event-editor.html` | `/admin/events/editor` | Implemented visible approved prototype event editor UI |
| `admin/gallery.html` | `/admin/gallery` | Implemented visible approved prototype Gallery UI |
| `admin/gallery-editor.html` | `/admin/gallery/editor` | Implemented approved MySQL-backed gallery editor with metadata, order, cover, attach, detach, delete, and publication-safety controls |
| `admin/downloads.html` | `/admin/downloads` | Implemented visible approved prototype Downloads UI |
| `admin/staff.html` | `/admin/staff` | Implemented visible approved prototype Staff UI |
| `admin/programmes.html` | `/admin/programmes` | Implemented visible approved prototype Programmes UI |
| `admin/admissions.html` | `/admin/admissions`, `/admin/admissions/{id}` | Implemented approved Admissions list plus protected detail, assignment, notes, status, and export workflow |
| `admin/contact-messages.html` | `/admin/contact-messages`, `/admin/contact-messages/{id}` | Implemented approved Contact Messages list plus protected detail, assignment, notes, status, and export workflow |
| `admin/media.html` | `/admin/media` | Implemented preview-first media library with search, type/status/order filters, collapsed metadata and consent controls, document previews, and MySQL-backed publication actions |
| `admin/users.html` | `/admin/users` | Implemented visible approved prototype Users UI |
| `admin/roles.html` | `/admin/roles` | Implemented visible approved prototype Roles UI |
| `admin/settings.html` | `/admin/settings` | Implemented visible approved prototype Settings UI |
| `admin/audit-log.html` | `/admin/audit-log` | Implemented visible approved prototype Audit Log UI |

## Assets

| Asset group | Source | Runtime location | Notes |
|---|---|---|---|
| Approved administration CSS/JS | `reference/scb_uiux_prototype/assets/` | `public/assets/scb/` | Production CSS/JS bundle; school photographs and original-source images are not public |
| Administration experience HTML | `reference/scb_uiux_prototype/admin/` | `resources/admin-experience/` | Production resources consumed by `AdminExperienceController`; runtime does not depend on `reference/` |
| Immutable school logo | `assets/logo-original.jpg` | `resources/reference/logo-original.jpg`, `public/assets/images/brand/scb-logo-original.jpg` | Copied without recompression |
| Supplied JPG photography | `/home/rauph/Downloads/scb_website_images_hd/scb_website_images/pictures/` | `resources/reference/school/`, then private Laravel storage through MySQL media records | Seed source is not web-accessible; public delivery is authorization-aware |
| 2026 graduation photography | Owner-shared Google Drive folder `1DT2cd4Qk7qO5cQ77ffrcTGZIUfs8KELW` | `resources/reference/school/2026/graduation/`, then private Laravel storage through MySQL media records | Six selected photographs; metadata-stripped WebP masters plus 480/960/1600 variants; provenance is recorded in `docs/decisions/2026-08-22-graduation-media-selection.md` |
| Falconode visual assets | `/home/rauph/Downloads/LOGO FALCONODE lite logo no background-02.png` | Preserved legacy files under `public/assets/images/brand/` | Not rendered; every credit is text-only: “Powered by Falconode (T) Ltd” linking to `https://www.falconode.net` |

## Delivery State

- All managed public pages render from MySQL-backed Blade and publication scopes.
- The visible `/admin` experience is role-protected, uses production resource templates, and has MySQL-backed workflows for every listed resource.
- The staff portal is intentionally separate under `/staff-portal` and requires the staff role.
- Filament 5 is available at `/admin-core` as a protected, MySQL-backed administration panel.
- The owner-approved compatibility target is PHP 8.2+, Laravel 12.64.0, Filament 5.7.5, and MySQL 8.
- Phase 0C migrations, models, factories, seeders, safety guards, and MySQL tests are verified.
- Formal browser screenshot comparison at the documented desktop/mobile widths remains a release-owner sign-off item; see `docs/design-parity-report.md`.
