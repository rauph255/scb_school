# Product Requirements Document

## St. Charles Borromeo Pre & Primary School Website and Administration System

**Document status:** Build baseline  
**Delivery model:** Laravel public website plus customized Filament administration panel  
**Visual authority:** `st_charles_borromeo_uiux_prototype.zip`  
**Implementation authority:** this PRD and `AGENTS.md`

---

## 1. Product vision

Build a professional, classic, minimalistic, responsive, and dynamic digital platform for St. Charles Borromeo Pre & Primary School. The public website should communicate the school’s identity, Catholic values, academic offering, admissions information, activities, and community life. The administration panel should let authorized staff update the website safely without changing code.

The approved HTML prototype is not inspiration; it is the visual specification. The finished website and admin panel must reproduce its layout system, branding, assets, interaction patterns, and responsive behaviour before connecting those interfaces to persistent data and workflows.

## 2. Product goals

1. Present a credible and distinctive school identity.
2. Make official school information easy to find on mobile and desktop.
3. Allow authorized staff to publish pages, news, events, galleries, programmes, staff profiles, and downloads.
4. Capture admission and contact enquiries through secure workflows.
5. Protect children’s personal information and media.
6. Provide accessible, search-friendly, maintainable, and performant pages.
7. Establish an extensible Laravel foundation for later parent or student systems without placing those sensitive systems in the first release.

## 3. Authoritative reference package

The build must use `st_charles_borromeo_uiux_prototype.zip`, SHA-256:

```text
45a8095cfb10247092535cc3f79a84ad6343d321e3a09c2add1a9652fcfab3e4
```

Start at `scb_uiux_prototype/prototype-map.html`. The archive contains the approved public screens, administration screens, shared CSS, JavaScript, optimized school photographs, original photographs, and exact uploaded logo.

The exact logo must remain unchanged. Its SHA-256 is:

```text
2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b
```

## 4. Target users

### Public users

- Prospective parents and guardians
- Current parents and guardians
- Pupils and prospective pupils
- Alumni and community members
- Church and education stakeholders
- Visitors seeking contact, location, programmes, events, or downloadable information

### Administrative users

- Super Administrator
- School Administrator
- Content Editor
- Admissions Officer
- Teacher or Contributor

## 5. Role summary

| Role | Primary capabilities | Restrictions |
|---|---|---|
| Super Administrator | All content, users, permissions, settings, logs, restoration, publishing | No restriction except protected system invariants |
| School Administrator | Manage and publish routine website content and enquiries | Cannot alter protected super-admin ownership or system-level secrets |
| Content Editor | Create and edit content, upload approved media, submit for review | Cannot manage users, permissions, or critical settings; publishing may require approval |
| Admissions Officer | Review and manage admission enquiries and admission content | No access to unrelated security or system settings |
| Teacher / Contributor | Create assigned drafts and proposed media | Cannot publish, manage users, or view private enquiries |

All permissions must be enforced server-side through policies and gates.

## 6. Technical baseline

- PHP 8.3+
- Laravel 13.x
- Filament 5.x
- Blade templates
- Livewire only where interaction requires it
- Alpine.js for lightweight client interactions
- Tailwind CSS 4.x
- Vite
- MySQL 8+ or PostgreSQL
- Redis recommended for production queues and cache
- S3-compatible storage optional for production media
- Pest or PHPUnit for automated tests
- Laravel Pint
- Git-based delivery

Major-version changes require a documented compatibility reason and explicit approval.

## 7. Approved design system

### Colours

| Token | Hex | Intended use |
|---|---|---|
| Plum | `#3D2D3F` | Primary brand surface, headings, admin navigation |
| Plum 2 | `#513651` | Secondary plum states and gradients |
| Crimson | `#B8101E` | Emphasis, active states, alerts, links |
| Gold | `#DAAD18` | Primary calls to action, highlights, borders |
| Soft Gold | `#E4CD5A` | Hover and subtle accent states |
| Cream | `#F8F5EC` | Warm section backgrounds |
| Paper | `#FFFDF8` | Primary page background |
| Ink | `#241D25` | Body text |
| Muted | `#6F6670` | Secondary text |
| Line | `#E7E0D8` | Borders and dividers |
| Green | `#36513B` | Supporting positive or institutional accent |

