<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Programme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminProgrammeController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Programme::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'programme_type' => ['required', 'string', 'max:64'],
            'level' => ['nullable', 'string', 'max:100'],
            'summary' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $programme = Programme::query()->create([
                'name' => $validated['name'],
                'slug' => $this->uniqueSlug($validated['name']),
                'programme_type' => Str::slug($validated['programme_type'], '_') ?: 'school_programme',
                'level' => $validated['level'] ?? null,
                'summary' => $validated['summary'] ?? null,
                'sort_order' => ((int) Programme::query()->max('sort_order')) + 1,
                'status' => 'draft',
                'robots_index' => false,
                'robots_follow' => true,
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ]);

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'programme.created',
                'subject_type' => Programme::class,
                'subject_id' => $programme->id,
                'description' => 'Programme draft created: '.$programme->name,
                'new_values' => $programme->only(['name', 'slug', 'programme_type', 'level', 'summary', 'sort_order', 'status']),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return redirect()
            ->route('admin.programmes')
            ->with('status', 'Programme draft created.');
    }

    public function updateStatus(Request $request, Programme $programme): RedirectResponse
    {
        Gate::authorize('updateStatus', $programme);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['draft', 'review', 'published', 'archived'])],
        ]);

        $oldValues = $programme->only(['status', 'published_at', 'updated_by']);

        DB::transaction(function () use ($programme, $request, $validated, $oldValues): void {
            $status = $validated['status'];

            $programme->forceFill([
                'status' => $status,
                'published_at' => $status === 'published' ? ($programme->published_at ?? now()) : null,
                'updated_by' => $request->user()?->id,
            ])->save();

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'programme.status_updated',
                'subject_type' => Programme::class,
                'subject_id' => $programme->id,
                'description' => 'Programme status updated: '.$programme->name.' to '.str($status)->replace('_', ' ')->title(),
                'old_values' => $oldValues,
                'new_values' => $programme->only(['status', 'published_at', 'updated_by']),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return redirect()
            ->route('admin.programmes')
            ->with('status', 'Programme updated.');
    }

    public function updateMetadata(Request $request, Programme $programme): RedirectResponse
    {
        Gate::authorize('updateMetadata', $programme);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'level' => ['nullable', 'string', 'max:190'],
            'summary' => ['nullable', 'string', 'max:500'],
        ]);

        $oldValues = $programme->only(['name', 'level', 'summary', 'updated_by']);

        DB::transaction(function () use ($programme, $request, $validated, $oldValues): void {
            $programme->forceFill([
                'name' => $validated['name'],
                'level' => $validated['level'] ?: null,
                'summary' => $validated['summary'] ?: null,
                'updated_by' => $request->user()?->id,
            ])->save();

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'programme.metadata_updated',
                'subject_type' => Programme::class,
                'subject_id' => $programme->id,
                'description' => 'Programme metadata updated: '.$programme->name,
                'old_values' => $oldValues,
                'new_values' => $programme->only(['name', 'level', 'summary', 'updated_by']),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return redirect()
            ->route('admin.programmes')
            ->with('status', 'Programme saved.');
    }

    private function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'school-programme';
        $slug = $baseSlug;
        $suffix = 2;

        while (Programme::query()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
