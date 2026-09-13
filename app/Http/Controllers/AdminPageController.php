<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminPageController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Page::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
        ]);

        $page = DB::transaction(function () use ($request, $validated): Page {
            $page = Page::query()->create([
                'title' => $validated['title'],
                'slug' => $this->uniqueSlug($validated['title']),
                'page_type' => 'standard',
                'template_key' => 'standard',
                'status' => 'draft',
                'excerpt' => $validated['excerpt'] ?? null,
                'robots_index' => false,
                'robots_follow' => true,
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ]);

            $this->audit($request, 'page.created', $page, 'Page draft created: '.$page->title, null, $page->only([
                'title', 'slug', 'page_type', 'template_key', 'status', 'excerpt', 'created_by', 'updated_by',
            ]));

            return $page;
        });

        return redirect()
            ->route('admin.pages.editor', ['page' => $page->slug])
            ->with('status', 'Page draft created.');
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        Gate::authorize('update', $page);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:190', Rule::unique('pages', 'slug')->ignore($page->id)],
            'status' => ['required', Rule::in(['draft', 'review', 'published', 'archived'])],
            'published_at' => ['nullable', 'date'],
            'robots_index' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:320'],
        ]);

        if ($validated['status'] === 'published') {
            Gate::authorize('publish', $page);
        }

        $oldValues = $page->only([
            'title',
            'slug',
            'status',
            'published_at',
            'robots_index',
            'seo_title',
            'seo_description',
        ]);

        DB::transaction(function () use ($page, $request, $validated, $oldValues): void {
            $page->forceFill([
                'title' => $validated['title'],
                'slug' => Str::slug($validated['slug']),
                'status' => $validated['status'],
                'published_at' => $validated['published_at'] ?? ($validated['status'] === 'published' ? now() : null),
                'robots_index' => $request->boolean('robots_index'),
                'seo_title' => $validated['seo_title'] ?? null,
                'seo_description' => $validated['seo_description'] ?? null,
                'updated_by' => $request->user()?->id,
            ])->save();

            $this->audit(
                $request,
                'page.updated',
                $page,
                'Page updated from visible admin editor: '.$page->title,
                $oldValues,
                $page->only([
                    'title',
                    'slug',
                    'status',
                    'published_at',
                    'robots_index',
                    'seo_title',
                    'seo_description',
                ]),
            );
        });

        return redirect()
            ->route('admin.pages')
            ->with('status', 'Page saved.');
    }

    private function uniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'managed-page';
        $slug = $baseSlug;
        $suffix = 2;

        while (Page::query()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function audit(Request $request, string $action, Page $page, string $description, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::query()->create([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'subject_type' => Page::class,
            'subject_id' => $page->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
        ]);
    }
}
