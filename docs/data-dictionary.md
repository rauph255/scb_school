# Data Dictionary

All tables use unsigned `BIGINT` primary keys, UTC timestamps, InnoDB, `utf8mb4`, and `utf8mb4_unicode_ci` unless noted.

## Identity And Access

| Table | Key columns | Foreign keys | Delete rules |
|---|---|---|---|
| `users` | `name`, `email` unique, `password`, `is_active`, `last_login_at`, `last_login_ip`, `deleted_at` | none | Soft delete |
| `roles` | `name`, `slug` unique, `description`, `is_system` | none | Restrict when assigned |
| `permissions` | `name`, `slug` unique, `group_name`, `description` | none | Restrict when assigned |
| `role_user` | unique `(role_id, user_id)`, `assigned_by` | `role_id`, `user_id`, `assigned_by` | role/user cascade, assigner set null |
| `permission_role` | unique `(permission_id, role_id)` | `permission_id`, `role_id` | Cascade |

## Site And Pages

| Table | Key columns | Foreign keys | Delete rules |
|---|---|---|---|
| `site_settings` | `group_name`, `setting_key`, `value_json`, `value_type`, `is_public` | `updated_by` | Updater set null |
| `menus` | `name`, `location` unique, `status` | none | Restrict if referenced |
| `menu_items` | `label`, `link_type`, `url`, `route_name`, `target`, `sort_order`, `is_active` | `menu_id`, `parent_id`, `page_id` | Menu/parent cascade, page set null |
| `pages` | `title`, `slug` unique, `page_type`, `template_key`, `status`, `excerpt`, SEO columns, `published_at`, `scheduled_at`, `deleted_at` | `featured_media_id`, `og_media_id`, `created_by`, `updated_by` | Media/user set null, soft delete |
| `page_blocks` | `block_type`, `heading`, `subheading`, `body`, `settings`, `sort_order`, `is_enabled`, `visible_from`, `visible_until` | `page_id`, `media_id` | Page cascade, media set null |
| `content_revisions` | `revisionable_type`, `revisionable_id`, `revision_number`, `snapshot`, `change_summary` | `created_by` | Creator set null |
| `redirects` | `source_path` unique, `target_url`, `http_status`, `is_active`, `hit_count`, `last_hit_at` | `created_by` | Creator set null |

## Editorial And Events

| Table | Key columns | Foreign keys | Delete rules |
|---|---|---|---|
| `post_categories` | `name`, `slug` unique, `description`, `sort_order`, `is_active`, `deleted_at` | none | Soft delete |
| `posts` | `title`, `slug` unique, `excerpt`, `body`, `status`, `is_featured`, `published_at`, `scheduled_at`, SEO columns, `deleted_at` | `post_category_id`, `author_id`, `featured_media_id`, `og_media_id`, `created_by`, `updated_by` | Optional references set null, soft delete |
| `tags` | `name`, `slug` unique | none | Restrict if used |
| `post_tag` | unique `(post_id, tag_id)` | `post_id`, `tag_id` | Cascade |
| `announcements` | `title`, `message`, `severity`, `status`, `starts_at`, `ends_at`, `link_label`, `link_url`, `is_dismissible`, `deleted_at` | `created_by`, `updated_by` | User set null, soft delete |
| `event_categories` | `name`, `slug` unique, `description`, `is_active`, `deleted_at` | none | Soft delete |
| `events` | `title`, `slug` unique, `summary`, `body`, `starts_at`, `ends_at`, `timezone`, `venue_name`, `venue_address`, `event_state`, `publication_status`, `published_at`, SEO columns, `deleted_at` | `event_category_id`, `featured_media_id`, `programme_download_id`, `created_by`, `updated_by` | Optional references set null, soft delete |

## School Information

