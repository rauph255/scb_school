<?php

namespace Tests\Feature;

use App\Filament\Widgets\SchoolOverview;
use App\Models\AdmissionEnquiry;
use App\Models\AdmissionEnquiryNote;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\ContactMessageNote;
use App\Models\Department;
use App\Models\Download;
use App\Models\DownloadCategory;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\Media;
use App\Models\MediaUsage;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Permission;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Programme;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\StaffMember;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use ReflectionMethod;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_homepage_renders_school_website_with_credit(): void
    {
        $this->prepareMySqlSchema(seed: true);

        PageBlock::query()
            ->where('block_type', 'welcome')
            ->update(['heading' => 'Database-backed welcome heading']);

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('ST. CHARLES BORROMEO PRE &amp; PRIMARY SCHOOL', false)
            ->assertSee('Database-backed welcome heading', false)
            ->assertSee('Growing Together Through Tree Planting', false)
            ->assertSee('Parent Orientation', false)
            ->assertSee('/staff-portal/login', false)
            ->assertSee('Powered by Falconode (T) Ltd')
            ->assertDontSee('/assets/images/brand/falconode-credit.png')
            ->assertSee('/assets/scb/css/app.css');
    }

    public function test_staff_portal_is_separate_from_admin_panel(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $this->assertDatabaseHas('users', [
            'email' => 'local.teacher@example.test',
            'name' => 'Staff Contributor',
        ]);

        $this->get('/staff-portal/login')
            ->assertOk()
            ->assertSee('Staff portal', false)
            ->assertSee('Teachers and school staff', false)
            ->assertSee('/staff-portal', false)
            ->assertSee('/admin/login', false)
            ->assertDontSee('admin-shell', false)
            ->assertDontSee('Administration panel', false);

        $this->get('/staff-portal')
            ->assertRedirect('/staff-portal/login');

        $this->post('/staff-portal/login', [
            'email' => 'local.teacher@example.test',
            'password' => 'ChangeMeLocalOnly!',
        ])->assertRedirect('/staff-portal');

        $this->get('/staff-portal')
            ->assertOk()
            ->assertSee('staff-shell', false)
            ->assertSee('School calendar', false)
            ->assertSee('Staff resources', false)
            ->assertSee('News contribution queue', false)
            ->assertSee('staff-contribution-form', false)
            ->assertSee('Parent Orientation', false)
            ->assertSee('/staff-portal/logout', false)
            ->assertDontSee('/admin/pages', false)
            ->assertDontSee('Roles & permissions', false);

        $this->get('/admin')
            ->assertForbidden();
    }

    public function test_staff_portal_contribution_form_persists_review_post_to_mysql(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $teacher = User::query()
            ->where('email', 'local.teacher@example.test')
            ->firstOrFail();

        $this->actingAs($teacher)
            ->from('/staff-portal')
            ->post('/staff-portal/contributions', [
                'title' => 'Staff Submitted MySQL Contribution',
                'excerpt' => 'A classroom update submitted by a staff user.',
                'body' => 'Longer classroom update body from the staff portal.',
            ])
            ->assertRedirect('/staff-portal')
            ->assertSessionHas('status', 'Contribution submitted for review.');

        $this->assertDatabaseHas('posts', [
            'title' => 'Staff Submitted MySQL Contribution',
            'slug' => 'staff-submitted-mysql-contribution',
            'excerpt' => 'A classroom update submitted by a staff user.',
            'body' => 'Longer classroom update body from the staff portal.',
            'status' => 'review',
            'author_id' => $teacher->id,
            'created_by' => $teacher->id,
            'updated_by' => $teacher->id,
        ]);

        $post = Post::query()
            ->where('slug', 'staff-submitted-mysql-contribution')
            ->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $teacher->id,
            'action' => 'staff_contribution.submitted',
            'subject_type' => Post::class,
            'subject_id' => $post->id,
            'description' => 'Staff news contribution submitted: Staff Submitted MySQL Contribution',
        ]);

        $this->actingAs($teacher)
            ->get('/staff-portal')
            ->assertOk()
            ->assertSee('Staff Submitted MySQL Contribution', false)
            ->assertSee('review', false);

        $this->get('/news')
            ->assertOk()
            ->assertDontSee('Staff Submitted MySQL Contribution', false);
    }

    public function test_admin_login_and_dashboard_match_approved_ui(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $this->get('/admin')
            ->assertRedirect('/admin/login');

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('admin-login', false)
            ->assertSee('Administration panel', false)
            ->assertSee('Content with care. Access with accountability.', false)
            ->assertSee('Email address', false)
            ->assertSee('method="POST"', false)
            ->assertSee('action="/admin/login"', false);

        $this->post('/admin/login', [
            'email' => 'local.admin@example.test',
            'password' => 'ChangeMeLocalOnly!',
        ])->assertRedirect('/admin');

        $this->get('/admin')
            ->assertOk()
            ->assertSee('admin-shell', false)
            ->assertSee('Dashboard', false)
            ->assertSee('Published pages', false)
            ->assertSee('<strong>'.Page::query()->published()->count().'</strong>', false)
            ->assertSee('Admissions Enquiry', false)
            ->assertSee('Growing Together Through Tree Planting', false)
            ->assertSee('/admin/logout', false)
            ->assertSee('/assets/scb/css/app.css', false)
            ->assertSee('Powered by Falconode (T) Ltd', false)
            ->assertDontSee('/assets/images/brand/falconode-credit.png', false);

        $this->get('/staff-portal')
            ->assertForbidden();
    }

    public function test_about_page_renders_mysql_backed_page_blocks_and_staff(): void
    {
        $this->prepareMySqlSchema(seed: true);

        PageBlock::query()
            ->where('block_type', 'identity')
            ->update(['heading' => 'Database-backed about heading']);

        $response = $this->get('/about');

        $response
            ->assertOk()
            ->assertSee('Database-backed about heading', false)
            ->assertSee('Teaching Community', false)
            ->assertSee('Powered by Falconode (T) Ltd')
            ->assertSee('/assets/scb/css/app.css');
    }

    public function test_academics_page_renders_mysql_backed_page_blocks_and_programmes(): void
    {
        $this->prepareMySqlSchema(seed: true);

        Programme::query()
            ->where('slug', 'primary-learning-programme')
            ->update(['name' => 'Database-backed Programme']);

        $response = $this->get('/academics');

        $response
            ->assertOk()
            ->assertSee('Programmes designed for growth, mastery and curiosity.', false)
            ->assertSee('Database-backed Programme', false)
            ->assertSee('Powered by Falconode (T) Ltd')
            ->assertSee('/assets/scb/css/app.css');
    }

    public function test_admissions_page_renders_mysql_backed_content(): void
    {
        $this->prepareMySqlSchema(seed: true);

        PageBlock::query()
            ->where('block_type', 'journey')
            ->update(['heading' => 'Database-backed admissions journey']);

        $response = $this->get('/admissions');

        $response
            ->assertOk()
            ->assertSee('Database-backed admissions journey', false)
            ->assertSee('Common admissions questions', false)
            ->assertSee('Start a conversation', false)
            ->assertSee('Powered by Falconode (T) Ltd');
    }

    public function test_admission_enquiry_form_persists_to_mysql(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $response = $this
            ->from('/admissions')
            ->post('/admissions/enquiries', [
                'guardian_name' => 'Prospective Parent',
                'telephone' => '+255 700 000 000',
                'email' => 'prospective.parent@example.test',
                'intended_level' => 'Pre-primary',
                'message' => 'Please contact me about available places.',
                'privacy_notice' => '1',
            ]);

        $response
            ->assertRedirect('/admissions')
            ->assertSessionHas('status', 'Admission enquiry submitted.');

        $this->assertDatabaseHas('admission_enquiries', [
            'guardian_name' => 'Prospective Parent',
            'email' => 'prospective.parent@example.test',
            'status' => 'new',
            'consent_confirmed' => true,
        ]);

        $this->assertNotNull(
            AdmissionEnquiry::query()
                ->where('email', 'prospective.parent@example.test')
                ->value('reference_code')
        );
    }

    public function test_news_listing_and_detail_render_mysql_posts(): void
    {
        $this->prepareMySqlSchema(seed: true);

        Post::query()
            ->where('slug', 'young-learners-shine')
            ->update(['title' => 'Database-backed News Story']);

        $this->get('/news')
            ->assertOk()
            ->assertSee('Database-backed News Story', false)
            ->assertSee('/news/young-learners-shine', false);

        $this->get('/news/young-learners-shine')
            ->assertOk()
            ->assertSee('Database-backed News Story', false)
            ->assertSee('Database-backed News Story', false);
    }

    public function test_events_listing_and_detail_render_mysql_events(): void
    {
        $this->prepareMySqlSchema(seed: true);

        Event::query()
            ->where('slug', 'parent-orientation')
            ->update(['title' => 'Database-backed Event']);

        $this->get('/events')
            ->assertOk()
            ->assertSee('Database-backed Event', false)
            ->assertSee('/events/parent-orientation', false);

        $this->get('/events/parent-orientation')
            ->assertOk()
            ->assertSee('Database-backed Event', false)
            ->assertSee('Event information', false);
    }

    public function test_gallery_downloads_faq_and_privacy_render_mysql_content(): void
    {
        $this->prepareMySqlSchema(seed: true);

        Download::query()
            ->where('slug', 'admissions-information-pack')
            ->update(['title' => 'Database-backed Download']);

        Faq::query()
            ->where('question', 'How do I begin an admission enquiry?')
            ->update(['question' => 'Database-backed FAQ']);

        PageBlock::query()
            ->where('block_type', 'privacy_statement')
            ->update(['body' => 'Database-backed privacy statement.']);

        $this->get('/gallery')
            ->assertOk()
            ->assertSee('School Life Gallery', false)
            ->assertSee('Aerial view of the St. Charles Borromeo school campus.', false);

        $this->get('/downloads')
            ->assertOk()
            ->assertSee('No published downloads match your search.', false)
            ->assertDontSee('Database-backed Download', false);

        $this->get('/faq')
            ->assertOk()
            ->assertSee('Database-backed FAQ', false);

        $this->get('/privacy')
            ->assertOk()
            ->assertSee('Database-backed privacy statement.', false)
            ->assertSee('Administrator access', false);
    }

    public function test_contact_message_form_persists_to_mysql(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $response = $this
            ->from('/contact')
            ->post('/contact/messages', [
                'full_name' => 'Website Visitor',
                'telephone' => '+255 711 000 000',
                'email' => 'visitor@example.test',
                'subject' => 'General enquiry',
                'message' => 'Please send me more information.',
                'privacy_notice' => '1',
            ]);

        $response
            ->assertRedirect('/contact')
            ->assertSessionHas('status', 'Message sent to the school office.');

        $this->assertDatabaseHas('contact_messages', [
            'full_name' => 'Website Visitor',
            'email' => 'visitor@example.test',
            'subject' => 'General enquiry',
            'status' => 'new',
            'consent_confirmed' => true,
        ]);

        $this->assertNotNull(
            ContactMessage::query()
                ->where('email', 'visitor@example.test')
                ->value('reference_code')
        );
    }

    public function test_visible_admin_routes_match_reference_navigation(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        Page::query()
            ->where('slug', 'about')
            ->update(['title' => 'Admin-visible MySQL Page']);

        Post::query()
            ->where('slug', 'young-learners-shine')
            ->update(['title' => 'Admin-visible MySQL News']);

        Event::query()
            ->where('slug', 'parent-orientation')
            ->update(['title' => 'Admin-visible MySQL Event']);

        Gallery::query()
            ->where('slug', 'school-life-gallery')
            ->update(['title' => 'Admin-visible MySQL Gallery']);

        Download::query()
            ->where('slug', 'admissions-information-pack')
            ->update(['title' => 'Admin-visible MySQL Download']);

        Programme::query()
            ->where('slug', 'primary-learning-programme')
            ->update(['name' => 'Admin-visible MySQL Programme']);

        StaffMember::query()
            ->where('slug', 'local-seed-teaching-community')
            ->update(['name' => 'Admin-visible MySQL Staff']);

        AdmissionEnquiry::query()
            ->where('reference_code', 'ADM-LOCAL-0001')
            ->update(['guardian_name' => 'Admin-visible MySQL Guardian']);

        ContactMessage::query()
            ->where('reference_code', 'CON-LOCAL-0001')
            ->update(['subject' => 'Admin-visible MySQL Message']);

        Media::query()
            ->where('stored_name', '05-classroom-learning.jpg')
            ->update(['original_name' => 'admin-visible-mysql-media.jpg']);

        User::query()
            ->where('email', 'local.teacher@example.test')
            ->update(['name' => 'Admin-visible MySQL User']);

        Permission::query()
            ->where('slug', 'pages.view')
            ->update(['name' => 'Admin-visible MySQL Permission']);

        Role::query()
            ->where('slug', 'super-administrator')
            ->update(['description' => 'Admin-visible MySQL Role']);

        SiteSetting::query()
            ->where('group_name', 'identity')
            ->where('setting_key', 'motto')
            ->update(['value_json' => ['value' => 'Admin-visible MySQL Motto']]);

        AuditLog::query()
            ->latest()
            ->firstOrFail()
            ->update(['description' => 'Admin-visible MySQL Audit']);

        $this->actingAs($admin)
            ->get('/admin/pages')
            ->assertOk()
            ->assertSee('admin-shell', false)
            ->assertSee('Admin-visible MySQL Page', false)
            ->assertSee('/about', false)
            ->assertSee('/admin/news', false);

        $this->actingAs($admin)
            ->get('/admin/news')
            ->assertOk()
            ->assertSee('admin-shell', false)
            ->assertSee('Admin-visible MySQL News', false)
            ->assertSee('/news/young-learners-shine', false)
            ->assertSee('/admin/news/editor', false);

        $this->actingAs($admin)
            ->get('/admin/events')
            ->assertOk()
            ->assertSee('admin-shell', false)
            ->assertSee('Admin-visible MySQL Event', false)
            ->assertSee('/events/parent-orientation', false)
            ->assertSee('/admin/events/editor', false);

        $this->actingAs($admin)
            ->get('/admin/gallery')
            ->assertOk()
            ->assertSee('admin-shell', false)
            ->assertSee('Admin-visible MySQL Gallery', false)
            ->assertSee('School Life', false)
            ->assertSee('/admin/gallery/editor', false);

        $this->actingAs($admin)
            ->get('/admin/admissions')
            ->assertOk()
            ->assertSee('admin-shell', false)
            ->assertSee('ADM-LOCAL-0001', false)
            ->assertSee('Admin-visible MySQL Guardian', false);

        $this->actingAs($admin)
            ->get('/admin/contact-messages')
            ->assertOk()
            ->assertSee('admin-shell', false)
            ->assertSee('Admin-visible MySQL Message', false)
            ->assertSee('contact@example.test', false);

        $this->actingAs($admin)
            ->get('/admin/downloads')
            ->assertOk()
            ->assertSee('admin-shell', false)
            ->assertSee('Admin-visible MySQL Download', false);

        $this->actingAs($admin)
            ->get('/admin/staff')
            ->assertOk()
            ->assertSee('admin-shell', false)
            ->assertSee('Admin-visible MySQL Staff', false);

        $this->actingAs($admin)
            ->get('/admin/programmes')
            ->assertOk()
            ->assertSee('admin-shell', false)
            ->assertSee('Admin-visible MySQL Programme', false);

        $this->actingAs($admin)
            ->get('/admin/media')
            ->assertOk()
            ->assertSee('admin-shell', false)
            ->assertSee('admin-visible-mysql-media.jpg', false);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('admin-shell', false)
            ->assertSee('Admin-visible MySQL User', false)
            ->assertSee('Teacher Contributor', false);

        $this->actingAs($admin)
            ->get('/admin/roles')
            ->assertOk()
            ->assertSee('admin-shell', false)
            ->assertSee('Admin-visible MySQL Role', false)
            ->assertSee('Admin-visible MySQL Permission', false);

        $this->actingAs($admin)
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('admin-shell', false)
            ->assertSee('Admin-visible MySQL Motto', false);

        $this->actingAs($admin)
            ->get('/admin/audit-log')
            ->assertOk()
            ->assertSee('admin-shell', false)
            ->assertSee('Admin-visible MySQL Audit', false)
            ->assertSee('System Administrator', false);
    }

    public function test_visible_admin_page_editor_updates_mysql_and_audit_log(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $page = Page::query()
            ->where('slug', 'about')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/pages/editor?page=about')
            ->assertOk()
            ->assertSee('form="page-editor-form"', false)
            ->assertSee('name="title"', false)
            ->assertSee('action="http://localhost/admin/pages/'.$page->id.'"', false)
            ->assertSee('About our school', false);

        $this->actingAs($admin)
            ->from('/admin/pages/editor?page=about')
            ->patch(route('admin.pages.update', $page), [
                'title' => 'Admin Saved MySQL Page',
                'slug' => 'admin-saved-mysql-page',
                'status' => 'published',
                'published_at' => '2026-08-04T10:30',
                'robots_index' => '1',
                'seo_title' => 'Admin Saved SEO Title',
                'seo_description' => 'Admin saved SEO description from the visible administration editor.',
            ])
            ->assertRedirect('/admin/pages')
            ->assertSessionHas('status', 'Page saved.');

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Admin Saved MySQL Page',
            'slug' => 'admin-saved-mysql-page',
            'status' => 'published',
            'seo_title' => 'Admin Saved SEO Title',
            'seo_description' => 'Admin saved SEO description from the visible administration editor.',
            'robots_index' => true,
            'updated_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'page.updated',
            'subject_type' => Page::class,
            'subject_id' => $page->id,
            'description' => 'Page updated from visible admin editor: Admin Saved MySQL Page',
        ]);

        $this->actingAs($admin)
            ->get('/admin/pages')
            ->assertOk()
            ->assertSee('Admin Saved MySQL Page', false)
            ->assertSee('/admin/pages/editor?page=admin-saved-mysql-page', false);
    }

    public function test_visible_admin_page_editor_updates_block_visibility_in_mysql(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $page = Page::query()
            ->where('slug', 'about')
            ->firstOrFail();

        $block = PageBlock::query()
            ->where('page_id', $page->id)
            ->where('block_type', 'identity')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/pages/editor?page=about')
            ->assertOk()
            ->assertSee('action="http://localhost/admin/page-blocks/'.$block->id.'/visibility"', false)
            ->assertSee('value="0"', false)
            ->assertSee('Enabled', false);

        $this->actingAs($admin)
            ->from('/admin/pages/editor?page=about')
            ->patch(route('admin.page-blocks.visibility', $block), [
                'is_enabled' => '0',
            ])
            ->assertRedirect('/admin/pages/editor?page=about')
            ->assertSessionHas('status', 'Page block updated.');

        $this->assertDatabaseHas('page_blocks', [
            'id' => $block->id,
            'is_enabled' => false,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'page_block.visibility_updated',
            'subject_type' => PageBlock::class,
            'subject_id' => $block->id,
            'description' => 'Page block visibility updated: About our school / identity',
        ]);

        $this->actingAs($admin)
            ->get('/admin/pages/editor?page=about')
            ->assertOk()
            ->assertSee('Hidden', false);

        $this->get('/about')
            ->assertOk()
            ->assertDontSee($block->heading, false);
    }

    public function test_visible_admin_page_block_builder_manages_the_mysql_lifecycle(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $page = Page::query()
            ->where('slug', 'about')
            ->firstOrFail();

        $identity = PageBlock::query()
            ->where('page_id', $page->id)
            ->where('block_type', 'identity')
            ->firstOrFail();

        $media = Media::query()
            ->publiclyVisible()
            ->where('mime_type', 'like', 'image/%')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/pages/editor?page=about')
            ->assertOk()
            ->assertSee('action="http://localhost/admin/pages/'.$page->id.'/blocks"', false)
            ->assertSee('action="http://localhost/admin/page-blocks/'.$identity->id.'"', false)
            ->assertSee('action="http://localhost/admin/page-blocks/'.$identity->id.'/order"', false)
            ->assertSee('action="http://localhost/admin/page-blocks/'.$identity->id.'/duplicate"', false)
            ->assertSee('name="settings_json"', false)
            ->assertSee('Add content block', false);

        $this->actingAs($admin)
            ->from('/admin/pages/editor?page=about')
            ->patch(route('admin.page-blocks.update', $identity), [
                'block_type' => 'identity',
                'heading' => 'Updated MySQL Identity Block',
                'subheading' => 'Updated block subheading',
                'body' => 'Updated page block body rendered from MySQL.',
                'media_id' => $media->id,
                'settings_json' => json_encode([
                    'eyebrow' => 'Updated identity',
                    'body_secondary' => 'Updated secondary block copy.',
                ], JSON_THROW_ON_ERROR),
                'is_enabled' => '1',
            ])
            ->assertRedirect('/admin/pages/editor?page=about')
            ->assertSessionHas('status', 'Page block saved.');

        $this->assertDatabaseHas('page_blocks', [
            'id' => $identity->id,
            'heading' => 'Updated MySQL Identity Block',
            'subheading' => 'Updated block subheading',
            'body' => 'Updated page block body rendered from MySQL.',
            'media_id' => $media->id,
            'is_enabled' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'page_block.updated',
            'subject_type' => PageBlock::class,
            'subject_id' => $identity->id,
            'description' => 'Page block updated: About our school / identity',
        ]);

        $this->get('/about')
            ->assertOk()
            ->assertSee('Updated MySQL Identity Block', false)
            ->assertSee('Updated page block body rendered from MySQL.', false)
            ->assertSee('Updated secondary block copy.', false);

        $restrictedMedia = Media::factory()->create([
            'stored_name' => 'restricted-page-block-media.jpg',
            'original_name' => 'restricted-page-block-media.jpg',
            'visibility' => 'private',
            'publication_restricted' => true,
            'consent_required' => true,
            'consent_confirmed' => false,
        ]);
        $identity->forceFill(['media_id' => $restrictedMedia->id])->save();

        $this->get('/about')
            ->assertOk()
            ->assertDontSee('restricted-page-block-media.jpg', false);

        $this->actingAs($admin)
            ->from('/admin/pages/editor?page=about')
            ->post(route('admin.page-blocks.store', $page), [
                'block_type' => 'rich_text',
                'heading' => 'New MySQL Rich Text Block',
                'subheading' => 'New block subheading',
                'body' => 'New block body stored in MySQL.',
                'media_id' => $media->id,
                'settings_json' => '{"eyebrow":"New content"}',
                'is_enabled' => '1',
                'visible_from' => '2026-08-05T08:00',
                'visible_until' => '2026-09-05T08:00',
            ])
            ->assertRedirect('/admin/pages/editor?page=about')
            ->assertSessionHas('status', 'Page block added.');

        $created = PageBlock::query()
            ->where('page_id', $page->id)
            ->where('heading', 'New MySQL Rich Text Block')
            ->firstOrFail();

        $this->assertSame('New content', $created->settings['eyebrow']);
        $this->assertTrue($created->is_enabled);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'page_block.created',
            'subject_type' => PageBlock::class,
            'subject_id' => $created->id,
        ]);

        $orderBefore = PageBlock::query()
            ->where('page_id', $page->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $this->actingAs($admin)
            ->patch(route('admin.page-blocks.order', $created), ['direction' => 'up'])
            ->assertRedirect('/admin/pages/editor?page=about')
            ->assertSessionHas('status', 'Page block order updated.');

        $expectedOrder = $orderBefore;
        $lastIndex = count($expectedOrder) - 1;
        [$expectedOrder[$lastIndex - 1], $expectedOrder[$lastIndex]] = [$expectedOrder[$lastIndex], $expectedOrder[$lastIndex - 1]];

        $this->assertSame(
            $expectedOrder,
            PageBlock::query()
                ->where('page_id', $page->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('id')
                ->all(),
        );

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'page_block.reordered',
            'subject_type' => PageBlock::class,
            'subject_id' => $created->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.page-blocks.duplicate', $created))
            ->assertRedirect('/admin/pages/editor?page=about')
            ->assertSessionHas('status', 'Page block duplicated.');

        $duplicate = PageBlock::query()
            ->where('page_id', $page->id)
            ->where('block_type', 'rich_text')
            ->whereKeyNot($created->id)
            ->firstOrFail();

        $this->assertSame($created->heading, $duplicate->heading);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'page_block.duplicated',
            'subject_type' => PageBlock::class,
            'subject_id' => $duplicate->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.page-blocks.destroy', $duplicate))
            ->assertRedirect('/admin/pages/editor?page=about')
            ->assertSessionHas('status', 'Page block deleted.');

        $this->assertDatabaseMissing('page_blocks', ['id' => $duplicate->id]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'page_block.deleted',
            'subject_type' => PageBlock::class,
            'subject_id' => $duplicate->id,
        ]);

        $restrictedAdmin = User::query()->create([
            'name' => 'Page Block Restricted Administrator',
            'email' => 'page-block-restricted@example.test',
            'password' => Hash::make('ChangeMeLocalOnly!'),
            'is_active' => true,
        ]);
        $restrictedAdmin->roles()->attach(
            Role::query()->where('slug', 'admissions-officer')->firstOrFail(),
            ['assigned_by' => $admin->id, 'created_at' => now()],
        );

        $this->actingAs($restrictedAdmin)
            ->post(route('admin.page-blocks.store', $page), [
                'block_type' => 'testimonial',
                'heading' => 'Unauthorized block',
                'is_enabled' => '1',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('page_blocks', [
            'page_id' => $page->id,
            'heading' => 'Unauthorized block',
        ]);
    }

    public function test_visible_admin_news_editor_updates_mysql_tags_and_audit_log(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $post = Post::query()
            ->where('slug', 'young-learners-shine')
            ->firstOrFail();

        $category = PostCategory::query()
            ->where('slug', 'learning')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/news/editor?post=young-learners-shine')
            ->assertOk()
            ->assertSee('form="news-editor-form"', false)
            ->assertSee('name="title"', false)
            ->assertSee('action="http://localhost/admin/news/'.$post->id.'"', false)
            ->assertSee('Growing Together Through Tree Planting', false);

        $this->actingAs($admin)
            ->from('/admin/news/editor?post=young-learners-shine')
            ->patch(route('admin.news.update', $post), [
                'title' => 'Admin Saved MySQL News Story',
                'excerpt' => 'Saved from the visible administration news editor.',
                'body' => 'Full story content saved from the visible admin news editor.',
                'status' => 'published',
                'published_at' => '2026-08-04T11:15',
                'is_featured' => '1',
                'post_category_id' => $category->id,
                'tags' => 'community, visible editor',
            ])
            ->assertRedirect('/admin/news')
            ->assertSessionHas('status', 'Story saved.');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'post_category_id' => $category->id,
            'title' => 'Admin Saved MySQL News Story',
            'slug' => 'admin-saved-mysql-news-story',
            'excerpt' => 'Saved from the visible administration news editor.',
            'body' => 'Full story content saved from the visible admin news editor.',
            'status' => 'published',
            'is_featured' => true,
            'updated_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('tags', [
            'slug' => 'visible-editor',
            'name' => 'Visible Editor',
        ]);

        $updatedPost = Post::query()->with('tags')->findOrFail($post->id);

        $this->assertTrue($updatedPost->tags->contains(fn (Tag $tag): bool => $tag->slug === 'visible-editor'));

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'post.updated',
            'subject_type' => Post::class,
            'subject_id' => $post->id,
            'description' => 'News story updated from visible admin editor: Admin Saved MySQL News Story',
        ]);

        $this->actingAs($admin)
            ->get('/admin/news')
            ->assertOk()
            ->assertSee('Admin Saved MySQL News Story', false)
            ->assertSee('/admin/news/editor?post=admin-saved-mysql-news-story', false);
    }

    public function test_visible_admin_event_editor_updates_mysql_and_audit_log(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $event = Event::query()
            ->where('slug', 'parent-orientation')
            ->firstOrFail();

        $category = EventCategory::query()
            ->where('slug', 'faith')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/events/editor?event=parent-orientation')
            ->assertOk()
            ->assertSee('form="event-editor-form"', false)
            ->assertSee('name="title"', false)
            ->assertSee('action="http://localhost/admin/events/'.$event->id.'"', false)
            ->assertSee('Parent Orientation', false);

        $this->actingAs($admin)
            ->from('/admin/events/editor?event=parent-orientation')
            ->patch(route('admin.events.update', $event), [
                'title' => 'Admin Saved MySQL Event',
                'summary' => 'Saved from the visible administration event editor.',
                'body' => 'Programme details saved from the visible admin event editor.',
                'starts_at' => '2026-08-18T09:00',
                'ends_at' => '2026-08-18T11:30',
                'venue_name' => 'Admin Saved Hall',
                'event_category_id' => $category->id,
                'publication_status' => 'published',
            ])
            ->assertRedirect('/admin/events')
            ->assertSessionHas('status', 'Event saved.');

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'event_category_id' => $category->id,
            'title' => 'Admin Saved MySQL Event',
            'slug' => 'admin-saved-mysql-event',
            'summary' => 'Saved from the visible administration event editor.',
            'body' => 'Programme details saved from the visible admin event editor.',
            'venue_name' => 'Admin Saved Hall',
            'publication_status' => 'published',
            'updated_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'event.updated',
            'subject_type' => Event::class,
            'subject_id' => $event->id,
            'description' => 'Event updated from visible admin editor: Admin Saved MySQL Event',
        ]);

        $this->actingAs($admin)
            ->get('/admin/events')
            ->assertOk()
            ->assertSee('Admin Saved MySQL Event', false)
            ->assertSee('/admin/events/editor?event=admin-saved-mysql-event', false);
    }

    public function test_visible_admin_gallery_editor_updates_mysql_and_audit_log(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $gallery = Gallery::query()
            ->where('slug', 'school-life-gallery')
            ->firstOrFail();
        $items = GalleryItem::query()
            ->where('gallery_id', $gallery->id)
            ->orderBy('sort_order')
            ->limit(2)
            ->get();

        $category = GalleryCategory::query()->create([
            'name' => 'Admin Saved Category',
            'slug' => 'admin-saved-category',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/gallery/editor?gallery=school-life-gallery')
            ->assertOk()
            ->assertSee('form="gallery-editor-form"', false)
            ->assertSee('name="title"', false)
            ->assertSee('action="http://localhost/admin/gallery/'.$gallery->id.'"', false)
            ->assertSee('name="items[0][caption]"', false)
            ->assertSee('name="items[0][sort_order]"', false)
            ->assertSee('name="featured_item_id"', false)
            ->assertSee('id="gallery-image-add-form"', false)
            ->assertSee('School Life Gallery', false)
            ->assertSee('Aerial view of the St. Charles Borromeo school campus.', false);

        $this->actingAs($admin)
            ->from('/admin/gallery/editor?gallery=school-life-gallery')
            ->patch(route('admin.gallery.update', $gallery), [
                'title' => 'Admin Saved MySQL Gallery',
                'description' => 'Saved from the visible administration gallery editor.',
                'gallery_category_id' => $category->id,
                'event_date' => '2026-08-04',
                'status' => 'published',
                'featured_item_id' => $items[0]->id,
                'items' => [
                    [
                        'id' => $items[0]->id,
                        'caption' => 'Updated featured gallery caption',
                        'sort_order' => 12,
                    ],
                    [
                        'id' => $items[1]->id,
                        'caption' => 'Updated second gallery caption',
                        'sort_order' => 3,
                    ],
                ],
            ])
            ->assertRedirect('/admin/gallery')
            ->assertSessionHas('status', 'Gallery saved.');

        $this->assertDatabaseHas('galleries', [
            'id' => $gallery->id,
            'gallery_category_id' => $category->id,
            'title' => 'Admin Saved MySQL Gallery',
            'slug' => 'admin-saved-mysql-gallery',
            'description' => 'Saved from the visible administration gallery editor.',
            'cover_media_id' => $items[0]->media_id,
            'event_date' => '2026-08-04',
            'status' => 'published',
            'updated_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('gallery_items', [
            'id' => $items[0]->id,
            'gallery_id' => $gallery->id,
            'caption' => 'Updated featured gallery caption',
            'sort_order' => 12,
            'is_featured' => true,
        ]);

        $this->assertDatabaseHas('gallery_items', [
            'id' => $items[1]->id,
            'gallery_id' => $gallery->id,
            'caption' => 'Updated second gallery caption',
            'sort_order' => 3,
            'is_featured' => false,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'gallery.updated',
            'subject_type' => Gallery::class,
            'subject_id' => $gallery->id,
            'description' => 'Gallery updated from visible admin editor: Admin Saved MySQL Gallery',
        ]);

        $this->actingAs($admin)
            ->get('/admin/gallery')
            ->assertOk()
            ->assertSee('Admin Saved MySQL Gallery', false)
            ->assertSee('Admin Saved Category', false)
            ->assertSee('/admin/gallery/editor?gallery=admin-saved-mysql-gallery', false);

        $this->actingAs($admin)
            ->get('/admin/gallery/editor?gallery=admin-saved-mysql-gallery')
            ->assertOk()
            ->assertSee('Updated featured gallery caption', false)
            ->assertSee('Updated second gallery caption', false);
    }

    public function test_admin_can_add_and_remove_gallery_images_with_mysql_usage_and_cover_records(): void
    {
        $this->prepareMySqlSchema(seed: true);
        Storage::fake('local');

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();
        $gallery = Gallery::query()
            ->where('slug', 'school-life-gallery')
            ->firstOrFail();
        $media = Media::factory()->create([
            'disk' => 'local',
            'directory' => 'media/testing',
            'original_name' => 'new-gallery-image.jpg',
            'stored_name' => 'new-gallery-image.jpg',
            'alt_text' => 'A newly attached gallery image.',
        ]);
        Storage::disk('local')->put($media->storagePath(), 'test image bytes');

        $this->actingAs($admin)
            ->get('/admin/gallery/editor?gallery=school-life-gallery')
            ->assertOk()
            ->assertSee('action="http://localhost/admin/gallery/'.$gallery->id.'/items"', false)
            ->assertSee('new-gallery-image.jpg', false)
            ->assertSee('Set as cover', false)
            ->assertSee('Remove from gallery', false);

        $this->actingAs($admin)
            ->from('/admin/gallery/editor?gallery=school-life-gallery')
            ->post(route('admin.gallery-items.store', $gallery), [
                'media_id' => $media->id,
                'caption' => 'Added from the gallery editor',
                'is_featured' => true,
            ])
            ->assertRedirect('/admin/gallery/editor?gallery=school-life-gallery')
            ->assertSessionHas('status', 'Image added to gallery.');

        $item = GalleryItem::query()
            ->where('gallery_id', $gallery->id)
            ->where('media_id', $media->id)
            ->firstOrFail();

        $this->assertDatabaseHas('gallery_items', [
            'id' => $item->id,
            'caption' => 'Added from the gallery editor',
            'sort_order' => 7,
            'is_featured' => true,
        ]);
        $this->assertDatabaseHas('media_usages', [
            'media_id' => $media->id,
            'usable_type' => GalleryItem::class,
            'usable_id' => $item->id,
            'field_name' => 'media_id',
        ]);
        $this->assertDatabaseHas('galleries', [
            'id' => $gallery->id,
            'cover_media_id' => $media->id,
            'updated_by' => $admin->id,
        ]);
        $this->assertSame(1, GalleryItem::query()
            ->where('gallery_id', $gallery->id)
            ->where('is_featured', true)
            ->count());
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'gallery.item_added',
            'subject_type' => GalleryItem::class,
            'subject_id' => $item->id,
        ]);

        $this->actingAs($admin)
            ->from('/admin/gallery/editor?gallery=school-life-gallery')
            ->delete(route('admin.gallery-items.destroy', [$gallery, $item]))
            ->assertRedirect('/admin/gallery/editor?gallery=school-life-gallery')
            ->assertSessionHas('status', 'Image removed from gallery.');

        $this->assertDatabaseMissing('gallery_items', ['id' => $item->id]);
        $this->assertDatabaseMissing('media_usages', [
            'media_id' => $media->id,
            'usable_type' => GalleryItem::class,
            'usable_id' => $item->id,
        ]);
        $this->assertDatabaseHas('media', ['id' => $media->id, 'deleted_at' => null]);
        $this->assertSame([1, 2, 3, 4, 5, 6], GalleryItem::query()
            ->where('gallery_id', $gallery->id)
            ->orderBy('sort_order')
            ->pluck('sort_order')
            ->all());
        $this->assertSame(1, GalleryItem::query()
            ->where('gallery_id', $gallery->id)
            ->where('is_featured', true)
            ->count());
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'gallery.item_removed',
            'subject_type' => GalleryItem::class,
            'subject_id' => $item->id,
        ]);
    }

    public function test_gallery_publication_and_media_deletion_enforce_safeguarding_and_usage_rules(): void
    {
        $this->prepareMySqlSchema(seed: true);
        Storage::fake('local');

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();
        $gallery = Gallery::query()
            ->where('slug', 'school-life-gallery')
            ->firstOrFail();
        $gallery->forceFill(['status' => 'draft', 'published_at' => null])->save();
        $media = Media::factory()->create([
            'disk' => 'local',
            'directory' => 'media/testing',
            'original_name' => 'consent-review-image.jpg',
            'stored_name' => 'consent-review-image.jpg',
            'alt_text' => 'Learners taking part in a school activity.',
            'visibility' => 'private',
            'consent_required' => true,
            'consent_confirmed' => false,
            'publication_restricted' => true,
        ]);
        Storage::disk('local')->put($media->storagePath(), 'test image bytes');

        $this->actingAs($admin)
            ->post(route('admin.gallery-items.store', $gallery), [
                'media_id' => $media->id,
                'caption' => 'Awaiting consent review',
            ])
            ->assertRedirect('/admin/gallery/editor?gallery=school-life-gallery');

        $item = GalleryItem::query()
            ->where('gallery_id', $gallery->id)
            ->where('media_id', $media->id)
            ->firstOrFail();

        $this->actingAs($admin)
            ->from('/admin/gallery/editor?gallery=school-life-gallery')
            ->patch(route('admin.gallery.update', $gallery), [
                'title' => $gallery->title,
                'description' => $gallery->description,
                'gallery_category_id' => $gallery->gallery_category_id,
                'event_date' => $gallery->event_date?->toDateString(),
                'status' => 'published',
            ])
            ->assertRedirect('/admin/gallery/editor?gallery=school-life-gallery')
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('galleries', [
            'id' => $gallery->id,
            'status' => 'draft',
            'published_at' => null,
        ]);

        $otherGallery = Gallery::query()->create([
            'title' => 'Other Media Usage Gallery',
            'slug' => 'other-media-usage-gallery',
            'status' => 'draft',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
        $otherItem = GalleryItem::query()->create([
            'gallery_id' => $otherGallery->id,
            'media_id' => $media->id,
            'caption' => 'Second usage',
            'sort_order' => 1,
            'is_featured' => true,
        ]);
        MediaUsage::query()->create([
            'media_id' => $media->id,
            'usable_type' => GalleryItem::class,
            'usable_id' => $otherItem->id,
            'field_name' => 'media_id',
        ]);

        $this->actingAs($admin)
            ->from('/admin/media')
            ->delete(route('admin.media.destroy', $media))
            ->assertRedirect('/admin/media')
            ->assertSessionHasErrors('media_id');

        $this->assertDatabaseHas('media', ['id' => $media->id, 'deleted_at' => null]);

        $this->actingAs($admin)
            ->from('/admin/gallery/editor?gallery=school-life-gallery')
            ->delete(route('admin.gallery-items.destroy-media', [$gallery, $item]))
            ->assertRedirect('/admin/gallery/editor?gallery=school-life-gallery')
            ->assertSessionHasErrors('media_id');

        $this->assertDatabaseHas('gallery_items', ['id' => $item->id]);
        $this->assertDatabaseHas('media', ['id' => $media->id, 'deleted_at' => null]);

        MediaUsage::query()->where('usable_type', GalleryItem::class)->where('usable_id', $otherItem->id)->delete();
        $otherItem->delete();

        $this->actingAs($admin)
            ->from('/admin/gallery/editor?gallery=school-life-gallery')
            ->delete(route('admin.gallery-items.destroy-media', [$gallery, $item]))
            ->assertRedirect('/admin/gallery/editor?gallery=school-life-gallery')
            ->assertSessionHas('status', 'Image and media record deleted.');

        $this->assertDatabaseMissing('gallery_items', ['id' => $item->id]);
        $this->assertSoftDeleted('media', ['id' => $media->id]);
        $this->assertDatabaseMissing('media_usages', [
            'media_id' => $media->id,
            'usable_type' => GalleryItem::class,
            'usable_id' => $item->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'gallery.media_deleted',
            'subject_type' => Media::class,
            'subject_id' => $media->id,
        ]);
    }

    public function test_visible_admin_settings_screen_updates_mysql_and_audit_log(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('form="settings-form"', false)
            ->assertSee('name="school_name"', false)
            ->assertSee('name="default_title"', false)
            ->assertSee('action="http://localhost/admin/settings"', false);

        $this->actingAs($admin)
            ->from('/admin/settings')
            ->patch(route('admin.settings.update'), [
                'school_name' => 'Admin Saved School Name',
                'motto' => 'Admin Saved Motto',
                'default_title' => 'Admin Saved SEO Title',
                'default_description' => 'Admin saved SEO description.',
                'primary_email' => 'saved-settings@example.test',
                'telephone' => '+255 799 000 111',
                'address' => 'Admin Saved Address',
            ])
            ->assertRedirect('/admin/settings')
            ->assertSessionHas('status', 'Settings saved.');

        $settings = SiteSetting::query()
            ->get()
            ->mapWithKeys(fn (SiteSetting $setting): array => [
                $setting->group_name.'.'.$setting->setting_key => $setting->value_json['value'] ?? null,
            ]);

        $this->assertSame('Admin Saved School Name', $settings['identity.school_name']);
        $this->assertSame('Admin Saved Motto', $settings['identity.motto']);
        $this->assertSame('Admin Saved SEO Title', $settings['seo.default_title']);
        $this->assertSame('Admin saved SEO description.', $settings['seo.default_description']);
        $this->assertSame('saved-settings@example.test', $settings['contact.primary_email']);
        $this->assertSame('+255 799 000 111', $settings['contact.telephone']);
        $this->assertSame('Admin Saved Address', $settings['contact.address']);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'settings.updated',
            'subject_type' => SiteSetting::class,
            'subject_id' => null,
            'description' => 'Site settings updated from visible admin settings screen.',
        ]);

        $this->actingAs($admin)
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('Admin Saved School Name', false)
            ->assertSee('Admin Saved Motto', false)
            ->assertSee('saved-settings@example.test', false)
            ->assertSee('Admin Saved Address', false);
    }

    public function test_visible_admin_enquiry_and_message_status_actions_update_mysql_and_audit_log(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $enquiry = AdmissionEnquiry::query()
            ->where('reference_code', 'ADM-LOCAL-0001')
            ->firstOrFail();

        $message = ContactMessage::query()
            ->where('reference_code', 'CON-LOCAL-0001')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/admissions')
            ->assertOk()
            ->assertSee('action="http://localhost/admin/admissions/'.$enquiry->id.'/status"', false)
            ->assertSee('value="responded"', false);

        $this->actingAs($admin)
            ->from('/admin/admissions')
            ->patch(route('admin.admissions.status', $enquiry), [
                'status' => 'responded',
            ])
            ->assertRedirect('/admin/admissions')
            ->assertSessionHas('status', 'Admission enquiry updated.');

        $updatedEnquiry = AdmissionEnquiry::query()->findOrFail($enquiry->id);

        $this->assertSame('responded', $updatedEnquiry->status);
        $this->assertSame($admin->id, $updatedEnquiry->assigned_to);
        $this->assertNotNull($updatedEnquiry->responded_at);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'admission_enquiry.status_updated',
            'subject_type' => AdmissionEnquiry::class,
            'subject_id' => $enquiry->id,
            'description' => 'Admission enquiry status updated: ADM-LOCAL-0001 to Responded',
        ]);

        $this->actingAs($admin)
            ->get('/admin/contact-messages')
            ->assertOk()
            ->assertSee('action="http://localhost/admin/contact-messages/'.$message->id.'/status"', false)
            ->assertSee('value="assigned"', false);

        $this->actingAs($admin)
            ->from('/admin/contact-messages')
            ->patch(route('admin.contact-messages.status', $message), [
                'status' => 'assigned',
            ])
            ->assertRedirect('/admin/contact-messages')
            ->assertSessionHas('status', 'Contact message updated.');

        $updatedMessage = ContactMessage::query()->findOrFail($message->id);

        $this->assertSame('assigned', $updatedMessage->status);
        $this->assertSame($admin->id, $updatedMessage->assigned_to);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'contact_message.status_updated',
            'subject_type' => ContactMessage::class,
            'subject_id' => $message->id,
            'description' => 'Contact message status updated: CON-LOCAL-0001 to Assigned',
        ]);

        $this->actingAs($admin)
            ->get('/admin/admissions')
            ->assertOk()
            ->assertSee('Responded', false);

        $this->actingAs($admin)
            ->get('/admin/contact-messages')
            ->assertOk()
            ->assertSee('Assigned', false);
    }

    public function test_visible_admin_enquiry_and_message_notes_persist_to_mysql_and_audit_log(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $enquiry = AdmissionEnquiry::query()
            ->where('reference_code', 'ADM-LOCAL-0001')
            ->firstOrFail();

        $message = ContactMessage::query()
            ->where('reference_code', 'CON-LOCAL-0001')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/admissions')
            ->assertOk()
            ->assertSee('action="http://localhost/admin/admissions/'.$enquiry->id.'/notes"', false)
            ->assertSee('name="note"', false);

        $this->actingAs($admin)
            ->from('/admin/admissions')
            ->post(route('admin.admissions.notes.store', $enquiry), [
                'note' => 'Call guardian after reviewing intake availability.',
                'is_sensitive' => true,
            ])
            ->assertRedirect('/admin/admissions')
            ->assertSessionHas('status', 'Admission note saved.');

        $this->assertDatabaseHas('admission_enquiry_notes', [
            'admission_enquiry_id' => $enquiry->id,
            'user_id' => $admin->id,
            'note' => 'Call guardian after reviewing intake availability.',
            'is_sensitive' => true,
        ]);

        $this->assertSame($admin->id, $enquiry->fresh()->assigned_to);

        $admissionNote = AdmissionEnquiryNote::query()
            ->where('admission_enquiry_id', $enquiry->id)
            ->latest()
            ->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'admission_enquiry.note_created',
            'subject_type' => AdmissionEnquiry::class,
            'subject_id' => $enquiry->id,
            'description' => 'Admission enquiry note added: ADM-LOCAL-0001',
        ]);

        $this->assertSame(true, $admissionNote->is_sensitive);

        $this->actingAs($admin)
            ->get('/admin/contact-messages')
            ->assertOk()
            ->assertSee('action="http://localhost/admin/contact-messages/'.$message->id.'/notes"', false)
            ->assertSee('name="note"', false);

        $this->actingAs($admin)
            ->from('/admin/contact-messages')
            ->post(route('admin.contact-messages.notes.store', $message), [
                'note' => 'Forwarded to the office for a response.',
                'is_sensitive' => true,
            ])
            ->assertRedirect('/admin/contact-messages')
            ->assertSessionHas('status', 'Message note saved.');

        $this->assertDatabaseHas('contact_message_notes', [
            'contact_message_id' => $message->id,
            'user_id' => $admin->id,
            'note' => 'Forwarded to the office for a response.',
            'is_sensitive' => true,
        ]);

        $contactNote = ContactMessageNote::query()
            ->where('contact_message_id', $message->id)
            ->latest()
            ->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'contact_message.note_created',
            'subject_type' => ContactMessage::class,
            'subject_id' => $message->id,
            'description' => 'Contact message note added: CON-LOCAL-0001',
        ]);

        $this->assertSame(true, $contactNote->is_sensitive);

        $this->actingAs($admin)
            ->get('/admin/admissions')
            ->assertOk()
            ->assertSee('Latest note: Call guardian after reviewing intake availability.', false);

        $this->actingAs($admin)
            ->get('/admin/contact-messages')
            ->assertOk()
            ->assertSee('Latest note: Forwarded to the office for a response.', false);
    }

    public function test_visible_admin_private_enquiry_details_assignments_and_safe_exports_use_mysql(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $enquiry = AdmissionEnquiry::query()
            ->where('reference_code', 'ADM-LOCAL-0001')
            ->firstOrFail();

        $message = ContactMessage::query()
            ->where('reference_code', 'CON-LOCAL-0001')
            ->firstOrFail();

        $enquiry->forceFill(['guardian_name' => '=SUM(1+1)'])->save();
        $message->forceFill(['full_name' => '@IMPORT("unsafe")'])->save();

        $this->actingAs($admin)
            ->get('/admin/admissions')
            ->assertOk()
            ->assertSee('href="http://localhost/admin/admissions/'.$enquiry->id.'"', false)
            ->assertSee('href="http://localhost/admin/admissions/export"', false);

        $this->actingAs($admin)
            ->get(route('admin.admissions.show', $enquiry))
            ->assertOk()
            ->assertSee('Private admission record', false)
            ->assertSee($enquiry->reference_code, false)
            ->assertSee($enquiry->email, false)
            ->assertSee($enquiry->telephone, false)
            ->assertSee($enquiry->message, false)
            ->assertSee('Local-only internal note for workflow testing.', false)
            ->assertSee($admin->name, false)
            ->assertSee('action="http://localhost/admin/admissions/'.$enquiry->id.'/assignment"', false)
            ->assertSee('action="http://localhost/admin/admissions/'.$enquiry->id.'/status"', false)
            ->assertDontSee($enquiry->source_ip_hash, false)
            ->assertDontSee($enquiry->user_agent_hash, false);

        $this->actingAs($admin)
            ->get('/admin/contact-messages')
            ->assertOk()
            ->assertSee('href="http://localhost/admin/contact-messages/'.$message->id.'"', false)
            ->assertSee('href="http://localhost/admin/contact-messages/export"', false);

        $this->actingAs($admin)
            ->get(route('admin.contact-messages.show', $message))
            ->assertOk()
            ->assertSee('Private contact record', false)
            ->assertSee($message->reference_code, false)
            ->assertSee($message->email, false)
            ->assertSee($message->telephone, false)
            ->assertSee($message->message, false)
            ->assertSee('Local-only contact workflow note.', false)
            ->assertSee($admin->name, false)
            ->assertSee('action="http://localhost/admin/contact-messages/'.$message->id.'/assignment"', false)
            ->assertSee('action="http://localhost/admin/contact-messages/'.$message->id.'/status"', false)
            ->assertDontSee($message->source_ip_hash, false)
            ->assertDontSee($message->user_agent_hash, false);

        $assignee = User::query()->create([
            'name' => 'Private Workflow Assignee',
            'email' => 'private-workflow-assignee@example.test',
            'password' => Hash::make('ChangeMeLocalOnly!'),
            'is_active' => true,
        ]);
        $assignee->roles()->attach(
            Role::query()->where('slug', 'super-administrator')->firstOrFail(),
            ['assigned_by' => $admin->id, 'created_at' => now()],
        );

        $this->actingAs($admin)
            ->from(route('admin.admissions.show', $enquiry))
            ->patch(route('admin.admissions.assignment', $enquiry), [
                'assigned_to' => $assignee->id,
            ])
            ->assertRedirect(route('admin.admissions.show', $enquiry))
            ->assertSessionHas('status', 'Admission enquiry assignment saved.');

        $this->actingAs($admin)
            ->from(route('admin.admissions.show', $enquiry))
            ->patch(route('admin.admissions.status', $enquiry), [
                'status' => 'in_progress',
            ])
            ->assertRedirect(route('admin.admissions.show', $enquiry));

        $updatedEnquiry = $enquiry->fresh();

        $this->assertSame($assignee->id, $updatedEnquiry->assigned_to);
        $this->assertSame('in_progress', $updatedEnquiry->status);

        $this->actingAs($admin)
            ->from(route('admin.admissions.show', $enquiry))
            ->post(route('admin.admissions.notes.store', $enquiry), [
                'note' => 'Detailed admission follow-up remains private.',
                'is_sensitive' => true,
            ])
            ->assertRedirect(route('admin.admissions.show', $enquiry));

        $this->actingAs($admin)
            ->from(route('admin.contact-messages.show', $message))
            ->patch(route('admin.contact-messages.assignment', $message), [
                'assigned_to' => $assignee->id,
            ])
            ->assertRedirect(route('admin.contact-messages.show', $message))
            ->assertSessionHas('status', 'Contact message assignment saved.');

        $this->actingAs($admin)
            ->from(route('admin.contact-messages.show', $message))
            ->patch(route('admin.contact-messages.status', $message), [
                'status' => 'assigned',
            ])
            ->assertRedirect(route('admin.contact-messages.show', $message));

        $updatedMessage = $message->fresh();

        $this->assertSame($assignee->id, $updatedMessage->assigned_to);
        $this->assertSame('assigned', $updatedMessage->status);

        $this->actingAs($admin)
            ->from(route('admin.contact-messages.show', $message))
            ->post(route('admin.contact-messages.notes.store', $message), [
                'note' => 'Detailed contact follow-up remains private.',
                'is_sensitive' => true,
            ])
            ->assertRedirect(route('admin.contact-messages.show', $message));

        $this->actingAs($admin)
            ->get(route('admin.admissions.show', $enquiry))
            ->assertOk()
            ->assertSee('Private Workflow Assignee', false)
            ->assertSee('Detailed admission follow-up remains private.', false);

        $this->actingAs($admin)
            ->get(route('admin.contact-messages.show', $message))
            ->assertOk()
            ->assertSee('Private Workflow Assignee', false)
            ->assertSee('Detailed contact follow-up remains private.', false);

        $admissionExport = $this->actingAs($admin)
            ->get(route('admin.admissions.export'))
            ->assertOk()
            ->assertDownload('admission-enquiries-'.today()->format('Y-m-d').'.csv')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $admissionCsv = $admissionExport->streamedContent();

        $this->assertStringContainsString('ADM-LOCAL-0001', $admissionCsv);
        $this->assertStringContainsString("'=SUM(1+1)", $admissionCsv);
        $this->assertStringNotContainsString($enquiry->source_ip_hash, $admissionCsv);
        $this->assertStringNotContainsString('Detailed admission follow-up remains private.', $admissionCsv);

        $contactExport = $this->actingAs($admin)
            ->get(route('admin.contact-messages.export'))
            ->assertOk()
            ->assertDownload('contact-messages-'.today()->format('Y-m-d').'.csv')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $contactCsv = $contactExport->streamedContent();

        $this->assertStringContainsString('CON-LOCAL-0001', $contactCsv);
        $this->assertStringContainsString("'@IMPORT", $contactCsv);
        $this->assertStringNotContainsString($message->source_ip_hash, $contactCsv);
        $this->assertStringNotContainsString('Detailed contact follow-up remains private.', $contactCsv);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'admission_enquiry.assignment_updated',
            'subject_type' => AdmissionEnquiry::class,
            'subject_id' => $enquiry->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'contact_message.assignment_updated',
            'subject_type' => ContactMessage::class,
            'subject_id' => $message->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'admission_enquiry.exported',
            'subject_type' => AdmissionEnquiry::class,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'contact_message.exported',
            'subject_type' => ContactMessage::class,
        ]);

        $restrictedAdmin = User::query()->create([
            'name' => 'Restricted Private Workflow User',
            'email' => 'restricted-private-workflow@example.test',
            'password' => Hash::make('ChangeMeLocalOnly!'),
            'is_active' => true,
        ]);
        $restrictedAdmin->roles()->attach(
            Role::query()->where('slug', 'admissions-officer')->firstOrFail(),
            ['assigned_by' => $admin->id, 'created_at' => now()],
        );

        $this->actingAs($restrictedAdmin)->get(route('admin.admissions'))->assertForbidden();
        $this->actingAs($restrictedAdmin)->get(route('admin.admissions.show', $enquiry))->assertForbidden();
        $this->actingAs($restrictedAdmin)->get(route('admin.admissions.export'))->assertForbidden();
        $this->actingAs($restrictedAdmin)
            ->patch(route('admin.admissions.assignment', $enquiry), ['assigned_to' => null])
            ->assertForbidden();
        $this->actingAs($restrictedAdmin)->get(route('admin.contact-messages'))->assertForbidden();
        $this->actingAs($restrictedAdmin)->get(route('admin.contact-messages.show', $message))->assertForbidden();
        $this->actingAs($restrictedAdmin)->get(route('admin.contact-messages.export'))->assertForbidden();
        $this->actingAs($restrictedAdmin)
            ->patch(route('admin.contact-messages.assignment', $message), ['assigned_to' => null])
            ->assertForbidden();
        $this->actingAs($restrictedAdmin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('=SUM(1+1)', false)
            ->assertDontSee('@IMPORT("unsafe")', false)
            ->assertSee('restricted contact messages', false);

        $this->get('/admissions')
            ->assertOk()
            ->assertDontSee('Detailed admission follow-up remains private.', false)
            ->assertDontSee('Detailed contact follow-up remains private.', false);
    }

    public function test_visible_admin_download_status_actions_update_mysql_and_audit_log(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $download = Download::query()
            ->where('slug', 'admissions-information-pack')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/downloads')
            ->assertOk()
            ->assertSee('action="http://localhost/admin/downloads/'.$download->id.'/file"', false)
            ->assertSee('Attach the document before publishing', false);

        $this->actingAs($admin)
            ->from('/admin/downloads')
            ->patch(route('admin.downloads.status', $download), [
                'status' => 'archived',
            ])
            ->assertRedirect('/admin/downloads')
            ->assertSessionHas('status', 'Download updated.');

        $updatedDownload = Download::query()->findOrFail($download->id);

        $this->assertSame('archived', $updatedDownload->status);
        $this->assertSame($admin->id, $updatedDownload->updated_by);
        $this->assertNull($updatedDownload->published_at);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'download.status_updated',
            'subject_type' => Download::class,
            'subject_id' => $download->id,
            'description' => 'Download status updated: Admissions Information Pack to Archived',
        ]);

        $this->actingAs($admin)
            ->get('/admin/downloads')
            ->assertOk()
            ->assertSee('Admissions Information Pack', false)
            ->assertSee('Archived', false);
    }

    public function test_visible_admin_download_metadata_actions_update_mysql_and_audit_log(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $download = Download::query()
            ->where('slug', 'admissions-information-pack')
            ->firstOrFail();

        $category = DownloadCategory::query()
            ->where('slug', 'calendar')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/downloads')
            ->assertOk()
            ->assertSee('form="download-metadata-form-'.$download->id.'"', false)
            ->assertSee('action="http://localhost/admin/downloads/'.$download->id.'/metadata"', false)
            ->assertSee('name="download_category_id"', false)
            ->assertSee('name="version"', false);

        $this->actingAs($admin)
            ->from('/admin/downloads')
            ->patch(route('admin.downloads.metadata', $download), [
                'title' => 'Updated Admissions Download',
                'description' => 'Updated MySQL-backed download description.',
                'download_category_id' => $category->id,
                'version' => 'v2-local',
            ])
            ->assertRedirect('/admin/downloads')
            ->assertSessionHas('status', 'Download saved.');

        $updatedDownload = Download::query()->findOrFail($download->id);

        $this->assertSame('Updated Admissions Download', $updatedDownload->title);
        $this->assertSame('updated-admissions-download', $updatedDownload->slug);
        $this->assertSame('Updated MySQL-backed download description.', $updatedDownload->description);
        $this->assertSame($category->id, $updatedDownload->download_category_id);
        $this->assertSame('v2-local', $updatedDownload->version);
        $this->assertSame($admin->id, $updatedDownload->updated_by);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'download.metadata_updated',
            'subject_type' => Download::class,
            'subject_id' => $download->id,
            'description' => 'Download metadata updated: Updated Admissions Download',
        ]);

        $this->actingAs($admin)
            ->get('/admin/downloads')
            ->assertOk()
            ->assertSee('Updated Admissions Download', false)
            ->assertSee('Calendar', false)
            ->assertSee('v2-local', false);
    }

    public function test_visible_admin_download_binary_workflow_uses_private_and_public_storage_with_mysql_metadata(): void
    {
        $this->prepareMySqlSchema(seed: true);
        Storage::fake('local');
        Storage::fake('public');

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $category = DownloadCategory::query()
            ->where('slug', 'admissions')
            ->firstOrFail();

        $seededDownload = Download::query()
            ->where('slug', 'admissions-information-pack')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/downloads')
            ->assertOk()
            ->assertSee('action="http://localhost/admin/downloads"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="publication_date"', false)
            ->assertSee('action="http://localhost/admin/downloads/'.$seededDownload->id.'/file"', false);

        $this->actingAs($admin)
            ->from('/admin/downloads')
            ->post(route('admin.downloads.store'), [
                'file' => UploadedFile::fake()->createWithContent(
                    'family-handbook.pdf',
                    "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF",
                ),
                'title' => 'Family Handbook 2026',
                'description' => 'A MySQL-backed parent resource awaiting review.',
                'download_category_id' => $category->id,
                'version' => '2026.1',
                'publication_date' => '2026-08-05',
            ])
            ->assertRedirect('/admin/downloads')
            ->assertSessionHas('status', 'Download uploaded as draft.');

        $download = Download::query()
            ->where('slug', 'family-handbook-2026')
            ->firstOrFail();
        $media = $download->media()->firstOrFail();
        $privatePath = $media->storagePath();

        $this->assertSame('draft', $download->status);
        $this->assertSame($category->id, $download->download_category_id);
        $this->assertSame('2026.1', $download->version);
        $this->assertSame('2026-08-05', $download->publication_date?->toDateString());
        $this->assertSame($admin->id, $download->created_by);
        $this->assertSame($admin->id, $download->updated_by);
        $this->assertNull($download->published_at);
        $this->assertSame('local', $media->disk);
        $this->assertStringStartsWith('media/', $media->directory);
        $this->assertNotSame('family-handbook.pdf', $media->stored_name);
        $this->assertSame('family-handbook.pdf', $media->original_name);
        $this->assertSame('application/pdf', $media->mime_type);
        $this->assertSame('pdf', $media->extension);
        $this->assertSame($admin->id, $media->uploaded_by);
        $this->assertSame('private', $media->visibility);
        $this->assertTrue($media->publication_restricted);
        $this->assertNull($media->publicUrl());
        Storage::disk('local')->assertExists($privatePath);
        Storage::disk('public')->assertMissing($privatePath);
        $this->assertSame(
            hash('sha256', Storage::disk('local')->get($privatePath)),
            $media->checksum_sha256,
        );

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'media.uploaded',
            'subject_type' => Media::class,
            'subject_id' => $media->id,
            'description' => 'Document uploaded for download review: family-handbook.pdf',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'download.created',
            'subject_type' => Download::class,
            'subject_id' => $download->id,
            'description' => 'Download created with private file: Family Handbook 2026',
        ]);

        $this->get('/downloads')
            ->assertOk()
            ->assertDontSee('Family Handbook 2026', false);

        $this->actingAs($admin)
            ->get(route('admin.media.preview', $media))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($admin)
            ->patch(route('admin.downloads.status', $download), ['status' => 'published'])
            ->assertRedirect('/admin/downloads')
            ->assertSessionHas('status', 'Download updated.');

        $publishedDownload = $download->fresh();
        $publishedMedia = $media->fresh();

        $this->assertSame('published', $publishedDownload->status);
        $this->assertNotNull($publishedDownload->published_at);
        $this->assertSame('local', $publishedMedia->disk);
        $this->assertSame('public', $publishedMedia->visibility);
        $this->assertFalse($publishedMedia->publication_restricted);
        $this->assertStringContainsString('/media/', $publishedMedia->publicUrl());
        Storage::disk('local')->assertExists($privatePath);
        Storage::disk('public')->assertMissing($privatePath);

        $this->get('/downloads')
            ->assertOk()
            ->assertSee('Family Handbook 2026', false)
            ->assertSee('/downloads/family-handbook-2026', false);

        $this->get(route('downloads.show', $publishedDownload))
            ->assertOk()
            ->assertDownload('family-handbook.pdf');

        $this->assertSame(1, $publishedDownload->fresh()->download_count);

        $this->actingAs($admin)
            ->from('/admin/downloads')
            ->patch(route('admin.downloads.file', $publishedDownload), [
                'file' => UploadedFile::fake()->createWithContent(
                    'family-handbook-revised.pdf',
                    "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Version /1.7 >>\nendobj\n%%EOF",
                ),
            ])
            ->assertRedirect('/admin/downloads')
            ->assertSessionHas('status', 'Replacement uploaded; download returned to draft.');

        $replacedDownload = $publishedDownload->fresh();
        $replacement = $replacedDownload->media()->firstOrFail();
        $replacementPath = $replacement->storagePath();
        $supersededMedia = $publishedMedia->fresh();

        $this->assertNotSame($publishedMedia->id, $replacement->id);
        $this->assertSame('draft', $replacedDownload->status);
        $this->assertNull($replacedDownload->published_at);
        $this->assertSame('family-handbook-revised.pdf', $replacement->original_name);
        $this->assertSame('local', $replacement->disk);
        $this->assertSame('private', $replacement->visibility);
        $this->assertTrue($replacement->publication_restricted);
        Storage::disk('local')->assertExists($replacementPath);
        Storage::disk('public')->assertMissing($replacementPath);
        $this->assertSame('local', $supersededMedia->disk);
        $this->assertSame('private', $supersededMedia->visibility);
        $this->assertTrue($supersededMedia->publication_restricted);
        Storage::disk('local')->assertExists($privatePath);
        Storage::disk('public')->assertMissing($privatePath);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'download.file_replaced',
            'subject_type' => Download::class,
            'subject_id' => $download->id,
            'description' => 'Download file replaced and returned to draft: Family Handbook 2026',
        ]);

        $this->get('/downloads')
            ->assertOk()
            ->assertDontSee('Family Handbook 2026', false);

        $this->actingAs($admin)
            ->patch(route('admin.downloads.status', $replacedDownload), ['status' => 'published'])
            ->assertRedirect('/admin/downloads');

        $publicReplacement = $replacement->fresh();

        $this->assertSame('local', $publicReplacement->disk);
        Storage::disk('local')->assertExists($replacementPath);
        Storage::disk('public')->assertMissing($replacementPath);

        $this->actingAs($admin)
            ->patch(route('admin.downloads.status', $replacedDownload), ['status' => 'archived'])
            ->assertRedirect('/admin/downloads');

        $archivedDownload = $replacedDownload->fresh();
        $withdrawnMedia = $replacement->fresh();

        $this->assertSame('archived', $archivedDownload->status);
        $this->assertNull($archivedDownload->published_at);
        $this->assertSame('local', $withdrawnMedia->disk);
        $this->assertSame('private', $withdrawnMedia->visibility);
        $this->assertTrue($withdrawnMedia->publication_restricted);
        $this->assertSame('Attached download is not published.', $withdrawnMedia->restriction_reason);
        $this->assertNull($withdrawnMedia->publicUrl());
        Storage::disk('local')->assertExists($replacementPath);
        Storage::disk('public')->assertMissing($replacementPath);

        $this->get('/downloads')
            ->assertOk()
            ->assertDontSee('Family Handbook 2026', false);

        $downloadCount = Download::query()->count();

        $this->actingAs($admin)
            ->from('/admin/downloads')
            ->post(route('admin.downloads.store'), [
                'file' => UploadedFile::fake()->image('not-a-document.jpg'),
                'title' => 'Rejected image download',
            ])
            ->assertRedirect('/admin/downloads')
            ->assertSessionHasErrors('file');

        $this->assertSame($downloadCount, Download::query()->count());

        $restrictedAdmin = User::query()->create([
            'name' => 'Restricted Download Administrator',
            'email' => 'restricted-downloads@example.test',
            'password' => Hash::make('ChangeMeLocalOnly!'),
            'is_active' => true,
        ]);
        $restrictedAdmin->roles()->attach(
            Role::query()->where('slug', 'admissions-officer')->firstOrFail(),
            ['assigned_by' => $admin->id, 'created_at' => now()],
        );

        $this->actingAs($restrictedAdmin)
            ->post(route('admin.downloads.store'), [
                'file' => UploadedFile::fake()->createWithContent(
                    'unauthorized.pdf',
                    "%PDF-1.4\n%%EOF",
                ),
                'title' => 'Unauthorized download',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('downloads', ['title' => 'Unauthorized download']);
        $this->assertDatabaseMissing('media', ['original_name' => 'unauthorized.pdf']);
    }

    public function test_visible_admin_media_publication_actions_update_mysql_and_audit_log(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $media = Media::query()
            ->where('stored_name', '05-classroom-learning.jpg')
            ->firstOrFail();

        $media->forceFill([
            'visibility' => 'public',
            'consent_required' => true,
            'consent_confirmed' => false,
            'publication_restricted' => false,
            'original_name' => 'Admin Media Consent Photo.jpg',
        ])->save();

        $this->actingAs($admin)
            ->get('/admin/media')
            ->assertOk()
            ->assertSee('action="http://localhost/admin/media/'.$media->id.'/publication"', false)
            ->assertSee('value="restrict"', false)
            ->assertSee('Consent needed', false);

        $this->actingAs($admin)
            ->from('/admin/media')
            ->patch(route('admin.media.publication', $media), [
                'action' => 'restrict',
            ])
            ->assertRedirect('/admin/media')
            ->assertSessionHas('status', 'Media publication updated.');

        $restrictedMedia = Media::query()->findOrFail($media->id);

        $this->assertSame('private', $restrictedMedia->visibility);
        $this->assertTrue($restrictedMedia->publication_restricted);
        $this->assertSame('Restricted from visible admin media workflow.', $restrictedMedia->restriction_reason);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'media.publication_updated',
            'subject_type' => Media::class,
            'subject_id' => $media->id,
            'description' => 'Media publication updated: Admin Media Consent Photo.jpg to Restrict',
        ]);

        $this->actingAs($admin)
            ->from('/admin/media')
            ->patch(route('admin.media.publication', $media), [
                'action' => 'approve',
            ])
            ->assertRedirect('/admin/media')
            ->assertSessionHasErrors('action');

        $stillRestrictedMedia = Media::query()->findOrFail($media->id);

        $this->assertSame('private', $stillRestrictedMedia->visibility);
        $this->assertFalse($stillRestrictedMedia->consent_confirmed);
        $this->assertTrue($stillRestrictedMedia->publication_restricted);

        $this->actingAs($admin)
            ->from('/admin/media')
            ->patch(route('admin.media.metadata', $media), [
                'original_name' => $stillRestrictedMedia->original_name,
                'alt_text' => $stillRestrictedMedia->alt_text,
                'caption' => $stillRestrictedMedia->caption,
                'credit' => $stillRestrictedMedia->credit,
                'consent_required' => '1',
                'consent_confirmed' => '1',
                'consent_reference' => 'CONSENT-LOCAL-0001',
            ])
            ->assertRedirect('/admin/media')
            ->assertSessionHas('status', 'Media metadata saved.');

        $this->actingAs($admin)
            ->from('/admin/media')
            ->patch(route('admin.media.publication', $media), [
                'action' => 'approve',
            ])
            ->assertRedirect('/admin/media')
            ->assertSessionHas('status', 'Media publication updated.');

        $approvedMedia = Media::query()->findOrFail($media->id);

        $this->assertSame('public', $approvedMedia->visibility);
        $this->assertTrue($approvedMedia->consent_confirmed);
        $this->assertSame('CONSENT-LOCAL-0001', $approvedMedia->consent_reference);
        $this->assertFalse($approvedMedia->publication_restricted);
        $this->assertNull($approvedMedia->restriction_reason);

        $this->actingAs($admin)
            ->get('/admin/media')
            ->assertOk()
            ->assertSee('Admin Media Consent Photo.jpg', false)
            ->assertSee('Public', false);
    }

    public function test_visible_admin_media_metadata_actions_update_mysql_and_audit_log(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $media = Media::query()
            ->where('stored_name', '05-classroom-learning.jpg')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/media')
            ->assertOk()
            ->assertSee('form="media-metadata-form-'.$media->id.'"', false)
            ->assertSee('action="http://localhost/admin/media/'.$media->id.'/metadata"', false)
            ->assertSee('name="alt_text"', false)
            ->assertSee('name="caption"', false)
            ->assertSee('name="credit"', false);

        $this->actingAs($admin)
            ->from('/admin/media')
            ->patch(route('admin.media.metadata', $media), [
                'original_name' => 'Updated Classroom Media.jpg',
                'alt_text' => 'Updated classroom learning alt text.',
                'caption' => 'Updated classroom media caption.',
                'credit' => 'Updated school media credit',
            ])
            ->assertRedirect('/admin/media')
            ->assertSessionHas('status', 'Media metadata saved.');

        $updatedMedia = Media::query()->findOrFail($media->id);

        $this->assertSame('Updated Classroom Media.jpg', $updatedMedia->original_name);
        $this->assertSame('Updated classroom learning alt text.', $updatedMedia->alt_text);
        $this->assertSame('Updated classroom media caption.', $updatedMedia->caption);
        $this->assertSame('Updated school media credit', $updatedMedia->credit);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'media.metadata_updated',
            'subject_type' => Media::class,
            'subject_id' => $media->id,
            'description' => 'Media metadata updated: Updated Classroom Media.jpg',
        ]);

        $this->actingAs($admin)
            ->get('/admin/media')
            ->assertOk()
            ->assertSee('Updated Classroom Media.jpg', false)
            ->assertSee('Updated classroom learning alt text.', false)
            ->assertSee('Updated classroom media caption.', false)
            ->assertSee('Updated school media credit', false);
    }

    public function test_visible_admin_media_binary_workflow_uses_private_and_public_storage_with_mysql_metadata(): void
    {
        $this->prepareMySqlSchema(seed: true);
        Storage::fake('local');
        Storage::fake('public');

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $replaceableMedia = Media::query()
            ->where('is_protected_asset', false)
            ->where('mime_type', 'like', 'image/%')
            ->firstOrFail();

        $protectedLogo = Media::query()
            ->where('is_protected_asset', true)
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/media')
            ->assertOk()
            ->assertSee('action="http://localhost/admin/media"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('action="http://localhost/admin/media/'.$replaceableMedia->id.'/replacement"', false)
            ->assertSee('action="http://localhost/admin/media/'.$replaceableMedia->id.'"', false)
            ->assertSee('href="/admin/media/'.$protectedLogo->id.'/preview"', false)
            ->assertSee('Protected', false);

        $this->actingAs($admin)
            ->from('/admin/media')
            ->post(route('admin.media.store'), [
                'file' => UploadedFile::fake()->image('classroom-upload.jpg', 80, 60),
                'alt_text' => 'A classroom upload awaiting review.',
                'caption' => 'Uploaded classroom media.',
                'credit' => 'School communications team',
                'consent_required' => '1',
                'consent_confirmed' => '1',
                'consent_reference' => 'CONSENT-UPLOAD-0001',
            ])
            ->assertRedirect('/admin/media')
            ->assertSessionHas('status', 'Media uploaded for review.');

        $uploaded = Media::query()
            ->where('original_name', 'classroom-upload.jpg')
            ->firstOrFail();
        $uploadedPath = $uploaded->storagePath();

        $this->assertSame('local', $uploaded->disk);
        $this->assertStringStartsWith('media/', $uploaded->directory);
        $this->assertNotSame('classroom-upload.jpg', $uploaded->stored_name);
        $this->assertSame('image/webp', $uploaded->mime_type);
        $this->assertSame('webp', $uploaded->extension);
        $this->assertSame(80, $uploaded->width);
        $this->assertSame(60, $uploaded->height);
        $this->assertSame($admin->id, $uploaded->uploaded_by);
        $this->assertSame('private', $uploaded->visibility);
        $this->assertTrue($uploaded->publication_restricted);
        $this->assertTrue($uploaded->consent_required);
        $this->assertTrue($uploaded->consent_confirmed);
        $this->assertSame('CONSENT-UPLOAD-0001', $uploaded->consent_reference);
        $this->assertNull($uploaded->publicUrl());
        Storage::disk('local')->assertExists($uploadedPath);
        Storage::disk('public')->assertMissing($uploadedPath);
        $this->assertSame(
            hash('sha256', Storage::disk('local')->get($uploadedPath)),
            $uploaded->checksum_sha256,
        );

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'media.uploaded',
            'subject_type' => Media::class,
            'subject_id' => $uploaded->id,
            'description' => 'Media uploaded for review: classroom-upload.jpg',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.media.preview', $uploaded))
            ->assertOk()
            ->assertHeader('content-type', 'image/webp');

        $this->actingAs($admin)
            ->patch(route('admin.media.publication', $uploaded), ['action' => 'approve'])
            ->assertRedirect('/admin/media')
            ->assertSessionHas('status', 'Media publication updated.');

        $published = $uploaded->fresh();

        $this->assertSame('local', $published->disk);
        $this->assertSame('public', $published->visibility);
        $this->assertFalse($published->publication_restricted);
        $this->assertStringContainsString('/media/', $published->publicUrl());
        Storage::disk('local')->assertExists($uploadedPath);
        Storage::disk('public')->assertMissing($uploadedPath);

        $oldPublishedPath = $published->storagePath();

        $this->actingAs($admin)
            ->from('/admin/media')
            ->patch(route('admin.media.replace', $published), [
                'file' => UploadedFile::fake()->image('replacement.png', 120, 90),
            ])
            ->assertRedirect('/admin/media')
            ->assertSessionHas('status', 'Replacement uploaded for review.');

        $replacement = $published->fresh();
        $replacementPath = $replacement->storagePath();

        $this->assertSame('replacement.png', $replacement->original_name);
        $this->assertSame('image/webp', $replacement->mime_type);
        $this->assertSame('webp', $replacement->extension);
        $this->assertSame(120, $replacement->width);
        $this->assertSame(90, $replacement->height);
        $this->assertSame('local', $replacement->disk);
        $this->assertSame('private', $replacement->visibility);
        $this->assertTrue($replacement->publication_restricted);
        $this->assertFalse($replacement->consent_confirmed);
        $this->assertNull($replacement->consent_reference);
        $this->assertNull($replacement->publicUrl());
        Storage::disk('public')->assertMissing($oldPublishedPath);
        Storage::disk('local')->assertExists($replacementPath);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'media.replaced',
            'subject_type' => Media::class,
            'subject_id' => $replacement->id,
            'description' => 'Media binary replaced for review: replacement.png',
        ]);

        $this->actingAs($admin)
            ->from('/admin/media')
            ->patch(route('admin.media.publication', $replacement), ['action' => 'approve'])
            ->assertRedirect('/admin/media')
            ->assertSessionHasErrors('action');

        $this->actingAs($admin)
            ->patch(route('admin.media.metadata', $replacement), [
                'original_name' => $replacement->original_name,
                'alt_text' => $replacement->alt_text,
                'caption' => $replacement->caption,
                'credit' => $replacement->credit,
                'consent_required' => '1',
                'consent_confirmed' => '1',
                'consent_reference' => 'CONSENT-REPLACEMENT-0001',
            ])
            ->assertRedirect('/admin/media');

        $this->actingAs($admin)
            ->patch(route('admin.media.publication', $replacement), ['action' => 'approve'])
            ->assertRedirect('/admin/media');

        $publicReplacement = $replacement->fresh();
        $this->assertSame('local', $publicReplacement->disk);
        Storage::disk('local')->assertExists($replacementPath);
        Storage::disk('public')->assertMissing($replacementPath);

        $this->actingAs($admin)
            ->delete(route('admin.media.destroy', $publicReplacement))
            ->assertRedirect('/admin/media')
            ->assertSessionHas('status', 'Media deleted.');

        $deleted = Media::withTrashed()->findOrFail($publicReplacement->id);

        $this->assertTrue($deleted->trashed());
        $this->assertSame('local', $deleted->disk);
        $this->assertSame('private', $deleted->visibility);
        $this->assertTrue($deleted->publication_restricted);
        Storage::disk('public')->assertMissing($replacementPath);
        Storage::disk('local')->assertExists($replacementPath);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'media.deleted',
            'subject_type' => Media::class,
            'subject_id' => $deleted->id,
            'description' => 'Media deleted: replacement.png',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.media.replace', $protectedLogo), [
                'file' => UploadedFile::fake()->image('replacement-logo.jpg'),
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->delete(route('admin.media.destroy', $protectedLogo))
            ->assertForbidden();

        $this->assertDatabaseHas('media', [
            'id' => $protectedLogo->id,
            'is_protected_asset' => true,
            'deleted_at' => null,
        ]);

        $mediaCount = Media::query()->count();

        $this->actingAs($admin)
            ->from('/admin/media')
            ->post(route('admin.media.store'), [
                'file' => UploadedFile::fake()->create('unsafe.svg', 10, 'image/svg+xml'),
                'alt_text' => 'Unsafe SVG upload.',
            ])
            ->assertRedirect('/admin/media')
            ->assertSessionHasErrors('file');

        $this->assertSame($mediaCount, Media::query()->count());

        $restrictedAdmin = User::query()->create([
            'name' => 'Restricted Media Administrator',
            'email' => 'restricted-media@example.test',
            'password' => Hash::make('ChangeMeLocalOnly!'),
            'is_active' => true,
        ]);
        $restrictedAdmin->roles()->attach(
            Role::query()->where('slug', 'admissions-officer')->firstOrFail(),
            ['assigned_by' => $admin->id, 'created_at' => now()],
        );

        $this->actingAs($restrictedAdmin)
            ->post(route('admin.media.store'), [
                'file' => UploadedFile::fake()->image('unauthorized.jpg'),
                'alt_text' => 'Unauthorized upload.',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('media', ['original_name' => 'unauthorized.jpg']);
    }

    public function test_visible_admin_staff_and_programme_actions_update_mysql_and_audit_log(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $staff = StaffMember::query()
            ->where('slug', 'local-seed-teaching-community')
            ->firstOrFail();

        $programme = Programme::query()
            ->where('slug', 'primary-learning-programme')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/staff')
            ->assertOk()
            ->assertSee('action="http://localhost/admin/staff/'.$staff->id.'/visibility"', false)
            ->assertSee('value="inactive"', false);

        $this->actingAs($admin)
            ->from('/admin/staff')
            ->patch(route('admin.staff.visibility', $staff), [
                'visibility' => 'inactive',
            ])
            ->assertRedirect('/admin/staff')
            ->assertSessionHas('status', 'Staff visibility updated.');

        $updatedStaff = StaffMember::query()->findOrFail($staff->id);

        $this->assertFalse($updatedStaff->is_active);
        $this->assertFalse($updatedStaff->is_public);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'staff_member.visibility_updated',
            'subject_type' => StaffMember::class,
            'subject_id' => $staff->id,
            'description' => 'Staff visibility updated: Teaching Community to Inactive',
        ]);

        $this->actingAs($admin)
            ->get('/admin/programmes')
            ->assertOk()
            ->assertSee('action="http://localhost/admin/programmes/'.$programme->id.'/status"', false)
            ->assertSee('value="archived"', false);

        $this->actingAs($admin)
            ->from('/admin/programmes')
            ->patch(route('admin.programmes.status', $programme), [
                'status' => 'archived',
            ])
            ->assertRedirect('/admin/programmes')
            ->assertSessionHas('status', 'Programme updated.');

        $updatedProgramme = Programme::query()->findOrFail($programme->id);

        $this->assertSame('archived', $updatedProgramme->status);
        $this->assertSame($admin->id, $updatedProgramme->updated_by);
        $this->assertNull($updatedProgramme->published_at);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'programme.status_updated',
            'subject_type' => Programme::class,
            'subject_id' => $programme->id,
            'description' => 'Programme status updated: Primary Learning Programme to Archived',
        ]);

        $this->actingAs($admin)
            ->get('/admin/staff')
            ->assertOk()
            ->assertSee('Inactive', false);

        $this->actingAs($admin)
            ->get('/admin/programmes')
            ->assertOk()
            ->assertSee('Archived', false);
    }

    public function test_visible_admin_staff_and_programme_metadata_actions_update_mysql_and_audit_log(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $department = Department::query()
            ->where('slug', 'primary-school')
            ->firstOrFail();

        $staff = StaffMember::query()
            ->where('slug', 'local-seed-teaching-community')
            ->firstOrFail();

        $programme = Programme::query()
            ->where('slug', 'primary-learning-programme')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/staff')
            ->assertOk()
            ->assertSee('form="staff-metadata-form-'.$staff->id.'"', false)
            ->assertSee('action="http://localhost/admin/staff/'.$staff->id.'/metadata"', false)
            ->assertSee('name="department_id"', false)
            ->assertSee('name="sort_order"', false);

        $this->actingAs($admin)
            ->from('/admin/staff')
            ->patch(route('admin.staff.metadata', $staff), [
                'name' => 'Updated Teaching Community',
                'job_title' => 'Updated Teaching Team',
                'department_id' => $department->id,
                'sort_order' => 9,
            ])
            ->assertRedirect('/admin/staff')
            ->assertSessionHas('status', 'Staff profile saved.');

        $updatedStaff = StaffMember::query()->findOrFail($staff->id);

        $this->assertSame('Updated Teaching Community', $updatedStaff->name);
        $this->assertSame('Updated Teaching Team', $updatedStaff->job_title);
        $this->assertSame($department->id, $updatedStaff->department_id);
        $this->assertSame(9, $updatedStaff->sort_order);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'staff_member.metadata_updated',
            'subject_type' => StaffMember::class,
            'subject_id' => $staff->id,
            'description' => 'Staff metadata updated: Updated Teaching Community',
        ]);

        $this->actingAs($admin)
            ->get('/admin/programmes')
            ->assertOk()
            ->assertSee('form="programme-metadata-form-'.$programme->id.'"', false)
            ->assertSee('action="http://localhost/admin/programmes/'.$programme->id.'/metadata"', false)
            ->assertSee('name="summary"', false);

        $this->actingAs($admin)
            ->from('/admin/programmes')
            ->patch(route('admin.programmes.metadata', $programme), [
                'name' => 'Updated Primary Programme',
                'level' => 'Updated Primary Level',
                'summary' => 'Updated MySQL-backed programme summary.',
            ])
            ->assertRedirect('/admin/programmes')
            ->assertSessionHas('status', 'Programme saved.');

        $updatedProgramme = Programme::query()->findOrFail($programme->id);

        $this->assertSame('Updated Primary Programme', $updatedProgramme->name);
        $this->assertSame('Updated Primary Level', $updatedProgramme->level);
        $this->assertSame('Updated MySQL-backed programme summary.', $updatedProgramme->summary);
        $this->assertSame($admin->id, $updatedProgramme->updated_by);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'programme.metadata_updated',
            'subject_type' => Programme::class,
            'subject_id' => $programme->id,
            'description' => 'Programme metadata updated: Updated Primary Programme',
        ]);

        $this->actingAs($admin)
            ->get('/admin/staff')
            ->assertOk()
            ->assertSee('Updated Teaching Community', false)
            ->assertSee('Updated Teaching Team', false);

        $this->actingAs($admin)
            ->get('/admin/programmes')
            ->assertOk()
            ->assertSee('Updated Primary Programme', false)
            ->assertSee('Updated Primary Level', false)
            ->assertSee('Updated MySQL-backed programme summary.', false);
    }

    public function test_visible_admin_user_status_actions_update_mysql_and_audit_log(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $teacher = User::query()
            ->where('email', 'local.teacher@example.test')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('action="http://localhost/admin/users/'.$teacher->id.'/status"', false)
            ->assertSee('value="disabled"', false);

        $this->actingAs($admin)
            ->from('/admin/users')
            ->patch(route('admin.users.status', $teacher), [
                'status' => 'disabled',
            ])
            ->assertRedirect('/admin/users')
            ->assertSessionHas('status', 'User updated.');

        $updatedTeacher = User::query()->findOrFail($teacher->id);

        $this->assertFalse($updatedTeacher->is_active);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'user.status_updated',
            'subject_type' => User::class,
            'subject_id' => $teacher->id,
            'description' => 'User status updated: local.teacher@example.test to Disabled',
        ]);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('Staff Contributor', false)
            ->assertSee('Disabled', false);

        $this->actingAs($admin)
            ->patch(route('admin.users.status', $admin), [
                'status' => 'disabled',
            ])
            ->assertForbidden();

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_visible_admin_user_invite_and_profile_actions_update_mysql_and_audit_log(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $role = Role::query()
            ->where('slug', 'content-editor')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('action="http://localhost/admin/users"', false)
            ->assertSee('Temporary password', false)
            ->assertSee('Invite user', false);

        $this->actingAs($admin)
            ->from('/admin/users')
            ->post(route('admin.users.store'), [
                'name' => 'Invited Admin User',
                'email' => 'invited.admin@example.test',
                'password' => 'TemporaryPass123',
                'role_ids' => [$role->id],
            ])
            ->assertRedirect('/admin/users')
            ->assertSessionHas('status', 'User invited.');

        $createdUser = User::query()
            ->where('email', 'invited.admin@example.test')
            ->with('roles')
            ->firstOrFail();

        $this->assertSame('Invited Admin User', $createdUser->name);
        $this->assertTrue(Hash::check('TemporaryPass123', $createdUser->password));
        $this->assertTrue($createdUser->is_active);
        $this->assertEqualsCanonicalizing(['content-editor'], $createdUser->roles->pluck('slug')->all());

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'user.created',
            'subject_type' => User::class,
            'subject_id' => $createdUser->id,
            'description' => 'User created from visible admin users screen: invited.admin@example.test',
        ]);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('form="user-profile-form-'.$createdUser->id.'"', false)
            ->assertSee('action="http://localhost/admin/users/'.$createdUser->id.'/profile"', false)
            ->assertSee('Invited Admin User', false);

        $this->actingAs($admin)
            ->from('/admin/users')
            ->patch(route('admin.users.profile', $createdUser), [
                'name' => 'Updated Invited User',
                'email' => 'updated.invited@example.test',
            ])
            ->assertRedirect('/admin/users')
            ->assertSessionHas('status', 'User profile saved.');

        $updatedUser = User::query()->findOrFail($createdUser->id);

        $this->assertSame('Updated Invited User', $updatedUser->name);
        $this->assertSame('updated.invited@example.test', $updatedUser->email);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'user.profile_updated',
            'subject_type' => User::class,
            'subject_id' => $createdUser->id,
            'description' => 'User profile updated: updated.invited@example.test',
        ]);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('Updated Invited User', false)
            ->assertSee('updated.invited@example.test', false);
    }

    public function test_visible_admin_user_role_actions_update_mysql_and_audit_log(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $teacher = User::query()
            ->where('email', 'local.teacher@example.test')
            ->firstOrFail();

        $roleIds = Role::query()
            ->whereIn('slug', ['content-editor', 'teacher-contributor'])
            ->orderBy('slug')
            ->pluck('id')
            ->all();

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('form="user-roles-form-'.$teacher->id.'"', false)
            ->assertSee('action="http://localhost/admin/users/'.$teacher->id.'/roles"', false)
            ->assertSee('name="role_ids[]"', false);

        $this->actingAs($admin)
            ->from('/admin/users')
            ->patch(route('admin.users.roles', $teacher), [
                'role_ids' => $roleIds,
            ])
            ->assertRedirect('/admin/users')
            ->assertSessionHas('status', 'User roles saved.');

        $updatedTeacher = User::query()->with('roles')->findOrFail($teacher->id);

        $this->assertEqualsCanonicalizing(
            ['content-editor', 'teacher-contributor'],
            $updatedTeacher->roles->pluck('slug')->all()
        );

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'user.roles_updated',
            'subject_type' => User::class,
            'subject_id' => $teacher->id,
            'description' => 'User roles updated: local.teacher@example.test',
        ]);

        foreach ($roleIds as $roleId) {
            $this->assertDatabaseHas('role_user', [
                'user_id' => $teacher->id,
                'role_id' => $roleId,
                'assigned_by' => $admin->id,
            ]);
        }

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('value="'.$roleIds[0].'" selected', false);

        $this->actingAs($admin)
            ->patch(route('admin.users.roles', $admin), [
                'role_ids' => $roleIds,
            ])
            ->assertForbidden();
    }

    public function test_visible_admin_role_permissions_update_mysql_and_audit_log(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $role = Role::query()
            ->where('slug', 'teacher-contributor')
            ->firstOrFail();

        $permissionIds = Permission::query()
            ->whereIn('slug', ['pages.view', 'media.view'])
            ->orderBy('slug')
            ->pluck('id')
            ->all();

        $this->actingAs($admin)
            ->get('/admin/roles')
            ->assertOk()
            ->assertSee('form="role-permissions-form-'.$role->id.'"', false)
            ->assertSee('action="http://localhost/admin/roles/'.$role->id.'/permissions"', false)
            ->assertSee('name="permission_ids[]"', false);

        $this->actingAs($admin)
            ->from('/admin/roles')
            ->patch(route('admin.roles.permissions', $role), [
                'permission_ids' => $permissionIds,
            ])
            ->assertRedirect('/admin/roles')
            ->assertSessionHas('status', 'Role permissions saved.');

        $updatedRole = Role::query()->with('permissions')->findOrFail($role->id);

        $this->assertEqualsCanonicalizing(
            ['media.view', 'pages.view'],
            $updatedRole->permissions->pluck('slug')->all()
        );

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'role.permissions_updated',
            'subject_type' => Role::class,
            'subject_id' => $role->id,
            'description' => 'Role permissions updated: Teacher Contributor',
        ]);

        foreach ($permissionIds as $permissionId) {
            $this->assertDatabaseHas('permission_role', [
                'role_id' => $role->id,
                'permission_id' => $permissionId,
            ]);
        }

        $this->actingAs($admin)
            ->get('/admin/roles')
            ->assertOk()
            ->assertSee('Teacher Contributor permissions', false)
            ->assertSee('value="'.$permissionIds[0].'" checked', false);
    }

    public function test_parent_portal_is_absent_and_every_portal_has_the_live_experience_shell(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $this->get('/parent-portal')->assertNotFound();

        foreach (['/', '/about', '/staff-portal/login', '/admin/login'] as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertSee('data-site-loader', false)
                ->assertSee('/assets/js/scb-experience.js', false)
                ->assertSee('/assets/css/scb-experience.css', false)
                ->assertSee('Powered by Falconode (T) Ltd', false)
                ->assertDontSee('/assets/images/brand/falconode-credit.png', false)
                ->assertDontSee('href="#"', false)
                ->assertDontSee('Parent portal', false);
        }

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin-core')
            ->assertOk()
            ->assertSee('data-site-loader', false)
            ->assertSee('/assets/js/scb-experience.js', false)
            ->assertSee('Powered by Falconode (T) Ltd', false);
    }

    public function test_public_search_and_calendar_controls_are_functional(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $this->get('/news?q=Growing+Together')
            ->assertOk()
            ->assertSee('Growing Together Through Tree Planting', false)
            ->assertSee('/news/young-learners-shine', false);

        $this->get('/news?q=NoSuchStoryInMySql')
            ->assertOk()
            ->assertSee('No published news matches your search.', false)
            ->assertDontSee('Growing Together Through Tree Planting', false);

        $this->get('/downloads?q=Admissions')
            ->assertOk()
            ->assertSee('No published downloads match your search.', false);

        $this->get('/downloads?q=NoSuchDownloadInMySql')
            ->assertOk()
            ->assertSee('No published downloads match your search.', false);

        $this->get('/events/parent-orientation/calendar.ics')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/calendar; charset=UTF-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="parent-orientation.ics"')
            ->assertSee('BEGIN:VCALENDAR', false)
            ->assertSee('SUMMARY:Parent Orientation', false);
    }

    public function test_visible_admin_create_actions_persist_private_mysql_records(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();
        $department = Department::query()->firstOrFail();

        foreach (['/admin/pages', '/admin/news', '/admin/events', '/admin/gallery', '/admin/staff', '/admin/programmes'] as $uri) {
            $this->actingAs($admin)
                ->get($uri)
                ->assertOk()
                ->assertSee('data-toggle-details', false)
                ->assertDontSee('href="#"', false);
        }

        foreach (['/admin', '/admin/downloads', '/admin/admissions', '/admin/contact-messages', '/admin/media', '/admin/users', '/admin/roles', '/admin/settings', '/admin/audit-log'] as $uri) {
            $this->actingAs($admin)
                ->get($uri)
                ->assertOk()
                ->assertDontSee('href="#"', false)
                ->assertDontSee('data-toast=', false);
        }

        $this->actingAs($admin)->post(route('admin.pages.store'), [
            'title' => 'New MySQL Managed Page',
            'excerpt' => 'Private page draft.',
        ])->assertRedirect('/admin/pages/editor?page=new-mysql-managed-page');

        $this->actingAs($admin)->post(route('admin.news.store'), [
            'title' => 'New MySQL News Draft',
            'excerpt' => 'Private news draft.',
            'body' => 'Draft story body.',
        ])->assertRedirect('/admin/news/editor?post=new-mysql-news-draft');

        $this->actingAs($admin)->post(route('admin.events.store'), [
            'title' => 'New MySQL Calendar Event',
            'starts_at' => '2026-10-20T09:00',
            'venue_name' => 'School hall',
        ])->assertRedirect('/admin/events/editor?event=new-mysql-calendar-event');

        $this->actingAs($admin)->post(route('admin.gallery.store'), [
            'title' => 'New MySQL Gallery Draft',
            'description' => 'Private gallery draft.',
        ])->assertRedirect('/admin/gallery/editor?gallery=new-mysql-gallery-draft');

        $this->actingAs($admin)->post(route('admin.staff.store'), [
            'name' => 'New Private Staff Profile',
            'job_title' => 'Class Teacher',
            'department_id' => $department->id,
        ])->assertRedirect('/admin/staff');

        $this->actingAs($admin)->post(route('admin.programmes.store'), [
            'name' => 'New MySQL Programme Draft',
            'programme_type' => 'Primary learning',
            'level' => 'Standard 4',
            'summary' => 'Private programme draft.',
        ])->assertRedirect('/admin/programmes');

        $this->actingAs($admin)->post(route('admin.roles.store'), [
            'name' => 'New Restricted MySQL Role',
            'description' => 'No permissions until explicitly assigned.',
        ])->assertRedirect('/admin/roles');

        $this->assertDatabaseHas('pages', ['slug' => 'new-mysql-managed-page', 'status' => 'draft']);
        $this->assertDatabaseHas('posts', ['slug' => 'new-mysql-news-draft', 'status' => 'draft']);
        $this->assertDatabaseHas('events', ['slug' => 'new-mysql-calendar-event', 'publication_status' => 'draft']);
        $this->assertDatabaseHas('galleries', ['slug' => 'new-mysql-gallery-draft', 'status' => 'draft']);
        $this->assertDatabaseHas('staff_members', ['slug' => 'new-private-staff-profile', 'is_public' => false, 'is_active' => true]);
        $this->assertDatabaseHas('programmes', ['slug' => 'new-mysql-programme-draft', 'status' => 'draft']);
        $this->assertDatabaseHas('roles', ['slug' => 'new-restricted-mysql-role', 'is_system' => false]);

        foreach (['page.created', 'post.created', 'event.created', 'gallery.created', 'staff_member.created', 'programme.created', 'role.created'] as $action) {
            $this->assertDatabaseHas('audit_logs', ['actor_id' => $admin->id, 'action' => $action]);
        }
    }

    public function test_audit_log_visible_actions_are_authorized_and_functional(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();
        $log = AuditLog::query()->latest()->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/audit-log')
            ->assertOk()
            ->assertSee(route('admin.audit-log.show', $log), false)
            ->assertSee(route('admin.audit-log.export'), false)
            ->assertDontSee('href="#"', false);

        $this->actingAs($admin)
            ->get(route('admin.audit-log.show', $log))
            ->assertOk()
            ->assertSee('Immutable log', false)
            ->assertSee($log->action, false);

        $this->actingAs($admin)
            ->get(route('admin.audit-log.export'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $teacher = User::query()->where('email', 'local.teacher@example.test')->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('admin.audit-log.show', $log))
            ->assertForbidden();
    }

    public function test_internal_filament_admin_resources_remain_mysql_backed(): void
    {
        $this->prepareMySqlSchema(seed: true);

        Post::query()
            ->where('slug', 'young-learners-shine')
            ->update(['title' => 'Admin-visible MySQL news']);

        $admin = User::query()
            ->where('email', 'local.admin@example.test')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin-core')
            ->assertOk()
            ->assertSee('St. Charles Borromeo', false);

        $stats = $this->schoolOverviewStats();

        $this->assertSame('Published pages', $stats[0]->getLabel());
        $this->assertSame(Page::query()->published()->count(), $stats[0]->getValue());
        $this->assertSame('Open enquiries', $stats[3]->getLabel());
        $this->assertSame(AdmissionEnquiry::query()->where('status', 'new')->count(), $stats[3]->getValue());

        $this->actingAs($admin)
            ->get('/admin-core/pages')
            ->assertOk()
            ->assertSee('Home', false);

        $this->actingAs($admin)
            ->get('/admin-core/posts')
            ->assertOk()
            ->assertSee('Admin-visible MySQL news', false);

        $this->actingAs($admin)
            ->get('/admin-core/events')
            ->assertOk()
            ->assertSee('Parent Orientation', false);

        $this->actingAs($admin)
            ->get('/admin-core/admission-enquiries')
            ->assertOk()
            ->assertSee('Admissions Enquiry', false);

        $this->actingAs($admin)
            ->get('/admin-core/contact-messages')
            ->assertOk()
            ->assertSee('Website Visitor', false);
    }

    private function schoolOverviewStats(): array
    {
        $method = new ReflectionMethod(SchoolOverview::class, 'getStats');
        $method->setAccessible(true);

        return $method->invoke(app(SchoolOverview::class));
    }
}
