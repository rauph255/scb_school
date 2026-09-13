<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StaffContributionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:1000'],
            'body' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $post = Post::query()->create([
                'post_category_id' => PostCategory::query()->where('slug', 'school-news')->value('id'),
                'author_id' => $request->user()?->id,
                'title' => $validated['title'],
                'slug' => $this->uniqueSlug($validated['title']),
                'excerpt' => $validated['excerpt'],
                'body' => $validated['body'] ?: $validated['excerpt'],
                'status' => 'review',
                'is_featured' => false,
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ]);

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'staff_contribution.submitted',
                'subject_type' => Post::class,
                'subject_id' => $post->id,
                'description' => 'Staff news contribution submitted: '.$post->title,
                'old_values' => [],
                'new_values' => $post->only(['title', 'slug', 'excerpt', 'status', 'author_id']),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return redirect()
            ->route('staff.dashboard')
            ->with('status', 'Contribution submitted for review.');
    }

    private function uniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'staff-contribution';
        $slug = $baseSlug;
        $suffix = 2;

        while (Post::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
