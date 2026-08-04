# Reference Inventory

Date: 2026-08-03

## Verified Inputs

| Input | Location | SHA-256 | Status |
|---|---|---:|---|
| UI/UX prototype ZIP | `st_charles_borromeo_uiux_prototype.zip` | `45a8095cfb10247092535cc3f79a84ad6343d321e3a09c2add1a9652fcfab3e4` | Verified |
| Immutable school logo | `assets/logo-original.jpg` | `2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b` | Verified |
| Extracted prototype logo | `reference/scb_uiux_prototype/assets/images/logo-original.jpg` | `2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b` | Verified |

## Prototype Screens

| Reference screen | Current Laravel route | Future implementation target |
|---|---|---|
| `index.html` | `/` | Blade home page with reusable public components |
| `about.html` | `/about` | Blade about page |
| `academics.html` | `/academics` | Blade academics/programmes page |
| `admissions.html` | `/admissions` | Blade admissions page and enquiry workflow |
| `news.html` | `/news` | News listing with search/filter/pagination |
| `news-detail.html` | `/news/young-learners-shine` | News detail template |
| `events.html` | `/events` | Event listing with upcoming/past filters |
| `event-detail.html` | `/events/parent-orientation` | Event detail template |
| `gallery.html` | `/gallery` | Gallery grid and accessible lightbox |
| `downloads.html` | `/downloads` | Downloads listing |
| `contact.html` | `/contact` | Contact details and contact form |
| `faq.html` | `/faq` | FAQ accordions |
| `privacy.html` | `/privacy` | Privacy and safeguarding page |
| `admin/login.html` | `/admin` | Filament branded login |
| `admin/dashboard.html` | `/admin/dashboard` | Filament dashboard widgets |
| `admin/pages.html` | `/admin/pages` | Filament Pages resource |
| `admin/page-editor.html` | `/admin/pages/editor` | Controlled page block editor |
| `admin/news.html` | `/admin/news` | Filament News resource |
| `admin/news-editor.html` | `/admin/news/editor` | News editor |
| `admin/events.html` | `/admin/events` | Filament Events resource |
| `admin/event-editor.html` | `/admin/events/editor` | Event editor |
| `admin/gallery.html` | `/admin/gallery` | Filament Gallery resource |
| `admin/gallery-editor.html` | `/admin/gallery/editor` | Gallery editor |
| `admin/downloads.html` | `/admin/downloads` | Filament Downloads resource |
| `admin/staff.html` | `/admin/staff` | Filament Staff resource |
| `admin/programmes.html` | `/admin/programmes` | Filament Programmes resource |
| `admin/admissions.html` | `/admin/admissions` | Admission enquiries resource |
| `admin/contact-messages.html` | `/admin/contact-messages` | Contact messages resource |
| `admin/media.html` | `/admin/media` | Media library |
| `admin/users.html` | `/admin/users` | Users resource |
| `admin/roles.html` | `/admin/roles` | Roles and permissions |
| `admin/settings.html` | `/admin/settings` | Site settings |
| `admin/audit-log.html` | `/admin/audit-log` | Audit log |

## Assets

| Asset group | Source | Runtime location | Notes |
|---|---|---|---|
| Prototype CSS/JS/images | `reference/scb_uiux_prototype/assets/` | `public/assets/prototype/` | Served unchanged as the current visual baseline |
| Immutable school logo | `assets/logo-original.jpg` | `resources/reference/logo-original.jpg`, `public/assets/images/brand/scb-logo-original.jpg` | Copied without recompression |
| Supplied JPG photography | `/home/rauph/Downloads/scb_website_images_hd/scb_website_images/pictures/` | `public/assets/images/school/` | Preserved for the Laravel asset library and future media seeding |
| Falconode original logo | `/home/rauph/Downloads/LOGO FALCONODE lite logo no background-02.png` | `public/assets/images/brand/falconode-original.png` | Preserved as supplied |
| Falconode credit crop | `public/assets/images/brand/falconode-original.png` | `public/assets/images/brand/falconode-credit.png` | Derived display crop for small footer/admin credit |

## Current Gaps

- Public and admin routes currently serve approved prototype HTML as a visual baseline. They must be replaced by MySQL-backed Blade/Filament screens after the Phase 0C schema and seeders are verified against MySQL.
- The current admin routes render static prototype screens and are not yet protected by authentication.
- Filament is not installed yet.
- The project is currently Laravel 12.x, while the updated contract calls for Laravel 13.x.
- Phase 0C migrations, models, factories, seeders, safety guard, and MySQL tests are present, but the clean MySQL migration/seed proof is blocked because local database administrator credentials or pre-created schemas are unavailable.
- The `.git` directory is empty, so the required chunk commit cannot be created until repository metadata is restored.
