<?php

namespace Tests\Feature;

use App\Jobs\SendEnquiryReply;
use App\Mail\EnquiryReplyMail;
use App\Models\ContactMessage;
use App\Models\EmailReply;
use App\Models\Event;
use App\Models\Faq;
use App\Models\Media;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContentOperationsTest extends TestCase
{
    public function test_faq_answers_require_authorised_verification_before_publication(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $faq = Faq::query()->create([
            'question' => 'What evidence is needed for a transfer?',
            'answer' => 'Please contact the school office so the current requirements can be confirmed.',
            'sort_order' => 500,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->get(route('faq'))
            ->assertOk()
            ->assertDontSee($faq->question);

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.faqs'))
            ->patch(route('admin.faqs.verify', $faq), [
                'action' => 'verify',
                'verification_notes' => 'Checked against the current admissions process.',
            ])
            ->assertRedirect(route('admin.faqs'));

        $this->assertDatabaseHas('faqs', [
            'id' => $faq->id,
            'status' => 'published',
            'verified_by' => $admin->id,
        ]);

        $this->get(route('faq'))
            ->assertOk()
            ->assertSee($faq->question)
            ->assertSee('FAQPage', false);
    }

    public function test_staff_can_queue_and_deliver_a_tracked_email_reply(): void
    {
        $this->prepareMySqlSchema(seed: true);
        Mail::fake();

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();
        $message = ContactMessage::query()->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.contact-messages.show', $message))
            ->post(route('admin.contact-messages.reply', $message), [
                'subject' => 'Reply from the school office',
                'body' => 'Thank you for contacting us. The school office will assist with your request.',
            ])
            ->assertRedirect(route('admin.contact-messages.show', $message))
            ->assertSessionHas('status', 'Email reply queued for delivery.');

        $reply = EmailReply::query()->where('replyable_id', $message->id)->latest('id')->firstOrFail();

        $this->assertSame('queued', $reply->status);
        $this->assertSame(mb_strtolower($message->email), $reply->recipient_email);

        (new SendEnquiryReply($reply->id))->handle();

        Mail::assertSent(EnquiryReplyMail::class, fn (EnquiryReplyMail $mail): bool => $mail->hasTo($message->email));
        $this->assertSame('sent', $reply->fresh()->status);
        $this->assertSame('responded', $message->fresh()->status);
    }

    public function test_uploaded_images_are_optimised_to_webp_with_responsive_variants(): void
    {
        $this->prepareMySqlSchema(seed: true);
        Storage::fake('local');

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.media'))
            ->post(route('admin.media.store'), [
                'file' => UploadedFile::fake()->image('large-campus.jpg', 2000, 1200),
                'alt_text' => 'A wide view of the school campus.',
                'consent_required' => '0',
                'consent_confirmed' => '0',
            ])
            ->assertRedirect(route('admin.media'));

        $media = Media::query()->where('original_name', 'large-campus.jpg')->firstOrFail();

        $this->assertSame('image/webp', $media->mime_type);
        $this->assertSame('webp', $media->extension);
        $this->assertSame(2000, $media->width);
        $this->assertSame(1200, $media->height);
        $this->assertCount(3, $media->variants);
        Storage::disk('local')->assertExists($media->storagePath());

        foreach ($media->variants as $variant) {
            $this->assertSame('image/webp', $variant->mime_type);
            Storage::disk('local')->assertExists($variant->path);
        }
    }

    public function test_news_and_event_editors_attach_approved_media_library_images(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();
        $media = Media::query()
            ->publiclyVisible()
            ->where('mime_type', 'like', 'image/%')
            ->where('stored_name', 'tree-planting-community.webp')
            ->firstOrFail();
        $post = Post::query()->where('slug', 'reading-together-in-the-library')->firstOrFail();
        $event = Event::query()->where('slug', 'parent-orientation')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.news.editor', ['post' => $post->slug]))
            ->assertOk()
            ->assertSee('name="featured_media_id"', false)
            ->assertSee('name="featured_image"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('value="'.$media->id.'"', false);

        $this->actingAs($admin)
            ->patch(route('admin.news.update', $post), [
                'title' => $post->title,
                'excerpt' => $post->excerpt,
                'body' => $post->body,
                'status' => 'published',
                'published_at' => now()->subMinute()->format('Y-m-d\TH:i'),
                'post_category_id' => $post->post_category_id,
                'featured_media_id' => $media->id,
            ])
            ->assertRedirect(route('admin.news'))
            ->assertSessionHas('status', 'Story saved.');

        $this->assertSame($media->id, $post->fresh()->featured_media_id);

        $this->actingAs($admin)
            ->get(route('admin.events.editor', ['event' => $event->slug]))
            ->assertOk()
            ->assertSee('name="featured_media_id"', false)
            ->assertSee('name="featured_image"', false)
            ->assertSee('value="'.$media->id.'"', false);

        $this->actingAs($admin)
            ->patch(route('admin.events.update', $event), [
                'title' => $event->title,
                'summary' => $event->summary,
                'body' => $event->body,
                'starts_at' => $event->starts_at->format('Y-m-d\TH:i'),
                'ends_at' => $event->ends_at?->format('Y-m-d\TH:i'),
                'venue_name' => $event->venue_name,
                'event_category_id' => $event->event_category_id,
                'publication_status' => 'published',
                'featured_media_id' => $media->id,
            ])
            ->assertRedirect(route('admin.events'))
            ->assertSessionHas('status', 'Event saved.');

        $this->assertSame($media->id, $event->fresh()->featured_media_id);
    }

    public function test_editor_upload_attaches_review_image_to_draft_and_cannot_publish_it_early(): void
    {
        $this->prepareMySqlSchema(seed: true);
        Storage::fake('local');

        $admin = User::query()->where('email', 'local.admin@example.test')->firstOrFail();
        $post = Post::query()->where('slug', 'young-learners-shine')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.news.editor', ['post' => $post->slug]))
            ->patch(route('admin.news.update', $post), [
                'title' => $post->title,
                'excerpt' => $post->excerpt,
                'body' => $post->body,
                'status' => 'draft',
                'post_category_id' => $post->post_category_id,
                'featured_image' => UploadedFile::fake()->image('new-story-photo.jpg', 1200, 800),
                'featured_image_alt_text' => 'Pupils working together during a supervised school activity.',
                'featured_image_caption' => 'Learning together.',
                'featured_image_credit' => 'School communications team',
                'featured_image_consent_required' => '1',
                'featured_image_consent_confirmed' => '1',
                'featured_image_consent_reference' => 'CONSENT-NEWS-0001',
                'featured_image_safeguarding_confirmed' => '1',
            ])
            ->assertRedirect(route('admin.news'))
            ->assertSessionHas('status', 'Story saved. The new image is attached and awaiting media approval.');

        $media = Media::query()->where('original_name', 'new-story-photo.jpg')->firstOrFail();
        $updatedPost = $post->fresh();

        $this->assertSame($media->id, $updatedPost->featured_media_id);
        $this->assertSame('draft', $updatedPost->status);
        $this->assertSame('private', $media->visibility);
        $this->assertTrue($media->publication_restricted);
        $this->assertTrue($media->consent_confirmed);
        $this->assertSame('CONSENT-NEWS-0001', $media->consent_reference);
        Storage::disk('local')->assertExists($media->storagePath());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'media.uploaded',
            'subject_type' => Media::class,
            'subject_id' => $media->id,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.news.editor', ['post' => $updatedPost->slug]))
            ->patch(route('admin.news.update', $updatedPost), [
                'title' => $updatedPost->title,
                'excerpt' => $updatedPost->excerpt,
                'body' => $updatedPost->body,
                'status' => 'published',
                'published_at' => now()->format('Y-m-d\TH:i'),
                'post_category_id' => $updatedPost->post_category_id,
                'featured_media_id' => $media->id,
            ])
            ->assertRedirect(route('admin.news.editor', ['post' => $updatedPost->slug]))
            ->assertSessionHasErrors('featured_media_id');

        $this->assertSame('draft', $updatedPost->fresh()->status);

        $event = Event::query()->where('slug', 'community-mass')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.events.editor', ['event' => $event->slug]))
            ->patch(route('admin.events.update', $event), [
                'title' => $event->title,
                'summary' => $event->summary,
                'body' => $event->body,
                'starts_at' => $event->starts_at->format('Y-m-d\TH:i'),
                'ends_at' => $event->ends_at?->format('Y-m-d\TH:i'),
                'venue_name' => $event->venue_name,
                'event_category_id' => $event->event_category_id,
                'publication_status' => 'draft',
                'featured_image' => UploadedFile::fake()->image('new-event-photo.png', 900, 600),
                'featured_image_alt_text' => 'The school hall prepared for a community event.',
                'featured_image_consent_required' => '0',
                'featured_image_consent_confirmed' => '0',
                'featured_image_safeguarding_confirmed' => '1',
            ])
            ->assertRedirect(route('admin.events'))
            ->assertSessionHas('status', 'Event saved. The new image is attached and awaiting media approval.');

        $eventMedia = Media::query()->where('original_name', 'new-event-photo.png')->firstOrFail();

        $this->assertSame($eventMedia->id, $event->fresh()->featured_media_id);
        $this->assertTrue($eventMedia->publication_restricted);
        Storage::disk('local')->assertExists($eventMedia->storagePath());
    }

    public function test_search_sitemap_metadata_accessibility_and_analytics_consent_are_wired(): void
    {
        $this->prepareMySqlSchema(seed: true);

        SiteSetting::query()->where('group_name', 'analytics')->where('setting_key', 'enabled')->update(['value_json' => ['value' => true]]);
        SiteSetting::query()->where('group_name', 'analytics')->where('setting_key', 'provider')->update(['value_json' => ['value' => 'plausible']]);
        SiteSetting::query()->where('group_name', 'analytics')->where('setting_key', 'site_id')->update(['value_json' => ['value' => 'school.example.org']]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('class="skip-link"', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('data-cookie-reset', false)
            ->assertSee('"provider":"plausible"', false)
            ->assertDontSee('src="https://plausible.io/js/script.js"', false);

        $this->get(route('search', ['q' => 'admissions']))
            ->assertOk()
            ->assertSee('Search the website')
            ->assertSee('name="robots" content="noindex,follow"', false);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->assertSee(route('home'), false)
            ->assertDontSee(route('search'), false);
    }

    public function test_request_ids_and_branded_rate_limit_errors_are_returned(): void
    {
        $this->prepareMySqlSchema(seed: true);

        $requestId = (string) Str::uuid();
        $this->withHeader('X-Request-Id', $requestId)
            ->get('/missing-school-page')
            ->assertNotFound()
            ->assertHeader('X-Request-Id', $requestId)
            ->assertSee('Reference: '.$requestId)
            ->assertSee('noindex,nofollow', false);

        $response = null;
        for ($attempt = 0; $attempt < 31; $attempt++) {
            $response = $this->get(route('search', ['q' => 'school']));
        }

        $response
            ->assertTooManyRequests()
            ->assertSee('Please wait a moment')
            ->assertHeader('X-Request-Id');
    }
}
