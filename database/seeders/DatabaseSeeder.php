<?php

namespace Database\Seeders;

use App\Models\AdmissionEnquiry;
use App\Models\AdmissionEnquiryNote;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\ContactMessageNote;
use App\Models\Department;
use App\Models\Download;
use App\Models\DownloadCategory;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Gallery;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuItem;
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
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = $this->seedAccess();
        $media = $this->seedMedia($admin);
        $pages = $this->seedPages($admin, $media);
        $this->seedMenus($pages);
        $this->seedSettings($admin);
        $this->seedEditorial($admin, $media);
        $download = $this->seedDownloads($admin);
        $this->seedEvents($admin, $media, $download);
        $this->seedSchoolInformation($admin, $media);
        $this->seedGalleries($admin, $media);
        $this->seedFaqs($admin);

        if (app()->environment(['local', 'testing'])) {
            $this->seedDemoWorkflow($admin);
        }
    }

    private function seedAccess(): User
    {
        $permissionSlugs = [
            'pages.view',
            'pages.create',
            'pages.update',
            'pages.publish',
            'media.view',
            'media.approve',
            'news.manage',
            'events.manage',
            'downloads.manage',
            'admissions.view',
            'admissions.export',
            'contacts.view',
            'settings.manage',
            'users.manage',
            'audit.view',
        ];

        foreach ($permissionSlugs as $slug) {
            Permission::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => Str::headline(str_replace('.', ' ', $slug)),
                    'group_name' => Str::before($slug, '.'),
                    'description' => 'Seeded permission for '.$slug.'.',
                ]
            );
        }

        $roles = [
            'super-administrator' => 'Super Administrator',
            'school-administrator' => 'School Administrator',
            'content-editor' => 'Content Editor',
            'admissions-officer' => 'Admissions Officer',
            'teacher-contributor' => 'Teacher Contributor',
        ];

        foreach ($roles as $slug => $name) {
            Role::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => 'Seeded local role: '.$name.'.', 'is_system' => true]
            );
        }

        $super = Role::query()->where('slug', 'super-administrator')->firstOrFail();
        $super->permissions()->sync(Permission::query()->pluck('id'));

        $admin = User::query()->updateOrCreate(
            ['email' => 'local.admin@example.test'],
            [
                'name' => 'Local Super Administrator',
                'password' => Hash::make('ChangeMeLocalOnly!'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        DB::table('role_user')->updateOrInsert(
            ['role_id' => $super->id, 'user_id' => $admin->id],
            ['assigned_by' => $admin->id, 'created_at' => now()]
        );

        return $admin;
    }

    /**
     * @return array<string, Media>
     */
    private function seedMedia(User $admin): array
    {
        $records = [];

        $assets = [
            'logo' => ['assets/images/brand/scb-logo-original.jpg', 'The exact St. Charles Borromeo school logo.', false, true],
            'ceremony' => ['assets/images/school/01-religious-community-ceremony.jpg', 'School community ceremony photograph.', true, false],
            'library' => ['assets/images/school/02-school-library-reading.jpg', 'Learners reading in the school library.', true, false],
            'community' => ['assets/images/school/03-sisters-community-group.jpg', 'School sisters and community members.', true, false],
            'classroom' => ['assets/images/school/05-classroom-learning.jpg', 'Classroom learning photograph.', true, false],
            'football' => ['assets/images/school/06-students-playing-football.jpg', 'Students playing football.', true, false],
            'assembly' => ['assets/images/school/07-students-school-assembly.jpg', 'Students gathered for school assembly.', true, false],
            'playground' => ['assets/images/school/11-school-playground.jpg', 'School playground area.', true, false],
            'staff' => ['assets/images/school/14-school-administration-staff.jpg', 'Administration staff group photograph.', false, false],
        ];

        foreach ($assets as $key => [$path, $alt, $consentRequired, $protected]) {
            $absolutePath = public_path($path);
            $dimensions = File::exists($absolutePath) ? @getimagesize($absolutePath) : null;
            $directory = dirname($path);
            $storedName = basename($path);

            $records[$key] = Media::query()->updateOrCreate(
                ['disk' => 'public', 'directory' => $directory, 'stored_name' => $storedName],
                [
                    'uuid' => (string) Str::uuid(),
                    'original_name' => $storedName,
                    'mime_type' => File::exists($absolutePath) ? File::mimeType($absolutePath) : 'image/jpeg',
                    'extension' => pathinfo($storedName, PATHINFO_EXTENSION),
                    'size_bytes' => File::exists($absolutePath) ? File::size($absolutePath) : 0,
                    'width' => $dimensions[0] ?? null,
                    'height' => $dimensions[1] ?? null,
                    'checksum_sha256' => File::exists($absolutePath) ? hash_file('sha256', $absolutePath) : hash('sha256', $path),
                    'alt_text' => $alt,
                    'caption' => 'Seeded media record from supplied school assets.',
                    'credit' => 'St. Charles Borromeo supplied asset',
                    'visibility' => 'public',
                    'consent_required' => $consentRequired,
                    'consent_confirmed' => $consentRequired,
                    'consent_reference' => $consentRequired ? 'local-seed-confirmed' : null,
                    'publication_restricted' => false,
                    'is_protected_asset' => $protected,
                    'uploaded_by' => $admin->id,
                ]
            );
        }

        $records['download-placeholder'] = Media::query()->updateOrCreate(
            ['disk' => 'public', 'directory' => 'assets/documents', 'stored_name' => 'admissions-pack-placeholder.pdf'],
            [
                'uuid' => (string) Str::uuid(),
                'original_name' => 'admissions-pack-placeholder.pdf',
                'mime_type' => 'application/pdf',
                'extension' => 'pdf',
                'size_bytes' => 0,
                'checksum_sha256' => hash('sha256', 'admissions-pack-placeholder.pdf'),
                'alt_text' => 'Placeholder admissions pack document metadata.',
                'caption' => 'Unverified placeholder document metadata for local development.',
                'visibility' => 'public',
                'consent_required' => false,
                'consent_confirmed' => false,
                'publication_restricted' => false,
                'is_protected_asset' => false,
                'uploaded_by' => $admin->id,
            ]
        );

        return $records;
    }

    /**
     * @param  array<string, Media>  $media
     * @return array<string, Page>
     */
    private function seedPages(User $admin, array $media): array
    {
        $pages = [];
        $pageData = [
            'home' => ['Home', 'home', 'home', $media['ceremony']->id],
            'about' => ['About', 'about', 'standard', $media['community']->id],
            'academics' => ['Academics', 'academics', 'standard', $media['classroom']->id],
            'admissions' => ['Admissions', 'admissions', 'standard', $media['playground']->id],
            'news' => ['News', 'news', 'listing', $media['assembly']->id],
            'events' => ['Events', 'events', 'listing', $media['ceremony']->id],
            'gallery' => ['Gallery', 'gallery', 'listing', $media['football']->id],
            'downloads' => ['Downloads', 'downloads', 'listing', null],
            'contact' => ['Contact', 'contact', 'form', null],
            'faq' => ['FAQ', 'faq', 'listing', null],
            'privacy' => ['Privacy', 'privacy', 'standard', null],
        ];

        foreach ($pageData as $key => [$title, $slug, $template, $featuredMediaId]) {
            $pages[$key] = Page::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'page_type' => $template === 'home' ? 'home' : 'standard',
                    'template_key' => $template,
                    'status' => 'published',
                    'excerpt' => 'Unverified placeholder page copy for local development.',
                    'featured_media_id' => $featuredMediaId,
                    'published_at' => now()->subDay(),
                    'seo_title' => $title.' | St. Charles Borromeo',
                    'seo_description' => 'Seeded placeholder metadata awaiting official school approval.',
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]
            );
        }

        $blocks = [
            ['home', 'hero', 'St. Charles Borromeo Pre & Primary School', 'Faith, care, learning and community.', $media['ceremony']->id],
            ['home', 'feature_cards', 'A Caring School Community', 'Unverified placeholder summary for the approved homepage layout.', $media['library']->id],
            ['home', 'latest_news', 'Latest News', null, null],
            ['home', 'upcoming_events', 'Upcoming Events', null, null],
            ['home', 'call_to_action', 'Admissions Enquiries', 'Unverified placeholder call to action.', null],
            ['about', 'image_text', 'About Our School', 'Unverified placeholder copy. Replace with approved school history and mission.', $media['community']->id],
            ['academics', 'programme_list', 'Learning Programmes', 'Seeded from MySQL programmes.', $media['classroom']->id],
            ['admissions', 'downloads_list', 'Admissions Information', 'Seeded placeholder admissions guidance. Official fees are not invented.', null],
            ['contact', 'contact_details', 'Contact The School', 'Seeded placeholder contact display values come from site settings.', null],
            ['faq', 'faq_accordion', 'Frequently Asked Questions', 'Seeded from MySQL FAQ records.', null],
        ];

        foreach ($blocks as $index => [$pageKey, $type, $heading, $body, $mediaId]) {
            PageBlock::query()->updateOrCreate(
                ['page_id' => $pages[$pageKey]->id, 'block_type' => $type, 'sort_order' => $index + 1],
                [
                    'heading' => $heading,
                    'body' => $body,
                    'media_id' => $mediaId,
                    'settings' => ['source' => 'seeded_mysql'],
                    'is_enabled' => true,
                ]
            );
        }

        return $pages;
    }

    /**
     * @param  array<string, Page>  $pages
     */
    private function seedMenus(array $pages): void
    {
        $menus = [
            'primary' => 'Primary Navigation',
            'footer-school' => 'Footer School',
            'footer-resources' => 'Footer Resources',
            'footer-legal' => 'Footer Legal',
        ];

        foreach ($menus as $location => $name) {
            Menu::query()->updateOrCreate(['location' => $location], ['name' => $name, 'status' => 'active']);
        }

        $primary = Menu::query()->where('location', 'primary')->firstOrFail();
        $items = [
            ['Home', 'home', 'home', 1],
            ['About', 'about', 'about', 2],
            ['Academics', 'academics', 'academics', 3],
            ['Admissions', 'admissions', 'admissions', 4],
            ['News', 'news.index', 'news', 5],
            ['Events', 'events.index', 'events', 6],
            ['Gallery', 'gallery', 'gallery', 7],
            ['Contact', 'contact', 'contact', 8],
        ];

        foreach ($items as [$label, $route, $pageKey, $order]) {
            MenuItem::query()->updateOrCreate(
                ['menu_id' => $primary->id, 'label' => $label],
                ['page_id' => $pages[$pageKey]->id, 'link_type' => 'route', 'route_name' => $route, 'sort_order' => $order, 'is_active' => true]
            );
        }
    }

    private function seedSettings(User $admin): void
    {
        $settings = [
            ['identity', 'school_name', 'string', 'St. Charles Borromeo Pre & Primary School', true],
            ['identity', 'motto', 'string', 'Placeholder motto awaiting official confirmation', true],
            ['contact', 'primary_email', 'string', 'placeholder@example.test', true],
            ['contact', 'telephone', 'string', '+27 00 000 0000', true],
            ['contact', 'address', 'string', 'Unverified placeholder address', true],
            ['seo', 'default_title', 'string', 'St. Charles Borromeo Pre & Primary School', true],
            ['seo', 'default_description', 'string', 'Seeded placeholder description awaiting official school approval.', true],
        ];

        foreach ($settings as [$group, $key, $type, $value, $public]) {
            SiteSetting::query()->updateOrCreate(
                ['group_name' => $group, 'setting_key' => $key],
                ['value_type' => $type, 'value_json' => ['value' => $value], 'is_public' => $public, 'updated_by' => $admin->id]
            );
        }
    }

    /**
     * @param  array<string, Media>  $media
     */
    private function seedEditorial(User $admin, array $media): void
    {
        $category = PostCategory::query()->updateOrCreate(['slug' => 'school-news'], ['name' => 'School News', 'description' => 'Seeded local category.', 'is_active' => true]);
        $tag = Tag::query()->updateOrCreate(['slug' => 'community'], ['name' => 'Community']);

        $post = Post::query()->updateOrCreate(
            ['slug' => 'young-learners-shine'],
            [
                'post_category_id' => $category->id,
                'author_id' => $admin->id,
                'title' => 'Young Learners Shine',
                'excerpt' => 'Unverified placeholder news excerpt for matching the approved prototype.',
                'body' => 'This seeded article is placeholder content stored in MySQL for local development and visual parity work.',
                'featured_media_id' => $media['assembly']->id,
                'status' => 'published',
                'is_featured' => true,
                'published_at' => now()->subDays(2),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );
        $post->tags()->syncWithoutDetaching([$tag->id]);

        Announcement::query()->updateOrCreate(
            ['title' => 'Admissions Notice'],
            [
                'message' => 'Unverified placeholder announcement for local development.',
                'severity' => 'info',
                'status' => 'published',
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonth(),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );
    }

    private function seedDownloads(User $admin): Download
    {
        $category = DownloadCategory::query()->updateOrCreate(['slug' => 'admissions'], ['name' => 'Admissions', 'description' => 'Seeded local category.', 'is_active' => true]);
        $media = Media::query()->where('stored_name', 'admissions-pack-placeholder.pdf')->firstOrFail();

        return Download::query()->updateOrCreate(
            ['slug' => 'admissions-information-pack'],
            [
                'download_category_id' => $category->id,
                'title' => 'Admissions Information Pack',
                'description' => 'Unverified placeholder download metadata. Replace with official document before launch.',
                'media_id' => $media->id,
                'version' => 'local-seed',
                'publication_date' => now()->toDateString(),
                'status' => 'published',
                'published_at' => now()->subDay(),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );
    }

    /**
     * @param  array<string, Media>  $media
     */
    private function seedEvents(User $admin, array $media, Download $download): void
    {
        $category = EventCategory::query()->updateOrCreate(['slug' => 'school-calendar'], ['name' => 'School Calendar', 'description' => 'Seeded local category.', 'is_active' => true]);

        Event::query()->updateOrCreate(
            ['slug' => 'parent-orientation'],
            [
                'event_category_id' => $category->id,
                'title' => 'Parent Orientation',
                'summary' => 'Unverified placeholder event summary.',
                'body' => 'This seeded event supports the prototype detail screen while final calendar content is confirmed.',
                'featured_media_id' => $media['ceremony']->id,
                'starts_at' => now()->addWeeks(2)->setTime(9, 0),
                'ends_at' => now()->addWeeks(2)->setTime(11, 0),
                'timezone' => config('app.timezone'),
                'venue_name' => 'Unverified placeholder venue',
                'programme_download_id' => $download->id,
                'event_state' => 'scheduled',
                'publication_status' => 'published',
                'published_at' => now()->subDay(),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );
    }

    /**
     * @param  array<string, Media>  $media
     */
    private function seedSchoolInformation(User $admin, array $media): void
    {
        $department = Department::query()->updateOrCreate(
            ['slug' => 'primary-school'],
            ['name' => 'Primary School', 'description' => 'Unverified placeholder department.', 'is_active' => true]
        );

        Programme::query()->updateOrCreate(
            ['slug' => 'primary-learning-programme'],
            [
                'department_id' => $department->id,
                'name' => 'Primary Learning Programme',
                'programme_type' => 'primary',
                'level' => 'Primary',
                'summary' => 'Unverified placeholder programme summary.',
                'body' => 'Seeded programme copy stored in MySQL for visual parity work.',
                'featured_media_id' => $media['classroom']->id,
                'sort_order' => 1,
                'status' => 'published',
                'published_at' => now()->subDay(),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );

        StaffMember::query()->updateOrCreate(
            ['slug' => 'local-seed-administrator'],
            [
                'department_id' => $department->id,
                'name' => 'Local Seed Administrator',
                'job_title' => 'Administration',
                'staff_type' => 'administration',
                'approved_biography' => 'Unverified placeholder staff biography for local development.',
                'photo_media_id' => $media['staff']->id,
                'sort_order' => 1,
                'is_leadership' => true,
                'is_public' => true,
                'is_active' => true,
            ]
        );
    }

    /**
     * @param  array<string, Media>  $media
     */
    private function seedGalleries(User $admin, array $media): void
    {
        $category = GalleryCategory::query()->updateOrCreate(['slug' => 'school-life'], ['name' => 'School Life', 'is_active' => true]);
        $gallery = Gallery::query()->updateOrCreate(
            ['slug' => 'school-life-gallery'],
            [
                'gallery_category_id' => $category->id,
                'title' => 'School Life Gallery',
                'description' => 'Seeded gallery using supplied school images.',
                'cover_media_id' => $media['football']->id,
                'event_date' => now()->subMonth()->toDateString(),
                'status' => 'published',
                'published_at' => now()->subDay(),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );

        foreach (['football', 'library', 'playground', 'assembly'] as $index => $key) {
            GalleryItem::query()->updateOrCreate(
                ['gallery_id' => $gallery->id, 'media_id' => $media[$key]->id],
                ['caption' => 'Seeded gallery item from supplied school assets.', 'sort_order' => $index + 1, 'is_featured' => $index === 0]
            );
        }
    }

    private function seedFaqs(User $admin): void
    {
        $category = FaqCategory::query()->updateOrCreate(['slug' => 'general'], ['name' => 'General', 'is_active' => true]);

        Faq::query()->updateOrCreate(
            ['question' => 'Where should official admissions information be confirmed?'],
            [
                'faq_category_id' => $category->id,
                'answer' => 'This is unverified placeholder FAQ content. Replace with approved school guidance before launch.',
                'sort_order' => 1,
                'status' => 'published',
                'published_at' => now()->subDay(),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );
    }

    private function seedDemoWorkflow(User $admin): void
    {
        $admission = AdmissionEnquiry::query()->updateOrCreate(
            ['reference_code' => 'ADM-LOCAL-0001'],
            [
                'guardian_name' => 'Local Demo Guardian',
                'email' => 'guardian@example.test',
                'telephone' => '+27 00 000 0001',
                'intended_level' => 'Unverified local seed level',
                'intended_year' => (int) now()->addYear()->format('Y'),
                'preferred_contact_method' => 'email',
                'message' => 'Local-only demo admission enquiry.',
                'consent_confirmed' => true,
                'status' => 'new',
                'assigned_to' => $admin->id,
                'source_ip_hash' => hash('sha256', '127.0.0.1'),
                'user_agent_hash' => hash('sha256', 'local-seed'),
            ]
        );

        AdmissionEnquiryNote::query()->updateOrCreate(
            ['admission_enquiry_id' => $admission->id, 'user_id' => $admin->id],
            ['note' => 'Local-only internal note for workflow testing.', 'is_sensitive' => true]
        );

        $message = ContactMessage::query()->updateOrCreate(
            ['reference_code' => 'CON-LOCAL-0001'],
            [
                'full_name' => 'Local Demo Contact',
                'email' => 'contact@example.test',
                'telephone' => '+27 00 000 0002',
                'subject' => 'Local demo contact message',
                'message' => 'Local-only demo contact message.',
                'consent_confirmed' => true,
                'status' => 'new',
                'assigned_to' => $admin->id,
                'source_ip_hash' => hash('sha256', '127.0.0.1'),
                'user_agent_hash' => hash('sha256', 'local-seed'),
            ]
        );

        ContactMessageNote::query()->updateOrCreate(
            ['contact_message_id' => $message->id, 'user_id' => $admin->id],
            ['note' => 'Local-only contact workflow note.', 'is_sensitive' => true]
        );

        AuditLog::query()->create([
            'actor_id' => $admin->id,
            'action' => 'database.seeded',
            'subject_type' => 'database',
            'subject_id' => null,
            'description' => 'Local MySQL seed data installed.',
            'new_values' => ['environment' => app()->environment()],
            'request_id' => (string) Str::uuid(),
        ]);
    }
}
