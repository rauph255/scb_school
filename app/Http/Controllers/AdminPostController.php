<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use App\Support\ContentFeaturedImageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminPostController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Post::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string'],
        ]);

        $post = DB::transaction(function () use ($request, $validated): Post {
            $post = Post::query()->create([
                'title' => $validated['title'],
                'slug' => $this->uniqueSlugForCreate($validated['title']),
                'excerpt' => $validated['excerpt'] ?? null,
                'body' => $validated['body'] ?? '',
                'status' => 'draft',
                'is_featured' => false,
                'author_id' => $request->user()?->id,
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ]);

            $this->audit($request, 'post.created', $post, 'News story draft created: '.$post->title, null, $post->only([
                'title', 'slug', 'excerpt', 'body', 'status', 'author_id', 'created_by', 'updated_by',
            ]));

            return $post;
        });

        return redirect()
            ->route('admin.news.editor', ['post' => $post->slug])
            ->with('status', 'Story draft created.');
    }

    public function update(
        Request $request,
        Post $post,
        ContentFeaturedImageManager $featuredImages,
    ): RedirectResponse {
        Gate::authorize('update', $post);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'body' => ['required', 'string'],
            'status' => ['required', Rule::in(['draft', 'review', 'published', 'scheduled', 'archived'])],
            'published_at' => ['nullable', 'date', 'required_if:status,scheduled'],
            'is_featured' => ['nullable', 'boolean'],
            'post_category_id' => ['nullable', 'integer', Rule::exists('post_categories', 'id')],
            'tags' => ['nullable', 'string', 'max:500'],
            ...$featuredImages->rules(),
        ]);

        if ($request->hasFile('featured_image')) {
            Gate::authorize('create', Media::class);
        }

        $selectedImage = $request->hasFile('featured_image')
            ? null
            : (array_key_exists('featured_media_id', $validated)
                ? $featuredImages->selectedImage($validated['featured_media_id'])
                : $post->featuredMedia);

        if (! $request->hasFile('featured_image') && array_key_exists('featured_media_id', $validated) && $selectedImage) {
            Gate::authorize('view', $selectedImage);
        }

        $oldValues = $post->load('tags')->only([
            'title',
            'excerpt',
            'body',
            'status',
            'is_featured',
            'published_at',
            'scheduled_at',
            'post_category_id',
            'featured_media_id',
        ]);
        $oldValues['tags'] = $post->tags->pluck('name')->all();

        $uploadedImage = $featuredImages->run(
            $request,
            $selectedImage,
            function (?Media $featuredImage, bool $uploaded) use (
                $post,
                $request,
                $validated,
                $oldValues,
                $featuredImages,
            ): bool {
                $publishDate = $validated['published_at'] ?? null;

                if (in_array($validated['status'], ['published', 'scheduled'], true)) {
                    $featuredImages->assertCanPublish($featuredImage);
                }

                $post->forceFill([
                    'title' => $validated['title'],
                    'slug' => $this->uniqueSlug($validated['title'], $post),
                    'excerpt' => $validated['excerpt'] ?? null,
                    'body' => $validated['body'],
                    'featured_media_id' => $featuredImage?->id,
                    'status' => $validated['status'],
                    'is_featured' => $request->boolean('is_featured'),
                    'published_at' => $validated['status'] === 'published' ? ($publishDate ?? now()) : null,
                    'scheduled_at' => $validated['status'] === 'scheduled' ? $publishDate : null,
                    'post_category_id' => $validated['post_category_id'] ?? null,
                    'updated_by' => $request->user()?->id,
                ])->save();

                $tagIds = collect(explode(',', (string) ($validated['tags'] ?? '')))
                    ->map(fn (string $tag): string => trim($tag))
                    ->filter()
                    ->unique(fn (string $tag): string => Str::slug($tag))
                    ->map(fn (string $tag): int => Tag::query()->firstOrCreate(
                        ['slug' => Str::slug($tag)],
                        ['name' => Str::headline($tag)]
                    )->id)
                    ->all();

                $post->tags()->sync($tagIds);
                $post->load('tags');

                $this->audit(
                    $request,
                    'post.updated',
                    $post,
                    'News story updated from visible admin editor: '.$post->title,
                    $oldValues,
                    [
                        ...$post->only([
                            'title',
                            'excerpt',
                            'body',
                            'status',
                            'is_featured',
                            'published_at',
                            'scheduled_at',
                            'post_category_id',
                            'featured_media_id',
                        ]),
                        'tags' => $post->tags->pluck('name')->all(),
                    ],
                );

                return $uploaded;
            },
        );

        return redirect()
            ->route('admin.news')
            ->with('status', $uploadedImage
                ? 'Story saved. The new image is attached and awaiting media approval.'
                : 'Story saved.');
    }

    private function uniqueSlug(string $title, Post $post): string
    {
        $baseSlug = Str::slug($title) ?: 'news-story';
        $slug = $baseSlug;
        $suffix = 2;

        while (Post::query()->withTrashed()->where('slug', $slug)->whereKeyNot($post->id)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function uniqueSlugForCreate(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'news-story';
        $slug = $baseSlug;
        $suffix = 2;

        while (Post::query()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function audit(Request $request, string $action, Post $post, string $description, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::query()->create([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'subject_type' => Post::class,
            'subject_id' => $post->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
        ]);
    }
}