### Typography

- Display headings: extra-bold, weight 900, tight line height.
- Body: neutral, highly readable sans-serif.
- School name and limited ceremonial accents: classic serif.
- Preferred production families: Manrope, Source Sans 3, and optional Cormorant Garamond.
- Fonts must be self-hosted or loaded in a privacy-conscious, licensed manner.
- Body paragraphs must not use extra-bold weight.

### Layout and component rules

- Maximum content width approximately 1240px.
- Large sections use generous vertical spacing.
- Cards use restrained rounded corners, fine borders, and soft plum-tinted shadows.
- Buttons are rectangular with subtle rounding and bold uppercase labels.
- Public navigation is sticky, responsive, and keyboard accessible.
- The admin panel uses a plum sidebar, white working surface, gold active indicator, clear table density, and consistent status badges.
- Animations are subtle and respect reduced-motion preferences.

## 8. Public information architecture

| Screen | Required content and features |
|---|---|
| Home | Hero, key message, calls to action, school strengths, welcome content, news, events, school life imagery, footer |
| About | History, mission, vision, motto, values, leadership message, religious identity, facilities and community context |
| Academics | Pre-primary, primary, curriculum, learning approach, co-curricular activities, religious formation, programme cards |
| Admissions | Requirements, process, dates, documents, FAQ, downloads, admission enquiry call to action |
| News | Searchable and paginated cards, categories, dates, featured content, empty states |
| News detail | Title, metadata, featured image, article content, related content, sharing metadata |
| Events | Upcoming and past tabs or filters, dates, venues, statuses, event cards/list |
| Event detail | Full details, time, venue, status, registration link, programme download, related events |
| Gallery | Album and category filtering, responsive media grid, captions, accessible lightbox |
| Downloads | Categories, title, description, date, version, file type, size, download action |
| Contact | Official contacts, location, office hours, map link, accessible contact form |
| FAQ | Ordered questions, categories, accessible disclosure controls |
| Privacy | Privacy, enquiry handling, cookies, safeguarding and data rights statements |
| Error pages | Branded 404 and 500 experiences with safe navigation back to core pages |

## 9. Shared public components

- Top information bar
- Sticky site header
- Exact logo and school-name lockup
- Desktop navigation and mobile navigation
- Breadcrumbs
- Hero and page-hero variants
- Buttons and text links
- Section headings
- Feature cards
- News cards
- Event rows/cards
- Statistics strip
- Values strip
- Image frames
- Quotes and testimonials
- Gallery grid and lightbox
- Search and filter controls
- Form controls and validation messages
- Empty states and alerts
- Footer and legal links
- Cookie controls when non-essential tracking is enabled

## 10. Administration information architecture

| Screen / resource | Required capabilities |
|---|---|
| Login | Branded login, validation, reset flow, throttling, secure session behaviour |
| Dashboard | Metrics, recent activity, pending content, enquiries, upcoming events, quick actions |
| Pages | Search, status filters, ordering, bulk actions, create, duplicate, preview, archive, restore |
| Page editor | Metadata, controlled block builder, preview, schedule, SEO, revision-aware workflow |
| News | Search, filters, categories, tags, author, featured state, publication workflow |
| News editor | Rich content, image, excerpt, SEO, scheduling, related posts, preview |
| Events | Date filters, status filters, categories, venue, publication workflow |
| Event editor | Start/end, venue, map, registration, programme, status, SEO, preview |
| Galleries | Albums, cover image, category, date, status, item count |
| Gallery editor | Ordered media, captions, alt text, consent status, cover selection, publication checks |
| Media library | Search, filter, upload, metadata, reuse, replacement, consent and usage information |
| Downloads | File, category, version, publication date, size, visibility, download count |
| Staff | Name, role, department, approved biography, photo, order, active status |
| Programmes | Type, level, summary, content, image, order, status |
| Admissions | Status, assignment, internal notes, safe export, reply tracking |
| Contact messages | Status, assignment, internal notes, spam handling, safe export |
| Users | Accounts, role assignment, active status, reset actions, last login |
| Roles | Permission matrix, protected roles, server-side enforcement |
| Settings | Identity, contacts, social links, navigation, footer, SEO defaults, mail display settings |
| Audit log | Actor, action, subject, changes, timestamp, IP where lawful, filters and read-only detail |

