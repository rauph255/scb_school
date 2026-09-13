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
use App\Models\MediaUsage;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException(
                'The representative development seeder is disabled outside local and testing environments.'
            );
        }

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

        $this->seedDemoWorkflow($admin);
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
            'galleries.manage',
            'downloads.manage',
            'admissions.view',
            'admissions.manage',
            'admissions.export',
            'contacts.view',
            'contacts.manage',
            'contacts.export',
            'staff.manage',
            'programmes.manage',
            'settings.manage',
            'users.manage',
            'roles.manage',
            'audit.view',
        ];

        foreach ($permissionSlugs as $slug) {
            Permission::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => Str::headline(str_replace('.', ' ', $slug)),
                    'group_name' => Str::before($slug, '.'),
                    'description' => 'Allows authorised users to '.str($slug)->replace('.', ' ')->lower().'.',
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
                ['name' => $name, 'description' => $name.' access profile.', 'is_system' => true]
            );
        }

        $super = Role::query()->where('slug', 'super-administrator')->firstOrFail();
        $super->permissions()->sync(Permission::query()->pluck('id'));

        $admin = User::query()->updateOrCreate(
            ['email' => 'local.admin@example.test'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('ChangeMeLocalOnly!'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        DB::table('role_user')->updateOrInsert(
            ['role_id' => $super->id, 'user_id' => $admin->id],
            ['assigned_by' => $admin->id, 'created_at' => now()]
        );

        $teacherRole = Role::query()->where('slug', 'teacher-contributor')->firstOrFail();

        $teacher = User::query()->updateOrCreate(
            ['email' => 'local.teacher@example.test'],
            [
                'name' => 'Staff Contributor',
                'password' => Hash::make('ChangeMeLocalOnly!'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        DB::table('role_user')->updateOrInsert(
            ['role_id' => $teacherRole->id, 'user_id' => $teacher->id],
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

        $logoPath = resource_path('reference/logo-original.jpg');
        $logo = Media::query()->firstOrNew([
            'disk' => 'public',
            'directory' => 'assets/images/brand',
            'stored_name' => 'scb-logo-original.jpg',
        ]);
        $logo->uuid ??= (string) Str::uuid();
        $logo->fill([
            'original_name' => 'scb-logo-original.jpg',
            'mime_type' => File::mimeType($logoPath),
            'extension' => 'jpg',
            'size_bytes' => File::size($logoPath),
            'width' => getimagesize($logoPath)[0] ?? null,
            'height' => getimagesize($logoPath)[1] ?? null,
            'checksum_sha256' => hash_file('sha256', $logoPath),
            'alt_text' => 'The exact St. Charles Borromeo school logo.',
            'caption' => 'Protected St. Charles Borromeo school identity asset.',
            'credit' => 'St. Charles Borromeo supplied asset',
            'visibility' => 'public',
            'consent_required' => false,
            'consent_confirmed' => false,
            'publication_restricted' => false,
            'is_protected_asset' => true,
            'uploaded_by' => $admin->id,
        ]);
        $logo->save();
        $records['logo'] = $logo;

        $assets = [
            'ceremony' => ['school/01-religious-community-ceremony.jpg', 'School community ceremony photograph.', true],
            'library' => ['school/02-school-library-reading.jpg', 'Learners reading in the school library.', true],
            'community' => ['school/03-sisters-community-group.jpg', 'School sisters and community members.', true],
            'classroom' => ['school/05-classroom-learning.jpg', 'Classroom learning photograph.', true],
            'football' => ['school/06-students-playing-football.jpg', 'Students playing football.', true],
            'assembly' => ['school/07-students-school-assembly.jpg', 'Students gathered for school assembly.', true],
            'playground' => ['school/11-school-playground.jpg', 'School playground area.', true],
            'staff' => ['school/14-school-administration-staff.jpg', 'Administration staff group photograph.', false],
            'campus-aerial' => ['school/2026/campus-aerial.webp', 'Aerial view of the St. Charles Borromeo school campus.', false],
            'tree-planting' => ['school/2026/tree-planting-community.webp', 'Pupils and staff taking part in a school tree-planting activity.', true],
            'pupil-recognition' => ['school/2026/pupil-recognition.webp', 'A pupil receiving a certificate during a school recognition ceremony.', true],
            'cultural-performance' => ['school/2026/cultural-performance.webp', 'Pupils presenting a cultural performance at a school celebration.', true],
            'graduation-guest-arrival' => ['school/2026/graduation/graduation-guest-arrival-2026.webp', 'Guests arriving for the 2026 graduation celebration at St. Charles Borromeo Hall.', true, 'Guests arrive for the school graduation celebration.'],
            'graduation-hall' => ['school/2026/graduation/graduation-hall-2026.webp', 'The decorated stage inside St. Charles Borromeo Hall for the 2026 graduation.', false, 'St. Charles Borromeo Hall prepared for the graduation ceremony.'],
            'graduation-welcome' => ['school/2026/graduation/graduation-ceremonial-welcome-2026.webp', 'Graduates forming a ceremonial welcome line during the 2026 school graduation.', true, 'Graduates welcome guests to the celebration.'],
            'graduation-portrait-01' => ['school/2026/graduation/graduation-graduate-portrait-01-2026.webp', 'A graduate holding flowers after the 2026 school ceremony.', true, 'A graduate marks the completion of an important school milestone.'],
            'graduation-portrait-02' => ['school/2026/graduation/graduation-graduate-portrait-02-2026.webp', 'A smiling graduate holding flowers at the 2026 school celebration.', true, 'A joyful moment after the graduation ceremony.'],
            'graduation-portrait-03' => ['school/2026/graduation/graduation-graduate-portrait-03-2026.webp', 'A graduate wearing a commemorative sash and holding flowers.', true, 'A graduate photographed during the school celebration.'],
        ];

        foreach ($assets as $key => $asset) {
            [$path, $alt, $consentRequired] = $asset;
            $caption = $asset[3] ?? 'St. Charles Borromeo school life.';
            $absolutePath = resource_path('reference/'.$path);
            $dimensions = @getimagesize($absolutePath);
            $directory = 'seed/'.dirname($path);
            $storedName = basename($path);
            $storagePath = $directory.'/'.$storedName;

            Storage::disk('local')->put($storagePath, File::get($absolutePath));

            $media = Media::query()->firstOrNew([
                'disk' => 'local',
                'directory' => $directory,
                'stored_name' => $storedName,
            ]);
            $media->uuid ??= (string) Str::uuid();
            $media->fill([
                'original_name' => $storedName,
                'mime_type' => File::mimeType($absolutePath),
                'extension' => pathinfo($storedName, PATHINFO_EXTENSION),
                'size_bytes' => File::size($absolutePath),
                'width' => $dimensions[0] ?? null,
                'height' => $dimensions[1] ?? null,
                'checksum_sha256' => hash_file('sha256', $absolutePath),
                'alt_text' => $alt,
                'caption' => $caption,
                'credit' => 'St. Charles Borromeo supplied asset',
                'visibility' => 'public',
                'consent_required' => $consentRequired,
                'consent_confirmed' => $consentRequired,
                'consent_reference' => $consentRequired ? 'owner-supplied-confirmed' : null,
                'publication_restricted' => false,
                'is_protected_asset' => false,
                'uploaded_by' => $admin->id,
            ]);
            $media->save();
            $this->syncSeedMediaVariants($media, $absolutePath);
            $records[$key] = $media;
        }

        $records['download-pending'] = Media::query()->updateOrCreate(
            ['disk' => 'public', 'directory' => 'assets/documents', 'stored_name' => 'admissions-document-awaiting-file.pdf'],
            [
                'uuid' => (string) Str::uuid(),
                'original_name' => 'admissions-document-awaiting-file.pdf',
                'mime_type' => 'application/pdf',
                'extension' => 'pdf',
                'size_bytes' => 0,
                'checksum_sha256' => hash('sha256', 'admissions-document-awaiting-file.pdf'),
                'alt_text' => null,
                'caption' => 'Admissions document record.',
                'visibility' => 'private',
                'consent_required' => false,
                'consent_confirmed' => false,
                'publication_restricted' => true,
                'restriction_reason' => 'No approved public file has been supplied.',
                'is_protected_asset' => false,
                'uploaded_by' => $admin->id,
            ]
        );

        return $records;
    }

    private function syncSeedMediaVariants(Media $media, string $masterSourcePath): void
    {
        $sourceDirectory = dirname($masterSourcePath).'/variants';

        if (! File::isDirectory($sourceDirectory)) {
            return;
        }

        $stem = pathinfo($masterSourcePath, PATHINFO_FILENAME);
        $variantKeys = [];

        foreach ([480, 960, 1600] as $width) {
            $sourcePath = $sourceDirectory.'/'.$stem.'-'.$width.'.webp';

            if (! File::exists($sourcePath)) {
                continue;
            }

            $dimensions = getimagesize($sourcePath);
            $variantKey = 'responsive-'.$width;
            $storagePath = $media->directory.'/variants/'.$stem.'-'.$width.'.webp';
            Storage::disk('local')->put($storagePath, File::get($sourcePath));

            $media->variants()->updateOrCreate(
                ['variant_key' => $variantKey],
                [
                    'disk' => 'local',
                    'path' => $storagePath,
                    'mime_type' => 'image/webp',
                    'width' => $dimensions[0],
                    'height' => $dimensions[1],
                    'size_bytes' => File::size($sourcePath),
                    'checksum_sha256' => hash_file('sha256', $sourcePath),
                ],
            );
            $variantKeys[] = $variantKey;
        }

        $media->variants()->whereNotIn('variant_key', $variantKeys)->delete();
    }

    /**
     * @param  array<string, Media>  $media
     * @return array<string, Page>
     */
    private function seedPages(User $admin, array $media): array
    {
        $pages = [];
        $pageData = [
            'home' => ['Home', 'home', 'home', $media['campus-aerial']->id, 'Faith, care, learning and community.'],
            'about' => ['About our school', 'about', 'standard', $media['community']->id, 'A community shaped by faith, thoughtful teaching and respectful relationships.'],
            'academics' => ['Academics', 'academics', 'standard', $media['classroom']->id, 'A coherent learning journey from early foundations to confident primary achievement.'],
            'admissions' => ['Admissions', 'admissions', 'standard', $media['playground']->id, 'A clear route from first enquiry to the next admissions step.'],
            'news' => ['News', 'news', 'listing', $media['tree-planting']->id, 'Stories and updates from school life.'],
            'events' => ['Events', 'events', 'listing', $media['cultural-performance']->id, 'Published dates and moments from the school calendar.'],
            'gallery' => ['Gallery', 'gallery', 'listing', $media['pupil-recognition']->id, 'A visual record of learning, community and celebration.'],
            'downloads' => ['Downloads', 'downloads', 'listing', $media['library']->id, 'Approved school documents and resources.'],
            'contact' => ['Contact', 'contact', 'form', $media['campus-aerial']->id, 'Send a message to the school team.'],
            'faq' => ['FAQ', 'faq', 'listing', $media['library']->id, 'Helpful answers for families and visitors.'],
            'privacy' => ['Privacy', 'privacy', 'standard', null, 'How personal information and safeguarding responsibilities are handled.'],
            'search' => ['Search', 'search', 'listing', null, 'Find published information across the school website.'],
        ];

        foreach ($pageData as $key => [$title, $slug, $template, $featuredMediaId, $excerpt]) {
            $pages[$key] = Page::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'page_type' => $template === 'home' ? 'home' : 'standard',
                    'template_key' => $template,
                    'status' => 'published',
                    'excerpt' => $excerpt,
                    'featured_media_id' => $featuredMediaId,
                    'published_at' => now()->subDay(),
                    'seo_title' => $title.' | St. Charles Borromeo',
                    'seo_description' => $excerpt,
                    'robots_index' => $slug !== 'search',
                    'robots_follow' => true,
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]
            );
        }

        $blocks = [
            [
                'home',
                'hero',
                'Growing minds. Forming character.',
                'A classic, faith-centred school experience where every child is known, challenged and prepared to serve.',
                $media['campus-aerial']->id,
                [
                    'badge' => 'St. Charles Borromeo Pre & Primary School',
                    'carousel_media_ids' => [
                        $media['campus-aerial']->id,
                        $media['graduation-welcome']->id,
                        $media['graduation-hall']->id,
                        $media['tree-planting']->id,
                    ],
                    'primary_action' => ['label' => 'Begin admission', 'route' => 'admissions'],
                    'secondary_action' => ['label' => 'Discover our school', 'route' => 'about'],
                    'stats' => [
                        ['label' => 'Faith', 'description' => 'Values for life'],
                        ['label' => 'Care', 'description' => 'Every child matters'],
                        ['label' => 'Excellence', 'description' => 'Confident learners'],
                        ['label' => 'Service', 'description' => 'Responsible citizens'],
                    ],
                ],
            ],
            [
                'home',
                'welcome',
                'A school that feels rooted, calm and ambitious.',
                'Our learning environment brings together strong classroom practice, Catholic identity, creative expression and a caring community.',
                $media['pupil-recognition']->id,
                [
                    'eyebrow' => 'Welcome',
                    'body_secondary' => 'From the first years of pre-primary to the final stage of primary education, children are guided to become thoughtful learners, kind friends and courageous contributors.',
                    'action' => ['label' => 'Our story', 'route' => 'about'],
                ],
            ],
            [
                'home',
                'programme_highlights',
                'Learning shaped around what children need to thrive.',
                'A balanced school experience brings learning, wellbeing, character and participation together.',
                null,
                [
                    'eyebrow' => 'The whole child',
                    'action' => ['label' => 'Explore academics', 'route' => 'academics'],
                    'cards' => [
                        ['icon' => '✦', 'title' => 'Pre-primary', 'description' => 'Playful foundations in language, numeracy, movement and social confidence.'],
                        ['icon' => '▤', 'title' => 'Primary learning', 'description' => 'Structured, engaging teaching that builds understanding and independence.'],
                        ['icon' => '♡', 'title' => 'Pastoral care', 'description' => 'A watchful community that supports wellbeing, dignity and belonging.'],
                        ['icon' => '⚑', 'title' => 'Clubs and sport', 'description' => 'Opportunities to discover talent, teamwork, leadership and joy.'],
                    ],
                ],
            ],
            [
                'home',
                'academic_life',
                'Clear teaching. Active minds. Purposeful progress.',
                'Learning is strengthened through participation, practice, feedback and the confidence to keep improving.',
                $media['cultural-performance']->id,
                [
                    'eyebrow' => 'Academic life',
                    'action' => ['label' => 'View programmes', 'route' => 'academics'],
                ],
            ],
            ['home', 'latest_news', 'Latest News', 'Recent school stories and upcoming moments.', null, ['eyebrow' => 'School life', 'action' => ['label' => 'All news', 'route' => 'news.index']]],
            ['home', 'upcoming_events', 'Upcoming Events', null, null, ['action' => ['label' => 'All events', 'route' => 'events.index']]],
            [
                'home',
                'call_to_action',
                'Come and see the school in action.',
                'The admissions journey is presented clearly: enquire, visit, apply and receive support from the school office.',
                null,
                [
                    'eyebrow' => 'Admissions',
                    'primary_action' => ['label' => 'Admission guide', 'route' => 'admissions'],
                    'secondary_action' => ['label' => 'Book a visit', 'route' => 'contact'],
                ],
            ],
            [
                'about',
                'identity',
                'Tradition gives the school roots. Learning gives every child wings.',
                'The About experience balances the institution’s Catholic heritage with a modern, confident story about learning and care.',
                $media['community']->id,
                [
                    'eyebrow' => 'Our identity',
                    'body_secondary' => 'The page layout gives administrators dedicated areas for school history, leadership messages, mission, vision, values, facilities and governance.',
                    'quote' => 'Education should form capable minds, generous hearts and responsible lives.',
                ],
            ],
            [
                'about',
                'values',
                'Mission, vision and values',
                'The school presents its mission, vision and values as clear commitments for daily life.',
                null,
                [
                    'items' => [
                        ['icon' => '✦', 'label' => 'Mission', 'description' => 'Purposeful education rooted in faith and care.'],
                        ['icon' => '◎', 'label' => 'Vision', 'description' => 'Confident learners prepared to serve society.'],
                        ['icon' => '◆', 'label' => 'Values', 'description' => 'Integrity, excellence, discipline and compassion.'],
                        ['icon' => '♜', 'label' => 'Leadership', 'description' => 'Responsible stewardship and open communication.'],
                    ],
                ],
            ],
            [
                'about',
                'catholic_character',
                'Faith is expressed through welcome, dignity and service.',
                'This content area is managed as a reusable page block, with an image, heading, body copy and call to action.',
                $media['ceremony']->id,
                [
                    'eyebrow' => 'Catholic character',
                    'body_secondary' => 'The visual treatment is formal without being cold: deep plum from the crest, warm gold accents, generous cream surfaces and restrained crimson highlights.',
                    'action' => ['label' => 'Meet the community', 'route' => 'contact'],
                ],
            ],
            [
                'about',
                'leadership_staff',
                'People who guide learning with clarity and care.',
                'Meet the people who support the school community.',
                null,
                ['eyebrow' => 'Leadership and staff'],
            ],
            [
                'academics',
                'learning_journey',
                'Programmes designed for growth, mastery and curiosity.',
                'The UX separates programmes clearly while preserving a single visual language across the school.',
                null,
                [
                    'eyebrow' => 'Learning journey',
                    'cards' => [
                        ['number' => '1', 'title' => 'Pre-primary', 'description' => 'Language, number, movement, creative play and routines that build confidence.', 'target' => 'preprimary'],
                        ['number' => '2', 'title' => 'Lower primary', 'description' => 'Strong foundations in literacy, numeracy, discovery and personal responsibility.', 'target' => 'primary'],
                        ['number' => '3', 'title' => 'Upper primary', 'description' => 'Deeper subject understanding, independence, leadership and preparation for transition.', 'target' => 'primary'],
                    ],
                ],
            ],
            [
                'academics',
                'pre_primary',
                'A joyful beginning with clear purpose.',
                'The page combines emotional reassurance for parents with practical curriculum information.',
                $media['playground']->id,
                [
                    'eyebrow' => 'Pre-primary',
                    'items' => [
                        ['label' => 'Communication', 'description' => 'Listening, speaking, vocabulary and early literacy.'],
                        ['label' => 'Discovery', 'description' => 'Early number, patterns, observation and problem solving.'],
                        ['label' => 'Movement', 'description' => 'Coordination, healthy routines and active play.'],
                        ['label' => 'Belonging', 'description' => 'Friendship, confidence, faith and self-management.'],
                    ],
                ],
            ],
            [
                'academics',
                'primary_school',
                'Knowledge that becomes skill and good judgement.',
                'Subject information is presented in scannable groups with downloadable curriculum documents.',
                $media['library']->id,
                [
                    'eyebrow' => 'Primary school',
                    'items' => [
                        ['label' => 'Core subjects', 'description' => 'Languages, mathematics, science and social studies.'],
                        ['label' => 'Formation', 'description' => 'Religious education, life skills and citizenship.'],
                        ['label' => 'Creative life', 'description' => 'Art, music, performance and practical projects.'],
                        ['label' => 'Wellbeing', 'description' => 'Physical education, sport and personal development.'],
                    ],
                ],
            ],
            [
                'academics',
                'beyond_lessons',
                'Talent grows through participation.',
                'Clubs, sports, worship, leadership and educational visits are given equal visual weight as essential parts of school life.',
                null,
                [
                    'eyebrow' => 'Beyond lessons',
                    'items' => [
                        ['label' => 'Sport', 'description' => 'Teamwork, fitness and fair play.'],
                        ['label' => 'Creative arts', 'description' => 'Expression, courage and celebration.'],
                    ],
                ],
            ],
            ['academics', 'programme_list', 'Learning Programmes', 'Explore the programmes currently published by the school.', $media['classroom']->id, ['source' => 'managed_content']],
            [
                'admissions',
                'journey',
                'A clear four-step admissions journey.',
                'Parents should always know what to do next. Each step has a strong action, expected response and responsible school contact.',
                null,
                [
                    'eyebrow' => 'Join the school',
                    'steps' => [
                        ['label' => 'Make an enquiry', 'description' => 'Tell us the child’s intended level and preferred admission period.'],
                        ['label' => 'Visit the school', 'description' => 'Meet the admissions team and experience the learning environment.'],
                        ['label' => 'Submit application', 'description' => 'Provide the requested documents through the approved process.'],
                        ['label' => 'Receive guidance', 'description' => 'The school communicates assessment, placement and next steps.'],
                    ],
                ],
            ],
            [
                'admissions',
                'prepare',
                'Helpful information before applying.',
                'Review the key information before making an enquiry.',
                null,
                [
                    'eyebrow' => 'What to prepare',
                    'cards' => [
                        ['icon' => '✓', 'title' => 'Entry information', 'description' => 'Age guidance, available levels and placement arrangements.'],
                        ['icon' => '▣', 'title' => 'Documents', 'description' => 'A simple list of approved documents, without collecting sensitive data too early.'],
                        ['icon' => '↓', 'title' => 'Downloads', 'description' => 'Application guide, calendars and school resources.', 'route' => 'downloads'],
                    ],
                ],
            ],
            [
                'admissions',
                'visit',
                'See the places where children learn, play and belong.',
                'A campus visit is a high-value conversion action and remains visible throughout the admissions experience.',
                $media['playground']->id,
                [
                    'eyebrow' => 'Visit the campus',
                    'action' => ['label' => 'Book a school visit', 'route' => 'contact'],
                ],
            ],
            ['admissions', 'downloads_list', 'Admissions Information', 'Approved admissions documents will be listed here when available.', null, ['source' => 'managed_content']],
            ['contact', 'contact_details', 'Contact The School', 'Use the form to send your enquiry to the school team.', null, ['source' => 'managed_content']],
            ['faq', 'faq_accordion', 'Frequently Asked Questions', 'Browse practical guidance for families and visitors.', null, ['source' => 'managed_content']],
            [
                'privacy',
                'privacy_statement',
                'Respectful data use and safeguarding.',
                'This page explains the website approach to privacy, child safeguarding, cookies and responsible access.',
                $media['community']->id,
                [
                    'sections' => [
                        ['heading' => 'Data minimisation', 'body' => 'Public forms should collect only what the school needs to respond. Sensitive records must use secure, purpose-built processes rather than general contact forms.'],
                        ['heading' => 'Children’s photographs', 'body' => 'The school records consent, avoids unnecessary identifying details and provides a clear removal-request process.'],
                        ['heading' => 'Administrator access', 'body' => 'Every administrator should use an individual account, least-privilege permissions, secure authentication and auditable activity.'],
                    ],
                    'links' => ['Privacy notice', 'Safeguarding statement', 'Cookie notice', 'Website terms'],
                ],
            ],
        ];

        foreach ($blocks as $index => [$pageKey, $type, $heading, $body, $mediaId, $settings]) {
            $block = PageBlock::query()->updateOrCreate(
                ['page_id' => $pages[$pageKey]->id, 'block_type' => $type, 'sort_order' => $index + 1],
                [
                    'heading' => $heading,
                    'body' => $body,
                    'media_id' => $mediaId,
                    'settings' => ['source' => 'managed_content', ...$settings],
                    'is_enabled' => true,
                ]
            );

            foreach (($settings['carousel_media_ids'] ?? []) as $carouselMediaId) {
                MediaUsage::query()->updateOrCreate([
                    'media_id' => $carouselMediaId,
                    'usable_type' => PageBlock::class,
                    'usable_id' => $block->id,
                    'field_name' => 'settings.carousel_media_ids',
                ]);
            }
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

        $footerItems = [
            'footer-school' => [
                ['About the school', 'about', 'about', 1],
                ['Academics', 'academics', 'academics', 2],
                ['Admissions', 'admissions', 'admissions', 3],
                ['News and notices', 'news.index', 'news', 4],
            ],
            'footer-resources' => [
                ['School calendar', 'events.index', 'events', 1],
                ['Gallery', 'gallery', 'gallery', 2],
                ['Downloads', 'downloads', 'downloads', 3],
                ['Frequently asked questions', 'faq', 'faq', 4],
            ],
            'footer-legal' => [
                ['Privacy and safeguarding', 'privacy', 'privacy', 1],
                ['Contact', 'contact', 'contact', 2],
            ],
        ];

        foreach ($footerItems as $location => $items) {
            $menu = Menu::query()->where('location', $location)->firstOrFail();

            foreach ($items as [$label, $route, $pageKey, $order]) {
                MenuItem::query()->updateOrCreate(
                    ['menu_id' => $menu->id, 'label' => $label],
                    ['page_id' => $pages[$pageKey]->id, 'link_type' => 'route', 'route_name' => $route, 'sort_order' => $order, 'is_active' => true]
                );
            }
        }
    }

    private function seedSettings(User $admin): void
    {
        $settings = [
            ['identity', 'school_name', 'string', 'St. Charles Borromeo Pre & Primary School', true],
            ['identity', 'motto', 'string', 'Learning with purpose', true],
            ['contact', 'primary_email', 'string', '', true],
            ['contact', 'telephone', 'string', '', true],
            ['contact', 'address', 'string', '', true],
            ['seo', 'default_title', 'string', 'St. Charles Borromeo Pre & Primary School', true],
            ['seo', 'default_description', 'string', 'Discover learning, school life and admissions at St. Charles Borromeo Pre & Primary School.', true],
            ['analytics', 'enabled', 'boolean', false, true],
            ['analytics', 'provider', 'string', 'none', true],
            ['analytics', 'site_id', 'string', '', true],
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
        $category = PostCategory::query()->updateOrCreate(['slug' => 'school-news'], ['name' => 'School News', 'description' => 'News from across the school community.', 'is_active' => true]);
        $learning = PostCategory::query()->updateOrCreate(['slug' => 'learning'], ['name' => 'Learning', 'description' => 'Learning experiences and achievements.', 'is_active' => true]);
        $sport = PostCategory::query()->updateOrCreate(['slug' => 'sport'], ['name' => 'Sport', 'description' => 'Sport, teamwork and participation.', 'is_active' => true]);
        $tag = Tag::query()->updateOrCreate(['slug' => 'community'], ['name' => 'Community']);
        $graduationTag = Tag::query()->updateOrCreate(['slug' => 'graduation'], ['name' => 'Graduation']);

        $graduationPost = Post::query()->updateOrCreate(
            ['slug' => 'st-charles-borromeo-2026-graduation'],
            [
                'post_category_id' => $category->id,
                'author_id' => $admin->id,
                'title' => 'St. Charles Borromeo Celebrates the 2026 Graduation',
                'excerpt' => 'The school community gathered at St. Charles Borromeo Hall on 15 August 2026 for a graduation celebration with guest of honour Solomon Itunda, Mbeya District Commissioner.',
                'body' => "St. Charles Borromeo Pre & Primary School held its 2026 graduation ceremony on 15 August at St. Charles Borromeo Hall. Graduates, families, staff and invited guests came together to mark an important milestone in the pupils' school journey.\n\nThe school welcomed Solomon Itunda, Mbeya District Commissioner, as guest of honour. The occasion recognised the graduates' progress and the support provided by their families, teachers and the wider school community.\n\nThe ceremony reflected the school's commitment to learning, character and service while giving the graduating pupils a joyful and dignified send-off.",
                'featured_media_id' => $media['graduation-welcome']->id,
                'status' => 'published',
                'is_featured' => true,
                'published_at' => now()->subHours(2),
                'seo_title' => '2026 Graduation Celebration | St. Charles Borromeo',
                'seo_description' => 'Highlights from the St. Charles Borromeo graduation held on 15 August 2026 with guest of honour Solomon Itunda, Mbeya District Commissioner.',
                'robots_index' => true,
                'robots_follow' => true,
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ],
        );
        $graduationPost->tags()->syncWithoutDetaching([$tag->id, $graduationTag->id]);

        MediaUsage::query()
            ->where('usable_type', Post::class)
            ->where('usable_id', $graduationPost->id)
            ->where('field_name', 'like', 'article_gallery:%')
            ->delete();

        foreach (['graduation-hall', 'graduation-guest-arrival', 'graduation-portrait-01', 'graduation-portrait-02', 'graduation-portrait-03'] as $index => $mediaKey) {
            MediaUsage::query()->create([
                'media_id' => $media[$mediaKey]->id,
                'usable_type' => Post::class,
                'usable_id' => $graduationPost->id,
                'field_name' => 'article_gallery:'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
            ]);
        }

        $posts = [
            [
                'young-learners-shine',
                $category->id,
                'Growing Together Through Tree Planting',
                'Pupils and staff share a practical moment of care for the school environment.',
                'The school community came together for a hands-on tree-planting activity, giving pupils an opportunity to learn through participation and shared responsibility.',
                $media['tree-planting']->id,
                true,
                2,
            ],
            [
                'reading-together-in-the-library',
                $learning->id,
                'Celebrating Effort And Progress',
                'Recognition moments encourage pupils to value steady effort and personal progress.',
                'A school recognition ceremony celebrates a pupil\'s work and marks an encouraging step in the learning journey.',
                $media['pupil-recognition']->id,
                false,
                7,
            ],
            [
                'teamwork-takes-the-field',
                $sport->id,
                'Culture In Motion',
                'Pupils bring colour, rhythm and confidence to a school celebration.',
                'A lively cultural performance gives pupils space to practise teamwork, expression and confidence before the school community.',
                $media['cultural-performance']->id,
                false,
                12,
            ],
        ];

        foreach ($posts as [$slug, $categoryId, $title, $excerpt, $body, $featuredMediaId, $featured, $daysAgo]) {
            $post = Post::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'post_category_id' => $categoryId,
                    'author_id' => $admin->id,
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'body' => $body,
                    'featured_media_id' => $featuredMediaId,
                    'status' => 'published',
                    'is_featured' => $featured,
                    'published_at' => now()->subDays($daysAgo),
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]
            );

            $post->tags()->syncWithoutDetaching([$tag->id]);
        }

        Announcement::query()->updateOrCreate(
            ['title' => 'Admissions Notice'],
            [
                'message' => 'Use the admissions page to send an enquiry and receive guidance from the school team.',
                'severity' => 'info',
                'status' => 'draft',
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonth(),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );
    }

    private function seedDownloads(User $admin): Download
    {
        $category = DownloadCategory::query()->updateOrCreate(['slug' => 'admissions'], ['name' => 'Admissions', 'description' => 'Approved admissions documents.', 'is_active' => true]);
        $calendar = DownloadCategory::query()->updateOrCreate(['slug' => 'calendar'], ['name' => 'Calendar', 'description' => 'Published school calendar documents.', 'is_active' => true]);
        $policies = DownloadCategory::query()->updateOrCreate(['slug' => 'policies'], ['name' => 'Policies', 'description' => 'Approved school policies and notices.', 'is_active' => true]);
        $media = Media::query()->where('stored_name', 'admissions-document-awaiting-file.pdf')->firstOrFail();

        $downloads = [
            ['admissions-information-pack', $category->id, 'Admissions Information Pack', 'Admissions guidance document.', 0],
            ['academic-calendar', $calendar->id, 'Academic Calendar', 'School calendar document.', 4],
            ['safeguarding-statement', $policies->id, 'Safeguarding Statement', 'School safeguarding document.', 8],
        ];

        $first = null;

        foreach ($downloads as [$slug, $categoryId, $title, $description, $daysAgo]) {
            $download = Download::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'download_category_id' => $categoryId,
                    'title' => $title,
                    'description' => $description,
                    'media_id' => $media->id,
                    'version' => null,
                    'publication_date' => now()->subDays($daysAgo)->toDateString(),
                    'status' => 'draft',
                    'published_at' => null,
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]
            );

            $first ??= $download;
        }

        return $first;
    }

    /**
     * @param  array<string, Media>  $media
     */
    private function seedEvents(User $admin, array $media, Download $download): void
    {
        $category = EventCategory::query()->updateOrCreate(['slug' => 'school-calendar'], ['name' => 'School Calendar', 'description' => 'Dates published by the school.', 'is_active' => true]);
        $faith = EventCategory::query()->updateOrCreate(['slug' => 'faith'], ['name' => 'Faith', 'description' => 'Faith and community events.', 'is_active' => true]);
        $sport = EventCategory::query()->updateOrCreate(['slug' => 'sport'], ['name' => 'Sport', 'description' => 'Sport and participation events.', 'is_active' => true]);

        $events = [
            ['parent-orientation', $category->id, 'Parent Orientation', 'Orientation information for families.', $media['ceremony']->id, 2, null],
            ['community-mass', $faith->id, 'Community Mass', 'A gathering for prayer and community.', $media['community']->id, 4, null],
            ['inter-house-sports-day', $sport->id, 'Inter-house Sports Day', 'A school sport and participation event.', $media['football']->id, 6, null],
        ];

        foreach ($events as [$slug, $categoryId, $title, $summary, $featuredMediaId, $weeksAhead, $venue]) {
            Event::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'event_category_id' => $categoryId,
                    'title' => $title,
                    'summary' => $summary,
                    'body' => 'Full event details will be published after the school calendar is confirmed.',
                    'featured_media_id' => $featuredMediaId,
                    'starts_at' => now()->addWeeks($weeksAhead)->setTime(9, 0),
                    'ends_at' => now()->addWeeks($weeksAhead)->setTime(11, 0),
                    'timezone' => config('app.timezone'),
                    'venue_name' => $venue,
                    'programme_download_id' => $download->id,
                    'event_state' => 'scheduled',
                    'publication_status' => 'published',
                    'published_at' => now()->subDay(),
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]
            );
        }
    }

    /**
     * @param  array<string, Media>  $media
     */
    private function seedSchoolInformation(User $admin, array $media): void
    {
        $department = Department::query()->updateOrCreate(
            ['slug' => 'primary-school'],
            ['name' => 'Primary School', 'description' => 'Primary learning and pastoral support.', 'is_active' => true]
        );

        Programme::query()->updateOrCreate(
            ['slug' => 'primary-learning-programme'],
            [
                'department_id' => $department->id,
                'name' => 'Primary Learning Programme',
                'programme_type' => 'primary',
                'level' => 'Primary',
                'summary' => 'A structured programme supporting knowledge, skill, character and confidence.',
                'body' => 'Programme information is organised to help families understand the learning journey and the support available to pupils.',
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
                'name' => 'School Administration',
                'job_title' => 'Administration',
                'staff_type' => 'administration',
                'approved_biography' => 'The administration team supports school operations and communication with families.',
                'photo_media_id' => $media['staff']->id,
                'sort_order' => 1,
                'is_leadership' => true,
                'is_public' => true,
                'is_active' => true,
            ]
        );

        StaffMember::query()->updateOrCreate(
            ['slug' => 'local-seed-teaching-community'],
            [
                'department_id' => $department->id,
                'name' => 'Teaching Community',
                'job_title' => 'Teaching Team',
                'staff_type' => 'teaching',
                'approved_biography' => 'The teaching community guides learning, participation and pupil progress.',
                'photo_media_id' => $media['classroom']->id,
                'sort_order' => 2,
                'is_leadership' => false,
                'is_public' => true,
                'is_active' => true,
            ]
        );

        StaffMember::query()->updateOrCreate(
            ['slug' => 'local-seed-pastoral-support'],
            [
                'department_id' => $department->id,
                'name' => 'Pastoral Support',
                'job_title' => 'Pastoral Support',
                'staff_type' => 'pastoral',
                'approved_biography' => 'Pastoral support helps sustain wellbeing, dignity and belonging across school life.',
                'photo_media_id' => $media['community']->id,
                'sort_order' => 3,
                'is_leadership' => false,
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
        $graduationGallery = Gallery::query()->updateOrCreate(
            ['slug' => 'graduation-2026'],
            [
                'gallery_category_id' => $category->id,
                'title' => 'Graduation 2026',
                'description' => 'Selected moments from the graduation celebration held at St. Charles Borromeo Hall on 15 August 2026.',
                'cover_media_id' => $media['graduation-welcome']->id,
                'event_date' => '2026-08-15',
                'status' => 'published',
                'published_at' => now()->subHours(2),
                'sort_order' => 0,
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ],
        );

        foreach (['graduation-welcome', 'graduation-hall', 'graduation-guest-arrival', 'graduation-portrait-01', 'graduation-portrait-02', 'graduation-portrait-03'] as $index => $key) {
            $item = GalleryItem::query()->updateOrCreate(
                ['gallery_id' => $graduationGallery->id, 'media_id' => $media[$key]->id],
                ['caption' => $media[$key]->caption, 'sort_order' => $index + 1, 'is_featured' => $index === 0],
            );

            MediaUsage::query()->updateOrCreate([
                'media_id' => $media[$key]->id,
                'usable_type' => GalleryItem::class,
                'usable_id' => $item->id,
                'field_name' => 'media_id',
            ]);
        }

        $gallery = Gallery::query()->updateOrCreate(
            ['slug' => 'school-life-gallery'],
            [
                'gallery_category_id' => $category->id,
                'title' => 'School Life Gallery',
                'description' => 'Learning, community, achievement and celebration at St. Charles Borromeo.',
                'cover_media_id' => $media['campus-aerial']->id,
                'event_date' => null,
                'status' => 'published',
                'published_at' => now()->subDay(),
                'sort_order' => 1,
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );

        $obsoleteItems = GalleryItem::query()
            ->where('gallery_id', $gallery->id)
            ->whereIn('media_id', [$media['playground']->id, $media['assembly']->id])
            ->pluck('id');

        if ($obsoleteItems->isNotEmpty()) {
            MediaUsage::query()
                ->where('usable_type', GalleryItem::class)
                ->whereIn('usable_id', $obsoleteItems)
                ->delete();
            GalleryItem::query()->whereKey($obsoleteItems)->delete();
        }

        $galleryMediaKeys = ['campus-aerial', 'tree-planting', 'pupil-recognition', 'cultural-performance', 'football', 'library'];

        foreach ($galleryMediaKeys as $index => $key) {
            $item = GalleryItem::query()->updateOrCreate(
                ['gallery_id' => $gallery->id, 'media_id' => $media[$key]->id],
                ['caption' => $media[$key]->alt_text, 'sort_order' => $index + 1, 'is_featured' => $index === 0]
            );

            MediaUsage::query()->updateOrCreate([
                'media_id' => $media[$key]->id,
                'usable_type' => GalleryItem::class,
                'usable_id' => $item->id,
                'field_name' => 'media_id',
            ]);
        }
    }

    private function seedFaqs(User $admin): void
    {
        $category = FaqCategory::query()->updateOrCreate(['slug' => 'general'], ['name' => 'General', 'is_active' => true]);

        Faq::query()->updateOrCreate(
            ['question' => 'Where should official admissions information be confirmed?'],
            [
                'faq_category_id' => $category->id,
                'answer' => 'Use the admissions enquiry form to request current guidance directly from the school team.',
                'sort_order' => 1,
                'status' => 'published',
                'published_at' => now()->subDay(),
                'verified_at' => now()->subDay(),
                'verified_by' => $admin->id,
                'verification_notes' => 'Website process confirmed.',
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );

        $faqs = [
            ['How do I begin an admission enquiry?', 'Families can send an enquiry through the Admissions page or contact the school office. An authorised staff member will confirm availability, required documents and the next step.', 2],
            ['How can I arrange a school visit?', 'Send an admission enquiry with your preferred contact details. The school office will respond with available arrangements.', 3],
            ['Where can I find term dates and forms?', 'Published calendars, admissions documents, policies and school resources are available on the Downloads page.', 4],
            ['How are school photographs managed?', 'Public photographs pass a safeguarding review. Images requiring consent remain private until authorised staff confirm the relevant consent record.', 5],
        ];

        foreach ($faqs as [$question, $answer, $order]) {
            Faq::query()->updateOrCreate(
                ['question' => $question],
                [
                    'faq_category_id' => $category->id,
                    'answer' => $answer,
                    'sort_order' => $order,
                    'status' => 'published',
                    'published_at' => now()->subDay(),
                    'verified_at' => now()->subDay(),
                    'verified_by' => $admin->id,
                    'verification_notes' => 'Website process confirmed.',
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]
            );
        }
    }

    private function seedDemoWorkflow(User $admin): void
    {
        $admission = AdmissionEnquiry::query()->updateOrCreate(
            ['reference_code' => 'ADM-LOCAL-0001'],
            [
                'guardian_name' => 'Admissions Enquiry',
                'email' => 'guardian@example.test',
                'telephone' => '+27 00 000 0001',
                'intended_level' => 'Primary',
                'intended_year' => (int) now()->addYear()->format('Y'),
                'preferred_contact_method' => 'email',
                'message' => 'Please share the next admissions steps.',
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
                'full_name' => 'Website Visitor',
                'email' => 'contact@example.test',
                'telephone' => '+27 00 000 0002',
                'subject' => 'General enquiry',
                'message' => 'Please share more information about the school.',
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
