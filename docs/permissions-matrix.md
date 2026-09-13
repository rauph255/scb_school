# Permissions Matrix

This matrix maps the first-release roles in `PRD.md` to the MySQL-backed permission records enforced by Laravel policies. The administration role editor is authoritative for assignments; role names alone never bypass a policy.

| Capability | Super administrator | School administrator | Content editor | Admissions officer | Teacher contributor |
|---|---:|---:|---:|---:|---:|
| Pages: view/create/update | Yes | Yes | Yes | Admissions content only | Draft contribution only |
| Pages: publish/verify FAQs | Yes | Yes | No | No | No |
| News and events | Yes | Yes | Draft/review | No | Draft contribution only |
| Galleries and downloads | Yes | Yes | Draft/review | Admissions downloads when assigned | No |
| Media view/upload | Yes | Yes | Yes | When required for assigned content | No |
| Media approval, consent and deletion | Yes | Yes | No | No | No |
| Admissions enquiries | Yes | Yes | No | View/manage/export | No |
| Contact messages | Yes | Yes | No | No | No |
| Staff and programmes | Yes | Yes | Draft/review | No | No |
| Public site settings | Yes | Yes | No | No | No |
| Users, roles and permissions | Yes | No | No | No | No |
| Audit log | Yes | Read when explicitly granted | No | No | No |
| Staff portal | Optional | Optional | Optional | Optional | Yes |

## Enforced Permission Records

- Content: `pages.view`, `pages.create`, `pages.update`, `pages.publish`, `news.manage`, `events.manage`, `galleries.manage`, `downloads.manage`, `staff.manage`, `programmes.manage`.
- Media: `media.view`, `media.approve`.
- Enquiries: `admissions.view`, `admissions.manage`, `admissions.export`, `contacts.view`, `contacts.manage`, `contacts.export`.
- Administration: `settings.manage`, `users.manage`, `roles.manage`, `audit.view`.

The representative local/testing seeder grants every permission only to the protected super-administrator. This fail-closed default prevents a role label from silently granting private-data access. Before adding production users, the launch owner must use **Roles & permissions** to approve the school-specific assignments above and then test each role with a separate account.

## Security Invariants

- Inactive or deleted accounts cannot access a portal.
- Teacher contributors cannot access the administrator portal solely because they can access the staff portal.
- Export, assignment, internal notes and replies use separate enquiry permissions.
- Media publication requires the approval permission and valid consent state.
- Users cannot disable or re-role their own active administrator account.
- Protected system roles and the exact school logo retain additional invariants.

