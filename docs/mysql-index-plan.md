# MySQL Index Plan

## Global Rules

- Every foreign key receives an index.
- Slugs and identity keys are unique.
- Public listings use composite indexes that begin with publication status and date fields.
- Searchable public text can use MySQL full-text indexes after the base schema exists.
- Large text columns are not included in ordinary B-tree composite indexes.

## Unique Indexes

| Table | Unique index |
|---|---|
| `users` | `email` |
| `roles` | `slug` |
| `permissions` | `slug` |
| `role_user` | `(role_id, user_id)` |
| `permission_role` | `(permission_id, role_id)` |
| `site_settings` | `(group_name, setting_key)` |
| `menus` | `location` |
| `pages` | `slug` |
| `content_revisions` | `(revisionable_type, revisionable_id, revision_number)` |
| `redirects` | `source_path` |
| `post_categories`, `tags`, `event_categories`, `departments`, `programmes`, `gallery_categories`, `download_categories` | `slug` |
| `posts`, `events`, `galleries`, `downloads` | `slug` |
| `post_tag` | `(post_id, tag_id)` |
| `media` | `uuid`, `(disk, directory, stored_name)` |
| `media_variants` | `(media_id, variant_key)` |
| `media_usages` | `(media_id, usable_type, usable_id, field_name)` |
| `gallery_items` | `(gallery_id, media_id)` |
| `admission_enquiries`, `contact_messages` | `reference_code` |

## Composite Listing Indexes

| Query | Index |
|---|---|
| Published pages | `pages(status, published_at)` |
| Page type listing | `pages(page_type, status)` |
| Page blocks render order | `page_blocks(page_id, is_enabled, sort_order)` |
| Active menu tree | `menu_items(menu_id, parent_id, sort_order)` |
| Published posts | `posts(status, published_at)` |
| Posts by category | `posts(post_category_id, status, published_at)` |
| Featured posts | `posts(is_featured, status, published_at)` |
| Active announcements | `announcements(status, starts_at, ends_at)` |
| Published events | `events(publication_status, starts_at)` |
| Events by category | `events(event_category_id, publication_status, starts_at)` |
| Event state filters | `events(event_state, starts_at)` |
| Published programmes | `programmes(status, published_at)` |
| Programmes by department | `programmes(department_id, status, sort_order)` |
| Public leadership staff | `staff_members(is_leadership, is_public, sort_order)` |
| Public media guard | `media(visibility, publication_restricted, deleted_at)` |
| Media consent review | `media(consent_required, consent_confirmed)` |
| Published galleries | `galleries(status, published_at)` |
| Gallery items order | `gallery_items(gallery_id, sort_order)` |
| Published downloads | `downloads(status, published_at)` |
| Downloads by category | `downloads(download_category_id, status, publication_date)` |
| Published FAQs | `faqs(status, published_at)` |
| FAQs by category | `faqs(faq_category_id, status, sort_order)` |
| Enquiry workflow | `admission_enquiries(status, created_at)`, `contact_messages(status, created_at)` |
| Assigned enquiries | `admission_enquiries(assigned_to, status, created_at)`, `contact_messages(assigned_to, status, created_at)` |
| Audit log by subject | `audit_logs(subject_type, subject_id, created_at)` |
| Audit log by actor | `audit_logs(actor_id, created_at)` |

## Full-Text Index Candidates

- `pages(title, excerpt)`
- `posts(title, excerpt, body)`
- `events(title, summary, body)`
- `programmes(name, summary, body)`
- `faqs(question, answer)`
- `downloads(title, description)`

Full-text search must still apply public visibility, soft-delete, and media-consent scopes before returning records.
