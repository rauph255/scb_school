<?php

namespace Tests\Feature;

use App\Mail\PublicSubmissionReceived;
use App\Models\AdmissionEnquiry;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\Download;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Media;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\AdminAlertFeed;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    public function test_runtime_meets_the_mandatory_platform_and_serialization_contract(): void
    {
        $this->assertGreaterThanOrEqual(80200, PHP_VERSION_ID);
        $this->assertTrue(version_compare($this->app->version(), '12.0.0', '>='));
        $this->assertTrue(version_compare($this->app->version(), '13.0.0', '<'));

        foreach (['curl', 'dom', 'fileinfo', 'gd', 'pdo_mysql', 'xmlwriter', 'zip'] as $extension) {
            $this->assertTrue(extension_loaded($extension), "Required PHP extension [{$extension}] is not loaded.");
        }

        $this->assertSame('json', config('session.serialization'));
        $this->assertFalse(config('cache.serializable_classes'));
    }

    public function test_upload_runtime_limits_match_the_visible_media_workflows(): void
    {
        $uploadConfiguration = parse_ini_file(public_path('.user.ini'));
        $composer = json_decode(File::get(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('21M', $uploadConfiguration['upload_max_filesize'] ?? null);
        $this->assertSame('25M', $uploadConfiguration['post_max_size'] ?? null);
        $this->assertStringContainsString(
            '-d upload_max_filesize=21M -d post_max_size=25M artisan serve',
            implode(' ', $composer['scripts']['dev']),
        );

        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.media'))
            ->assertOk()
            ->assertSee('data-upload-max-mb="10"', false);

        $this->actingAs($admin)
            ->get(route('admin.downloads'))
            ->assertOk()
            ->assertSee('data-upload-max-mb="20"', false);

        $oversizedResponse = $this->actingAs($admin)
            ->withServerVariables(['CONTENT_LENGTH' => 1024 * 1024 * 1024])
            ->post(route('admin.media.store'));

        $this->assertSame(413, $oversizedResponse->getStatusCode());
        $this->assertStringContainsString('That file is too large', $oversizedResponse->getContent());
        $this->assertStringContainsString('image no larger than 10 MB', $oversizedResponse->getContent());

        $javascript = File::get(public_path('assets/js/scb-experience.js'));
        $this->assertStringContainsString('input[type="file"][data-upload-max-mb]', $javascript);
        $this->assertStringContainsString('input.setCustomValidity(message)', $javascript);
        $this->assertStringContainsString("qsa('[data-media-upload-file]')", $javascript);
        $this->assertStringContainsString("alternativeText.required = input.files?.[0]?.type.startsWith('image/')", $javascript);
    }

    public function test_home_carousel_uses_approved_mysql_media_and_clean_partner_credit(): void
    {
        $this->prepareMySqlSchema(seed: true);

        foreach ([
            'campus-aerial.webp',
            'tree-planting-community.webp',
            'pupil-recognition.webp',
            'cultural-performance.webp',
        ] as $storedName) {
            $this->assertDatabaseHas('media', [
                'stored_name' => $storedName,
                'visibility' => 'public',
                'publication_restricted' => false,
            ]);
        }

        $this->assertDatabaseMissing('media', ['stored_name' => '104.png']);

        $mediaUrls = collect([
            'campus-aerial.webp',
            'graduation-ceremonial-welcome-2026.webp',
            'graduation-hall-2026.webp',
            'tree-planting-community.webp',
        ])->map(fn (string $storedName): string => Media::query()
            ->where('stored_name', $storedName)
            ->firstOrFail()
            ->responsiveUrl(1600));

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('data-hero-carousel', false)
            ->assertSee('href="https://www.falconode.net"', false)
            ->assertSee('Powered by Falconode (T) Ltd', false)
            ->assertDontSee('falconode-credit.png', false)
            ->assertSee('class="btn btn-primary btn-sm nav-apply"', false)
            ->assertDontSee('Seeded placeholder', false)
            ->assertDontSee('Unverified placeholder', false);

        foreach ($mediaUrls as $url) {
            $response->assertSee($url, false);
        }
    }

    public function test_graduation_story_media_alert_and_minimal_library_are_production_ready(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $post = Post::query()
            ->with(['featuredMedia.variants', 'articleMediaUsages.media.variants'])
            ->where('slug', 'st-charles-borromeo-2026-graduation')
            ->firstOrFail();

        $this->assertSame('published', $post->status);
        $this->assertSame('graduation-ceremonial-welcome-2026.webp', $post->featuredMedia->stored_name);
        $this->assertCount(5, $post->articleMediaUsages);
        $this->assertStringStartsWith('/media/', $post->featuredMedia->publicUrl());
        $this->assertStringStartsWith('/media/', $post->featuredMedia->responsiveUrl(960));

        $graduationMedia = Media::query()
            ->with('variants')
            ->where('directory', 'seed/school/2026/graduation')
            ->get();

        $this->assertCount(6, $graduationMedia);

        foreach ($graduationMedia as $media) {
            $this->assertSame('image/webp', $media->mime_type);
            $this->assertSame('webp', $media->extension);
            $this->assertSame('public', $media->visibility);
            $this->assertFalse($media->publication_restricted);
            $this->assertCount(3, $media->variants);
            Storage::disk('local')->assertExists($media->storagePath());

            foreach ($media->variants as $variant) {
                Storage::disk($variant->disk)->assertExists($variant->path);
            }
        }

        $this->get('/')
            ->assertOk()
            ->assertSee('data-recent-news-alert', false)
            ->assertSee('St. Charles Borromeo Celebrates the 2026 Graduation', false)
            ->assertSee('Published', false);

        $this->get(route('news.show', $post))
            ->assertOk()
            ->assertSee('15 August 2026', false)
            ->assertSee('Solomon Itunda', false)
            ->assertSee('Mbeya District Commissioner', false)
            ->assertSee('article-gallery__grid', false)
            ->assertSee('Graduation in pictures', false)
            ->assertSee('"@context":"https://schema.org"', false)
            ->assertDontSee('__contextArgs', false)
            ->assertSee('srcset="/media/', false);

        $this->get($post->featuredMedia->responsiveUrl(960))
            ->assertOk()
            ->assertHeader('content-type', 'image/webp')
            ->assertHeaderMissing('set-cookie')
            ->assertHeaderMissing('x-powered-by');

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.media', ['q' => 'graduation-hall-2026']))
            ->assertOk()
            ->assertSee('media-library-toolbar', false)
            ->assertSee('graduation-hall-2026.webp', false)
            ->assertSee('Edit details', false)
            ->assertSee('Optional details and consent', false)
            ->assertDontSee('graduation-graduate-portrait-01-2026.webp', false);
    }

    public function test_login_forms_do_not_expose_local_credentials(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $aerialMedia = Media::query()
            ->with('variants')
            ->where('stored_name', 'campus-aerial.webp')
            ->firstOrFail();

        $this->assertCount(3, $aerialMedia->variants);

        $this->get('/admin/login')
            ->assertOk()
            ->assertDontSee('local.admin@example.test', false)
            ->assertDontSee('ChangeMeLocalOnly!', false)
            ->assertSee('autocomplete="username"', false)
            ->assertSee('autocomplete="current-password"', false)
            ->assertSee($aerialMedia->responsiveUrl(1600), false)
            ->assertSee('sizes="(max-width: 800px) 100vw, 55vw"', false)
            ->assertSee('class="site-credit site-credit--light login-credit site-credit--login"', false)
            ->assertSee('data-admin-login-credit', false)
            ->assertSee('Powered by Falconode (T) Ltd', false);

        $this->get('/staff-portal/login')
            ->assertOk()
            ->assertDontSee('local.teacher@example.test', false)
            ->assertDontSee('ChangeMeLocalOnly!', false)
            ->assertSee('autocomplete="username"', false)
            ->assertSee('autocomplete="current-password"', false)
            ->assertSee($aerialMedia->responsiveUrl(1600), false)
            ->assertSee('Powered by Falconode (T) Ltd', false);
    }

    public function test_public_gallery_is_responsive_filterable_and_keyboard_accessible(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $response = $this->get(route('gallery'));

        $response
            ->assertOk()
            ->assertSee('data-gallery-filter="all"', false)
            ->assertSee('data-gallery-filter="school-life-gallery"', false)
            ->assertSee('data-gallery-filter="graduation-2026"', false)
            ->assertSee('data-gallery-item', false)
            ->assertSee('data-gallery-open', false)
            ->assertSee('srcset="/media/', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('data-gallery-modal-caption', false)
            ->assertSee('Open photograph:', false)
            ->assertDontSee('role="button" tabindex="0"', false);

        $css = File::get(public_path('assets/css/scb-experience.css'));
        $javascript = File::get(public_path('assets/scb/js/app.js'));

        $this->assertStringContainsString('grid-template-columns: repeat(3, minmax(0, 1fr))', $css);
        $this->assertStringContainsString('@media (max-width: 900px)', $css);
        $this->assertStringContainsString('@media (max-width: 640px)', $css);
        $this->assertStringContainsString("qsa('[data-gallery-open]')", $javascript);
        $this->assertStringContainsString('lastGalleryTrigger?.focus()', $javascript);
    }

    public function test_seeded_private_images_have_working_media_library_and_gallery_previews(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();
        $gallery = Gallery::query()->where('slug', 'school-life-gallery')->firstOrFail();
        $media = Media::query()
            ->where('disk', 'local')
            ->where('directory', 'like', 'seed/%')
            ->where('mime_type', 'like', 'image/%')
            ->firstOrFail();

        Storage::disk('local')->assertExists($media->storagePath());

        $preview = $this->actingAs($admin)
            ->get(route('admin.media.preview', $media))
            ->assertOk()
            ->assertHeader('content-type', $media->mime_type)
            ->assertHeader('x-content-type-options', 'nosniff');
        $this->assertStringContainsString('private', (string) $preview->headers->get('cache-control'));
        $this->assertStringContainsString('no-store', (string) $preview->headers->get('cache-control'));

        $this->actingAs($admin)
            ->get(route('admin.gallery.editor', ['gallery' => $gallery->slug]))
            ->assertOk()
            ->assertSee('src="'.$gallery->items()->firstOrFail()->media->adminPreviewUrl().'"', false);
    }

    public function test_missing_gallery_files_are_rejected_and_omitted_from_public_output(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();
        $gallery = Gallery::query()->where('slug', 'school-life-gallery')->firstOrFail();
        $item = GalleryItem::query()
            ->where('gallery_id', $gallery->id)
            ->with('media')
            ->firstOrFail();
        $item->forceFill(['caption' => 'Unavailable gallery file marker'])->save();
        $item->media->forceFill([
            'disk' => 'local',
            'directory' => 'media/missing-gallery-test',
            'stored_name' => 'missing.webp',
        ])->save();

        $this->get(route('gallery'))
            ->assertOk()
            ->assertDontSee('Unavailable gallery file marker', false);

        $this->actingAs($admin)
            ->get(route('admin.media.preview', $item->media))
            ->assertNotFound();

        $item->media->forceFill([
            'visibility' => 'private',
            'publication_restricted' => true,
            'restriction_reason' => 'Missing file regression test.',
        ])->save();

        $this->actingAs($admin)
            ->from(route('admin.media'))
            ->patch(route('admin.media.publication', $item->media), ['action' => 'approve'])
            ->assertRedirect(route('admin.media'))
            ->assertSessionHasErrors('action');

        $gallery->forceFill(['status' => 'draft', 'published_at' => null])->save();

        $this->actingAs($admin)
            ->from(route('admin.gallery.editor', ['gallery' => $gallery->slug]))
            ->patch(route('admin.gallery.update', $gallery), [
                'title' => $gallery->title,
                'description' => $gallery->description,
                'gallery_category_id' => $gallery->gallery_category_id,
                'event_date' => $gallery->event_date?->toDateString(),
                'status' => 'published',
            ])
            ->assertRedirect(route('admin.gallery.editor', ['gallery' => $gallery->slug]))
            ->assertSessionHasErrors('status');

        $this->assertSame('draft', $gallery->fresh()->status);
    }

    public function test_password_reset_flow_uses_a_verified_token_and_audits_the_change(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();
        $token = Password::createToken($admin);

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Reset your password', false);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => mb_strtoupper($admin->email),
            'password' => 'ReplacementPassw0rd!',
            'password_confirmation' => 'ReplacementPassw0rd!',
        ])->assertRedirect(route('admin.login'));

        $this->assertTrue(Hash::check('ReplacementPassw0rd!', $admin->fresh()->password));
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'account.password_reset',
            'subject_type' => User::class,
            'subject_id' => $admin->id,
        ]);
    }

    public function test_production_artifacts_do_not_publish_school_media_or_local_secrets(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $this->assertDirectoryDoesNotExist(public_path('assets/images/school'));
        $this->assertDirectoryDoesNotExist(public_path('assets/prototype'));
        $this->assertFileExists(public_path('assets/scb/css/app.css'));
        $this->assertFileExists(public_path('assets/scb/js/app.js'));
        $this->assertFalse(file_exists(public_path('storage')) || is_link(public_path('storage')));
        $this->assertSame(
            '2ba8c080a722aee70747ffce99097ae59596d351649bc8b75e8cac973e422c5b',
            hash_file('sha256', public_path('assets/images/brand/scb-logo-original.jpg')),
        );
        $this->assertStringNotContainsString(
            'name="DB_PASSWORD"',
            (string) file_get_contents(base_path('phpunit.xml')),
        );
        $this->assertFileExists(resource_path('admin-experience/dashboard.html'));
        $this->assertStringNotContainsString(
            'reference/scb_uiux_prototype',
            (string) file_get_contents(app_path('Http/Controllers/AdminExperienceController.php')),
        );
    }

    public function test_representative_seeder_is_disabled_in_production(): void
    {
        $this->prepareMySqlSchema(seed: true);
        $previousEnvironment = $this->app->environment();

        try {
            $this->app->detectEnvironment(fn (): string => 'production');

            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('development seeder is disabled');

            (new DatabaseSeeder)->run();
        } finally {
            $this->app->detectEnvironment(fn (): string => $previousEnvironment);
        }
    }

    public function test_admin_alert_is_permission_aware_and_clears_after_record_is_opened(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();
        $teacher = User::query()->where('email', 'local.teacher@example.test')->firstOrFail();
        $message = ContactMessage::query()->where('reference_code', 'CON-LOCAL-0001')->firstOrFail();

        $this->assertNull($message->first_viewed_at);
        $this->assertSame(0, app(AdminAlertFeed::class)->for($teacher)['count']);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('data-admin-alert="message-'.$message->id.'"', false);

        $this->actingAs($admin)
            ->get(route('admin.contact-messages.show', $message))
            ->assertOk()
            ->assertDontSee('data-admin-alert="message-'.$message->id.'"', false);

        $message->refresh();
        $this->assertNotNull($message->first_viewed_at);
        $this->assertSame($admin->id, $message->viewed_by);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'contact_message.first_viewed',
            'subject_type' => ContactMessage::class,
            'subject_id' => $message->id,
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee('data-admin-alert="message-'.$message->id.'"', false);
    }

    public function test_admin_can_securely_edit_only_their_own_account(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.profile'))
            ->assertOk()
            ->assertSee('My profile', false)
            ->assertSee('action="'.route('admin.profile.update').'"', false);

        $this->actingAs($admin)
            ->from(route('admin.profile'))
            ->patch(route('admin.profile.update'), [
                'name' => 'Rejected Account Name',
                'email' => $admin->email,
                'current_password' => 'incorrect-password',
            ])
            ->assertRedirect(route('admin.profile'))
            ->assertSessionHasErrors('current_password', null, 'profile');

        $this->assertSame('System Administrator', $admin->fresh()->name);

        $this->actingAs($admin)
            ->patch(route('admin.profile.update'), [
                'name' => 'School Account Administrator',
                'email' => 'account.admin@example.test',
                'current_password' => 'ChangeMeLocalOnly!',
                'password' => 'BetterPassw0rd!',
                'password_confirmation' => 'BetterPassw0rd!',
            ])
            ->assertRedirect(route('admin.profile'))
            ->assertSessionHas('status', 'Your account details were updated.');

        $admin->refresh();
        $this->assertSame('School Account Administrator', $admin->name);
        $this->assertSame('account.admin@example.test', $admin->email);
        $this->assertTrue(Hash::check('BetterPassw0rd!', $admin->password));
        $this->assertNull($admin->email_verified_at);

        $audit = AuditLog::query()
            ->where('action', 'account.profile_and_password_updated')
            ->where('subject_id', $admin->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertTrue($audit->new_values['password_changed']);
        $this->assertArrayNotHasKey('password', $audit->new_values);
    }

    public function test_staff_can_edit_their_account_without_admin_access(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $teacher = User::query()->where('email', 'local.teacher@example.test')->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSee('id="staff-profile-form"', false)
            ->assertSee('action="'.route('staff.profile.update').'"', false);

        $this->actingAs($teacher)
            ->patch(route('staff.profile.update'), [
                'name' => 'Teaching Team Contributor',
                'email' => $teacher->email,
                'current_password' => 'ChangeMeLocalOnly!',
            ])
            ->assertRedirect(route('staff.dashboard').'#staff-profile-form')
            ->assertSessionHas('status', 'Your account details were updated.');

        $this->assertSame('Teaching Team Contributor', $teacher->fresh()->name);

        $this->actingAs($teacher)
            ->get(route('admin.profile'))
            ->assertForbidden();
    }

    public function test_new_public_submissions_begin_unread(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $this->from('/contact')->post('/contact/messages', [
            'full_name' => 'New Website Visitor',
            'telephone' => '+255 700 100 200',
            'email' => 'new.visitor@example.test',
            'subject' => 'General enquiry',
            'message' => 'Please send more information.',
            'privacy_notice' => '1',
        ])->assertRedirect('/contact');

        $message = ContactMessage::query()->where('email', 'new.visitor@example.test')->firstOrFail();
        $this->assertNull($message->first_viewed_at);
        $this->assertNull($message->viewed_by);

        $enquiry = AdmissionEnquiry::query()->where('reference_code', 'ADM-LOCAL-0001')->firstOrFail();
        $this->assertNull($enquiry->first_viewed_at);
    }

    public function test_public_forms_use_honeypots_complete_admissions_fields_and_queued_notifications(): void
    {
        $this->prepareMySqlSchema(seed: true);
        Mail::fake();

        SiteSetting::query()
            ->where('group_name', 'contact')
            ->where('setting_key', 'primary_email')
            ->update(['value_json' => ['value' => 'school.office@example.test']]);

        $this->get(route('admissions'))
            ->assertOk()
            ->assertSee('name="website"', false)
            ->assertSee('name="intended_term"', false)
            ->assertSee('name="intended_year"', false)
            ->assertSee('name="preferred_contact_method"', false);

        $this->from(route('admissions'))->post(route('admissions.enquiries.store'), [
            'website' => 'https://spam.example',
            'guardian_name' => 'Blocked Bot',
            'telephone' => '+255 700 000 001',
            'email' => 'bot@example.test',
            'privacy_notice' => '1',
        ])->assertRedirect(route('admissions'))->assertSessionHasErrors('website');

        $this->assertDatabaseMissing('admission_enquiries', ['email' => 'bot@example.test']);

        $this->from(route('admissions'))->post(route('admissions.enquiries.store'), [
            'guardian_name' => 'Prospective Guardian',
            'telephone' => '+255 700 000 002',
            'email' => 'PARENT@EXAMPLE.TEST',
            'intended_level' => 'Pre-primary',
            'intended_term' => 'First term',
            'intended_year' => now()->year,
            'preferred_contact_method' => 'email',
            'privacy_notice' => '1',
        ])->assertRedirect(route('admissions'));

        $enquiry = AdmissionEnquiry::query()->where('email', 'parent@example.test')->firstOrFail();
        $this->assertSame('First term', $enquiry->intended_term);
        $this->assertSame(now()->year, $enquiry->intended_year);
        $this->assertSame('email', $enquiry->preferred_contact_method);

        Mail::assertQueued(PublicSubmissionReceived::class, fn (PublicSubmissionReceived $mail): bool => $mail->hasTo('school.office@example.test')
            && $mail->referenceCode === $enquiry->reference_code
        );
    }

    public function test_public_media_is_mysql_authorized_and_kept_out_of_public_storage(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $media = Media::query()->where('stored_name', 'campus-aerial.webp')->firstOrFail();

        $this->assertSame('local', $media->disk);
        Storage::disk('local')->assertExists($media->storagePath());
        Storage::disk('public')->assertMissing($media->storagePath());

        $response = $this->get(route('media.show', $media))->assertOk();
        $this->assertStringContainsString('immutable', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('public', (string) $response->headers->get('Cache-Control'));

        $media->forceFill(['publication_restricted' => true])->save();

        $this->get(route('media.show', $media))->assertNotFound();
    }

    public function test_logout_requires_a_csrf_protected_post_request(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.logout'))
            ->assertMethodNotAllowed();

        $this->actingAs($admin)
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_document_without_a_stored_pdf_cannot_be_published_or_linked(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();
        $download = Download::query()->where('slug', 'admissions-information-pack')->firstOrFail();

        $this->assertSame('draft', $download->status);
        $this->assertFalse($download->media->storedFileExists());

        $this->actingAs($admin)
            ->from(route('admin.downloads'))
            ->patch(route('admin.downloads.status', $download), ['status' => 'published'])
            ->assertRedirect(route('admin.downloads'))
            ->assertSessionHasErrors('status');

        $this->assertSame('draft', $download->fresh()->status);

        $this->get(route('downloads'))
            ->assertOk()
            ->assertDontSee('Admissions Information Pack', false)
            ->assertSee('No published downloads match your search.', false);
    }

    public function test_public_and_private_responses_include_security_headers(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.profile'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }
}
