# MySQL Database Design

Date: 2026-08-03

## Authority

This document implements the binding direction in `PRD_MYSQL_AMENDMENT.md` and `DATABASE_DESIGN.md`.

## Platform Rules

- Database engine: MySQL 8.0+ only.
- Storage engine: InnoDB.
- Character set: `utf8mb4`.
- Collation: `utf8mb4_unicode_ci`.
- Application access: Eloquent ORM or Laravel Query Builder.
- Test database: separate MySQL schema, `scb_school_test`.
- Large binaries: Laravel storage or S3-compatible storage, with metadata in MySQL.

## Domain Areas

| Area | Tables | Purpose |
|---|---|---|
| Identity and access | `users`, `roles`, `permissions`, `role_user`, `permission_role` | Admin accounts, RBAC, policy support |
| Framework infrastructure | `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`, `notifications` | Laravel database-backed runtime services |
| Site settings and navigation | `site_settings`, `menus`, `menu_items` | Public identity, contact details, header, footer |
| Pages | `pages`, `page_blocks`, `content_revisions`, `redirects` | Controlled page-builder content, SEO, redirects |
| Editorial content | `post_categories`, `posts`, `tags`, `post_tag`, `announcements` | News, announcements, categories, tags |
| Events | `event_categories`, `events` | Published school events and event detail pages |
| School information | `departments`, `programmes`, `staff_members` | Academics, programmes, leadership, staff |
| Media and files | `media`, `media_variants`, `media_usages`, `download_categories`, `downloads` | Asset metadata, consent, public file access |
| Galleries | `gallery_categories`, `galleries`, `gallery_items` | Public albums and ordered media |
| FAQs | `faq_categories`, `faqs` | Categorized public FAQs |
| Enquiries | `admission_enquiries`, `admission_enquiry_notes`, `contact_messages`, `contact_message_notes` | Private admissions/contact workflows |
| Audit | `audit_logs` | Append-only privileged activity trail |

## Public Visibility Scopes

Publishable records are public only when:

```text
status = published
AND published_at <= CURRENT_TIMESTAMP
AND deleted_at IS NULL
```

Event records also require `publication_status = published`. Media is public only when:

```text
visibility = public
AND publication_restricted = false
AND deleted_at IS NULL
AND (consent_required = false OR consent_confirmed = true)
```

## Prototype Data Source Mapping

| Screen region | MySQL source |
|---|---|
| Header logo/name/motto | `media`, `site_settings` |
| Header navigation | `menus`, `menu_items`, optional `pages` |
| Footer columns | `menus`, `menu_items`, `site_settings` |
| Home hero and sections | `pages`, `page_blocks`, `media` |
| Home news/events | published `posts`, upcoming published `events` |
| About | `pages`, `page_blocks`, leadership `staff_members`, `media` |
| Academics | `pages`, `page_blocks`, published `programmes`, `departments` |
| Admissions | `pages`, `page_blocks`, published `downloads`, published `faqs` |
| News list/detail | `posts`, `post_categories`, `tags`, `media` |
| Events list/detail | `events`, `event_categories`, optional `downloads` |
| Gallery | `galleries`, `gallery_items`, public consent-approved `media` |
| Downloads | `downloads`, `download_categories`, public file `media` |
| Contact | public `site_settings`, persisted `contact_messages` |
| FAQ | `faq_categories`, published `faqs` |
| Admin dashboard | aggregate queries across content/enquiry/audit tables |
| Admin resources | Eloquent resources for each managed table |

## Deletion Rules

- Use soft deletes for recoverable public content, media metadata, users, enquiries, downloads, and galleries.
- Use cascade deletes for true owned child rows, such as page blocks, gallery items, and enquiry notes.
- Use `SET NULL` for optional creators, updaters, assignees, authors, departments, categories, and featured media.
- Use `RESTRICT` where deleting a referenced record would expose broken public content or unsafe files.

## Open Implementation Decisions

- MySQL credentials are not yet available to create `scb_school` and `scb_school_test`.
- Laravel 13.x and Filament 5.x are required by the product docs, but the current installed project is Laravel 12.x and Filament is not installed.