## 11. Content workflow

Managed publishable content must support:

- Draft
- Pending review
- Scheduled
- Published
- Archived

Rules:

1. Draft and review content is never public.
2. Scheduled content becomes visible only at the approved date and time.
3. Archived content is not part of normal public listings.
4. Preview links are signed, time-limited, and permission-aware.
5. Publishing and destructive actions are audited.
6. Soft-deleted records can be restored by authorized users.

## 12. Page builder

The page editor must use controlled blocks, not unrestricted HTML layout creation.

Supported blocks:

- Hero
- Rich text
- Image and text
- Call to action
- Statistics
- Feature cards
- Values
- Staff list
- Programme list
- Testimonials or quote
- Latest news
- Upcoming events
- Gallery preview
- Downloads list
- FAQ accordion
- Contact details
- Map or directions link
- Approved video embed

Each block supports only the design options present in the approved system: content, image, alignment, button, background variant, enabled state, and ordering. Administrators must not be able to create arbitrary colours or uncontrolled page structures.

## 13. Media and child safeguarding

Each media item should support:

- Original name and secure stored name
- MIME type and file size
- Width and height for images
- Alternative text
- Caption
- Credit
- Focal point
- Consent required flag
- Consent confirmed flag
- Consent reference or internal note
- Publication restriction
- Uploaded by and timestamps
- Usage references where practical

Rules:

- The original approved logo is a protected system asset.
- Public child media cannot be published when required consent is unconfirmed.
- Private or review media is not exposed through public URLs.
- Unnecessary EXIF metadata should be stripped from public derivatives.
- Uploads are validated by real MIME type, extension, size, and image decoding.
- SVG uploads are disallowed unless a safe sanitization process is implemented.

## 14. Forms and enquiries

### Contact form fields

- Full name
- Email
- Telephone number
- Subject
- Message
- Consent confirmation

### Admission enquiry fields

- Parent or guardian name
- Email
- Telephone number
- Intended class or level
- Intended term or year
- Preferred contact method
- Message
- Consent confirmation

### Behaviour

- Accessible labels and error summaries
- Client feedback plus authoritative server validation
- CSRF protection
- Honeypot and rate limiting
- Optional privacy-conscious CAPTCHA only when abuse requires it
- Database persistence
- Queued email notification
- Submission receipt or clear success state
- Status workflow: New, In progress, Responded, Closed, Spam
- Staff assignment and internal notes
- CSV export limited by permission
- No sensitive child documents in the basic enquiry form

## 15. Search and SEO

- Search Pages, News, Events, Announcements, Programmes, FAQs, and Downloads where published.
- Exclude draft, review, archived, private, and expired preview content.
- Generate XML sitemap and robots.txt.
- Support editable SEO title, description, canonical URL, Open Graph title, description, and image.
- Support index/no-index controls.
- Add structured data for School, Article, Event, FAQPage, and BreadcrumbList where applicable.
- Provide redirect management for changed slugs.
- Use clean, stable URLs and semantic headings.

## 16. Accessibility

Target WCAG 2.2 AA-oriented implementation:

- Keyboard-operable menus, tabs, accordions, modals, and lightboxes
- Visible focus indicator
- Skip link
- Semantic landmarks and heading order
- Accessible names and descriptions
- Form labels, inline errors, and error summary
- Sufficient contrast
- Alternative text management
- Captions or transcripts for meaningful video
- Adequate touch targets
- No meaning conveyed by colour alone
- Reduced-motion support
- Zoom and reflow support
- Screen-reader announcements for dynamic updates

## 17. Security and privacy

- HTTPS in production
- Secure cookies and environment handling
- Production debug disabled
- Authentication throttling
- Authorization policies and panel access checks
- Validation and output escaping
- Safe file handling
- Rate limiting for public endpoints
- Audit logging for privileged changes
- No shared administrator accounts
- Password reset and optional 2FA architecture
- Least-privilege roles
- Data-retention policy support
- Backups stored outside the main server
- No public exposure of internal notes or private contact data

