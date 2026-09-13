# Migration Order

Migrations must be created in this sequence so foreign keys can be applied intentionally.

1. Framework infrastructure:
   `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `notifications`.
2. `users`.
3. `roles`.
4. `permissions`.
5. `role_user`.
6. `permission_role`.
7. `media`.
8. `media_variants`.
9. `media_usages`.
10. `site_settings`.
11. `pages`.
12. Add page media foreign keys if circular references require a later pass.
13. `page_blocks`.
14. `content_revisions`.
15. `menus`.
16. `menu_items`.
17. `post_categories`.
18. `tags`.
19. `posts`.
20. `post_tag`.
21. `announcements`.
22. `download_categories`.
23. `downloads`.
24. `event_categories`.
25. `events`.
26. Add optional `events.programme_download_id` foreign key after `downloads` exists.
27. `departments`.
28. `programmes`.
29. `staff_members`.
30. `gallery_categories`.
31. `galleries`.
32. `gallery_items`.
33. `faq_categories`.
34. `faqs`.
35. `admission_enquiries`.
36. `admission_enquiry_notes`.
37. `contact_messages`.
38. `contact_message_notes`.
39. `redirects`.
40. `audit_logs`.
41. Add enquiry view-state columns.
42. Add FAQ verification columns and verifier relationship.
43. `email_replies` after users and both enquiry tables exist.
44. Full-text indexes and late composite indexes that depend on all columns existing.

## Validation Before Milestone 0C

- Confirm MySQL credentials and version.
- Confirm `DB_CONNECTION=mysql` in `.env`, `.env.example`, and `phpunit.xml`.
- Confirm `scb_school` and `scb_school_test` exist before destructive migration commands.
- Add a command guard before running `migrate:fresh` in non-local/non-testing environments.
- Do not create `database/database.sqlite`.
