<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Media;
use App\Support\ContentFeaturedImageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminEventController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Event::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'venue_name' => ['nullable', 'string', 'max:255'],
        ]);

        $event = DB::transaction(function () use ($request, $validated): Event {
            $event = Event::query()->create([
                'title' => $validated['title'],
                'slug' => $this->uniqueSlugForCreate($validated['title']),
                'starts_at' => $validated['starts_at'],
                'timezone' => config('app.timezone', 'UTC'),
                'venue_name' => $validated['venue_name'] ?? null,
                'event_state' => 'scheduled',
                'publication_status' => 'draft',
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ]);

            $this->audit($request, 'event.created', $event, 'Event draft created: '.$event->title, null, $event->only([
                'title', 'slug', 'starts_at', 'timezone', 'venue_name', 'event_state', 'publication_status', 'created_by', 'updated_by',
            ]));

            return $event;
        });

        return redirect()
            ->route('admin.events.editor', ['event' => $event->slug])
            ->with('status', 'Event draft created.');
    }

    public function update(
        Request $request,
        Event $event,
        ContentFeaturedImageManager $featuredImages,
    ): RedirectResponse {
        Gate::authorize('update', $event);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'event_category_id' => ['nullable', 'integer', Rule::exists('event_categories', 'id')],
            'publication_status' => ['required', Rule::in(['draft', 'review', 'published', 'archived'])],
            ...$featuredImages->rules(),
        ]);

        if ($request->hasFile('featured_image')) {
            Gate::authorize('create', Media::class);
        }

        $selectedImage = $request->hasFile('featured_image')
            ? null
            : (array_key_exists('featured_media_id', $validated)
                ? $featuredImages->selectedImage($validated['featured_media_id'])
                : $event->featuredMedia);

        if (! $request->hasFile('featured_image') && array_key_exists('featured_media_id', $validated) && $selectedImage) {
            Gate::authorize('view', $selectedImage);
        }

        $oldValues = $event->only([
            'event_category_id',
            'title',
            'slug',
            'summary',
            'body',
            'starts_at',
            'ends_at',
            'venue_name',
            'featured_media_id',
            'publication_status',
            'published_at',
        ]);

        $uploadedImage = $featuredImages->run(
            $request,
            $selectedImage,
            function (?Media $featuredImage, bool $uploaded) use (
                $event,
                $request,
                $validated,
                $oldValues,
                $featuredImages,
            ): bool {
                if ($validated['publication_status'] === 'published') {
                    $featuredImages->assertCanPublish($featuredImage);
                }

                $event->forceFill([
                    'event_category_id' => $validated['event_category_id'] ?? null,
                    'title' => $validated['title'],
                    'slug' => $this->uniqueSlug($validated['title'], $event),
                    'summary' => $validated['summary'] ?? null,
                    'body' => $validated['body'] ?? null,
                    'starts_at' => $validated['starts_at'],
                    'ends_at' => $validated['ends_at'] ?? null,
                    'venue_name' => $validated['venue_name'] ?? null,
                    'featured_media_id' => $featuredImage?->id,
                    'publication_status' => $validated['publication_status'],
                    'published_at' => $validated['publication_status'] === 'published'
                        ? ($event->published_at ?? now())
                        : null,
                    'updated_by' => $request->user()?->id,
                ])->save();

                $this->audit(
                    $request,
                    'event.updated',
                    $event,
                    'Event updated from visible admin editor: '.$event->title,
                    $oldValues,
                    $event->only([
                        'event_category_id',
                        'title',
                        'slug',
                        'summary',
                        'body',
                        'starts_at',
                        'ends_at',
                        'venue_name',
                        'featured_media_id',
                        'publication_status',
                        'published_at',
                    ]),
                );

                return $uploaded;
            },
        );

        return redirect()
            ->route('admin.events')
            ->with('status', $uploadedImage
                ? 'Event saved. The new image is attached and awaiting media approval.'
                : 'Event saved.');
    }

    private function uniqueSlug(string $title, Event $event): string
    {
        $baseSlug = Str::slug($title) ?: 'school-event';
        $slug = $baseSlug;
        $suffix = 2;

        while (Event::query()->withTrashed()->where('slug', $slug)->whereKeyNot($event->id)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function uniqueSlugForCreate(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'school-event';
        $slug = $baseSlug;
        $suffix = 2;

        while (Event::query()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function audit(Request $request, string $action, Event $event, string $description, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::query()->create([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'subject_type' => Event::class,
            'subject_id' => $event->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
        ]);
    }
}