## 18. Performance and operations

- Responsive WebP or AVIF derivatives where supported
- Lazy loading below the fold
- Explicit media dimensions
- Minified compiled assets
- Minimal third-party scripts
- Indexed database queries and eager loading
- Route/config/view caching in production
- Queued mail and media work
- Scheduler for publishing, cleanup, and maintenance
- CDN-ready storage URLs
- Health endpoint
- Error and uptime monitoring guidance
- Daily database and media backups with documented restore process

## 19. Data model summary

Core entities:

- User
- Role
- Permission
- SiteSetting
- Menu
- MenuItem
- Page
- PageBlock
- Post
- PostCategory
- Tag
- Announcement
- Event
- EventCategory
- Programme
- StaffMember
- Department
- Gallery
- GalleryItem
- Media
- Download
- DownloadCategory
- FAQ
- FAQCategory
- AdmissionEnquiry
- ContactMessage
- Redirect
- AuditLog

All publishable records should include status, slug where applicable, publication timestamps, creator/updater identifiers, timestamps, and soft deletion where required.

## 20. Implementation phases and chunks

### Phase A — Reference and UI

1. Reference inventory and design-token lock.
2. Public UI implementation with fixture data.
3. Admin UI implementation with fixture or seeded data.
4. Desktop and mobile visual parity report.

### Phase B — Functional chunks

1. Foundation, authentication, RBAC, settings, audit base.
2. Navigation, pages, block builder, SEO fields, publishing workflow.
3. Media library and safeguarding.
4. News and announcements.
5. Events.
6. Admissions and contact workflows.
7. Academics, programmes, staff, and FAQs.
8. Galleries and downloads.
9. Search, SEO, analytics, accessibility, and performance.
10. Final testing, hardening, backups, and deployment.

Each chunk must finish with migrations, models, policies, factories, seeders, UI, public integration, tests, documentation, build checks, and a clear commit.

## 21. Testing requirements

- Clean migration and seeding test
- Authentication and panel access tests
- Role and policy matrix tests
- CRUD tests for managed resources
- Publication visibility and scheduling tests
- Signed preview tests
- Search visibility tests
- Form validation, rate limiting, and spam-control tests
- Upload validation and publication restriction tests
- Consent enforcement tests for child media
- Email notification and queue tests
- Export permission tests
- Route and status-code tests
- Browser or component tests for essential interaction
- Accessibility checks for key templates
- Screenshot parity checks for representative desktop and mobile views

## 22. Deliverables

- Complete Laravel repository
- Customized Filament admin panel
- Responsive public site
- Exact original logo and approved media assets
- Environment example
- Migrations, factories, and seeders
- Automated tests
- Local development admin account
- Setup and deployment documentation
- Reference inventory
- Data model documentation
- Permissions matrix
- Design parity report
- Build log
- Content and safeguarding launch checklist
- Final PRD acceptance matrix with evidence

## 23. Out of scope for first release

- Parent portal
- Student portal
- Online fee payment
- Attendance and report cards
- Homework management
- Transport tracking
- Library lending system
- Mobile application
- Full online admissions with sensitive document uploads
- Integration with an external school-management system unless separately specified

The architecture may allow later integration, but first-release code must not pretend these systems exist.

## 24. Acceptance criteria

The project is accepted only when:

1. Every prototype public and admin screen has a production implementation.
2. Desktop and mobile design parity has no unresolved high-severity issue.
3. The exact original logo hash remains unchanged.
4. Routine content is fully manageable through Filament.
5. Permissions are enforced server-side and tested.
6. Draft and restricted content cannot be accessed publicly.
7. Child media consent restrictions are enforced.
8. Contact and admission forms validate, store, notify, and resist basic abuse.
9. Search and SEO features operate only on publishable content.
10. The project migrates and seeds from a clean database.
11. The test suite and production asset build pass.
12. Documentation is complete enough for another developer and school administrator to operate the system.
13. No placeholder official facts are presented as verified school information.
14. No unapproved sensitive information is publicly exposed.
15. The final acceptance report maps each requirement to implementation and evidence.
