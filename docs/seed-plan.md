# Seed Plan

Seeders populate MySQL with development records. The runtime application must query these records through Eloquent or Query Builder.

## Seeder Order

1. `RolePermissionSeeder`
2. `LocalAdminSeeder`
3. `SiteSettingSeeder`
4. `MenuSeeder`
5. `MediaSeeder`
6. `PageSeeder`
7. `EditorialSeeder`
8. `EventSeeder`
9. `SchoolInformationSeeder`
10. `GallerySeeder`
11. `DownloadSeeder`
12. `FaqSeeder`
13. `DemoEnquirySeeder` for local and testing only

## Seed Content Boundaries

- Placeholder copy must be labelled as unverified where it could be mistaken for official content.
- Do not seed invented official fees, addresses, examination results, accreditations, or statistics.
- Do not seed production credentials.
- The local admin account must be local-only and documented in the final setup guide once authentication exists.
- Child media records must include consent fields; public seed media must be consent-confirmed or not consent-required.

## Prototype Region Mapping

| Region | Seeder | Tables |
|---|---|---|
| School identity, motto, contact placeholders | `SiteSettingSeeder` | `site_settings` |
| Header navigation and footer links | `MenuSeeder` | `menus`, `menu_items`, `pages` |
| Logo and supplied images | `MediaSeeder` | `media`, `media_variants` |
| Home/about/admissions/static page content | `PageSeeder` | `pages`, `page_blocks`, `media_usages` |
| News cards and detail | `EditorialSeeder` | `post_categories`, `posts`, `tags`, `post_tag` |
| Events listing/detail | `EventSeeder` | `event_categories`, `events` |
| Academics and staff sections | `SchoolInformationSeeder` | `departments`, `programmes`, `staff_members` |
| Gallery grid | `GallerySeeder` | `gallery_categories`, `galleries`, `gallery_items` |
| Downloads table | `DownloadSeeder` | `download_categories`, `downloads`, `media` |
| FAQ accordions | `FaqSeeder` | `faq_categories`, `faqs` |
| Admin dashboard sample workflow | all seeders plus optional demo enquiries | content/enquiry/audit tables |

## Required Seed Assertions

- A primary menu exists and has active items.
- Footer menus exist for school, resources, and legal links.
- A protected school logo media record exists with the required checksum.
- At least one public page exists for every public route.
- Home page blocks cover hero, welcome, feature cards, latest news, upcoming events, and call to action.
- News, events, programmes, galleries, downloads, and FAQs have published records.
- Private/internal enquiry notes are present only in local/testing seed data.
