<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Page;
use App\Models\PageBlock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminPageBlockController extends Controller
{
    public function store(Request $request, Page $page): RedirectResponse
    {
        Gate::authorize('update', $page);

        $validated = $request->validate($this->blockRules());

        DB::transaction(function () use ($request, $page, $validated): void {
            $block = $page->blocks()->create([
                ...$this->blockValues($request, $validated),
                'sort_order' => ((int) $page->blocks()->max('sort_order')) + 1,
            ]);

            $this->audit(
                $request,
                'page_block.created',
                $block,
                'Page block created: '.$page->title.' / '.$block->block_type,
                null,
                $this->snapshot($block),
            );
        });

        return $this->redirectToEditor($page, 'Page block added.');
    }

    public function update(Request $request, PageBlock $pageBlock): RedirectResponse
    {
        $pageBlock->load('page');

        Gate::authorize('update', $pageBlock->page);

        $validated = $request->validate($this->blockRules());
        $oldValues = $this->snapshot($pageBlock);

        DB::transaction(function () use ($request, $pageBlock, $validated, $oldValues): void {
            $pageBlock->forceFill($this->blockValues($request, $validated))->save();

            $this->audit(
                $request,
                'page_block.updated',
                $pageBlock,
                'Page block updated: '.$pageBlock->page->title.' / '.$pageBlock->block_type,
                $oldValues,
                $this->snapshot($pageBlock),
            );
        });

        return $this->redirectToEditor($pageBlock->page, 'Page block saved.');
    }

    public function updateVisibility(Request $request, PageBlock $pageBlock): RedirectResponse
    {
        $pageBlock->load('page');

        Gate::authorize('update', $pageBlock->page);

        $validated = $request->validate([
            'is_enabled' => ['required', 'boolean'],
        ]);

        $oldValues = $pageBlock->only(['is_enabled']);

        DB::transaction(function () use ($request, $pageBlock, $validated, $oldValues): void {
            $pageBlock->forceFill([
                'is_enabled' => (bool) $validated['is_enabled'],
            ])->save();

            $this->audit(
                $request,
                'page_block.visibility_updated',
                $pageBlock,
                'Page block visibility updated: '.$pageBlock->page->title.' / '.$pageBlock->block_type,
                $oldValues,
                $pageBlock->only(['is_enabled']),
            );
        });

        return $this->redirectToEditor($pageBlock->page, 'Page block updated.');
    }

    public function reorder(Request $request, PageBlock $pageBlock): RedirectResponse
    {
        $pageBlock->load('page');

        Gate::authorize('update', $pageBlock->page);

        $validated = $request->validate([
            'direction' => ['required', Rule::in(['up', 'down'])],
        ]);

        DB::transaction(function () use ($request, $pageBlock, $validated): void {
            $blocks = PageBlock::query()
                ->where('page_id', $pageBlock->page_id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $oldOrder = $blocks->pluck('id')->all();
            $currentIndex = $blocks->search(fn (PageBlock $block): bool => $block->is($pageBlock));
            $targetIndex = $validated['direction'] === 'up'
                ? max(0, (int) $currentIndex - 1)
                : min($blocks->count() - 1, (int) $currentIndex + 1);

            if ($currentIndex !== false && $currentIndex !== $targetIndex) {
                $current = $blocks->get($currentIndex);
                $target = $blocks->get($targetIndex);
                $blocks->put($currentIndex, $target);
                $blocks->put($targetIndex, $current);
            }

            $blocks->values()->each(function (PageBlock $block, int $index): void {
                $block->forceFill(['sort_order' => $index + 1])->save();
            });

            $this->audit(
                $request,
                'page_block.reordered',
                $pageBlock,
                'Page block reordered: '.$pageBlock->page->title.' / '.$pageBlock->block_type,
                ['block_ids' => $oldOrder],
                ['block_ids' => $blocks->sortBy('sort_order')->pluck('id')->all()],
            );
        });

        return $this->redirectToEditor($pageBlock->page, 'Page block order updated.');
    }

    public function duplicate(Request $request, PageBlock $pageBlock): RedirectResponse
    {
        $pageBlock->load('page');

        Gate::authorize('update', $pageBlock->page);

        DB::transaction(function () use ($request, $pageBlock): void {
            $duplicate = $pageBlock->replicate();
            $duplicate->sort_order = ((int) $pageBlock->page->blocks()->max('sort_order')) + 1;
            $duplicate->save();

            $this->audit(
                $request,
                'page_block.duplicated',
                $duplicate,
                'Page block duplicated: '.$pageBlock->page->title.' / '.$pageBlock->block_type,
                null,
                $this->snapshot($duplicate),
            );
        });

        return $this->redirectToEditor($pageBlock->page, 'Page block duplicated.');
    }

    public function destroy(Request $request, PageBlock $pageBlock): RedirectResponse
    {
        $pageBlock->load('page');

        Gate::authorize('update', $pageBlock->page);

        $page = $pageBlock->page;
        $oldValues = $this->snapshot($pageBlock);

        DB::transaction(function () use ($request, $pageBlock, $page, $oldValues): void {
            $pageBlock->delete();

            $page->blocks()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->each(function (PageBlock $block, int $index): void {
                    $block->forceFill(['sort_order' => $index + 1])->save();
                });

            $this->audit(
                $request,
                'page_block.deleted',
                $pageBlock,
                'Page block deleted: '.$page->title.' / '.$pageBlock->block_type,
                $oldValues,
                null,
            );
        });

        return $this->redirectToEditor($page, 'Page block deleted.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function blockRules(): array
    {
        return [
            'block_type' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9_]+$/'],
            'heading' => ['nullable', 'string', 'max:255'],
            'subheading' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:50000'],
            'media_id' => ['nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at')],
            'settings_json' => ['nullable', 'json'],
            'is_enabled' => ['nullable', 'boolean'],
            'visible_from' => ['nullable', 'date'],
            'visible_until' => ['nullable', 'date', 'after_or_equal:visible_from'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function blockValues(Request $request, array $validated): array
    {
        return [
            'block_type' => $validated['block_type'],
            'heading' => $this->nullableText($validated['heading'] ?? null),
            'subheading' => $this->nullableText($validated['subheading'] ?? null),
            'body' => $this->nullableText($validated['body'] ?? null),
            'media_id' => $validated['media_id'] ?? null,
            'settings' => filled($validated['settings_json'] ?? null)
                ? json_decode($validated['settings_json'], true, 512, JSON_THROW_ON_ERROR)
                : null,
            'is_enabled' => $request->boolean('is_enabled'),
            'visible_from' => $validated['visible_from'] ?? null,
            'visible_until' => $validated['visible_until'] ?? null,
        ];
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(PageBlock $pageBlock): array
    {
        return $pageBlock->only([
            'page_id',
            'block_type',
            'heading',
            'subheading',
            'body',
            'media_id',
            'settings',
            'sort_order',
            'is_enabled',
            'visible_from',
            'visible_until',
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function audit(
        Request $request,
        string $action,
        PageBlock $pageBlock,
        string $description,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        AuditLog::query()->create([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'subject_type' => PageBlock::class,
            'subject_id' => $pageBlock->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
        ]);
    }

    private function redirectToEditor(Page $page, string $status): RedirectResponse
    {
        return redirect()
            ->route('admin.pages.editor', ['page' => $page->slug])
            ->with('status', $status);
    }
}