| Table | Key columns | Foreign keys | Delete rules |
|---|---|---|---|
| `departments` | `name`, `slug` unique, `description`, `email`, `telephone`, `sort_order`, `is_active`, `deleted_at` | none | Soft delete |
| `programmes` | `name`, `slug` unique, `programme_type`, `level`, `summary`, `body`, `sort_order`, `status`, `published_at`, SEO columns, `deleted_at` | `department_id`, `featured_media_id`, `created_by`, `updated_by` | Optional references set null, soft delete |
| `staff_members` | `name`, `slug` unique, `job_title`, `staff_type`, `approved_biography`, `email`, `telephone`, `sort_order`, `is_leadership`, `is_public`, `is_active`, `deleted_at` | `department_id`, `photo_media_id` | Optional references set null, soft delete |

## Media, Galleries, Downloads

| Table | Key columns | Foreign keys | Delete rules |
|---|---|---|---|
| `media` | `uuid` unique, `disk`, `directory`, `stored_name`, `original_name`, `mime_type`, `extension`, `size_bytes`, `width`, `height`, `checksum_sha256`, `alt_text`, `caption`, `credit`, `focal_x`, `focal_y`, `visibility`, `consent_required`, `consent_confirmed`, `consent_reference`, `publication_restricted`, `restriction_reason`, `is_protected_asset`, `deleted_at` | `uploaded_by` | Uploader set null, soft delete |
| `media_variants` | `variant_key`, `disk`, `path`, `mime_type`, `width`, `height`, `size_bytes`, `checksum_sha256` | `media_id` | Cascade |
| `media_usages` | `usable_type`, `usable_id`, `field_name` | `media_id` | Cascade |
| `gallery_categories` | `name`, `slug` unique, `sort_order`, `is_active` | none | Restrict if used |
| `galleries` | `title`, `slug` unique, `description`, `event_date`, `status`, `published_at`, `sort_order`, `deleted_at` | `gallery_category_id`, `cover_media_id`, `created_by`, `updated_by` | Optional references set null, soft delete |
| `gallery_items` | unique `(gallery_id, media_id)`, `caption`, `sort_order`, `is_featured` | `gallery_id`, `media_id` | Gallery cascade, media restrict |
| `download_categories` | `name`, `slug` unique, `description`, `sort_order`, `is_active`, `deleted_at` | none | Soft delete |
| `downloads` | `title`, `slug` unique, `description`, `version`, `publication_date`, `status`, `published_at`, `download_count`, `deleted_at` | `download_category_id`, `media_id`, `created_by`, `updated_by` | Category/users set null, media restrict, soft delete |

## FAQs, Enquiries, Audit

| Table | Key columns | Foreign keys | Delete rules |
|---|---|---|---|
| `faq_categories` | `name`, `slug` unique, `sort_order`, `is_active` | none | Restrict if used |
| `faqs` | `question`, `answer`, `sort_order`, `status`, `published_at`, `deleted_at` | `faq_category_id`, `created_by`, `updated_by` | Optional references set null, soft delete |
| `admission_enquiries` | `reference_code` unique, `guardian_name`, `email`, `telephone`, `intended_level`, `intended_term`, `intended_year`, `preferred_contact_method`, `message`, `consent_confirmed`, `status`, `source_ip_hash`, `user_agent_hash`, `responded_at`, `closed_at`, `deleted_at` | `assigned_to` | Assignee set null, soft delete |
| `admission_enquiry_notes` | `note`, `is_sensitive` | `admission_enquiry_id`, `user_id` | Enquiry cascade, user set null |
| `contact_messages` | `reference_code` unique, `full_name`, `email`, `telephone`, `subject`, `message`, `consent_confirmed`, `status`, `source_ip_hash`, `user_agent_hash`, `responded_at`, `closed_at`, `deleted_at` | `assigned_to` | Assignee set null, soft delete |
| `contact_message_notes` | `note`, `is_sensitive` | `contact_message_id`, `user_id` | Message cascade, user set null |
| `audit_logs` | `actor_id`, `action`, `subject_type`, `subject_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `request_id`, `created_at` | `actor_id` | Actor set null, append-only |
