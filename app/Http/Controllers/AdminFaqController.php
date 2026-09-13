<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminFaqController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Faq::class);

        return view('admin.faqs.index', [
            'faqs' => Faq::query()
                ->with(['category:id,name', 'verifier:id,name'])
                ->orderBy('sort_order')
                ->orderBy('question')
                ->paginate(30),
            'categories' => FaqCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
            'activeNav' => 'faqs',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Faq::class);

        $validated = $request->validate($this->contentRules());

        DB::transaction(function () use ($request, $validated): void {
            $faq = Faq::query()->create([
                ...$validated,
                'status' => 'draft',
                'published_at' => null,
                'verified_at' => null,
                'verified_by' => null,
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ]);

            $this->audit($request, 'faq.created', $faq, null, $this->snapshot($faq));
        });

        return back()->with('status', 'FAQ saved for review.');
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        Gate::authorize('update', $faq);

        $validated = $request->validate($this->contentRules());
        $oldValues = $this->snapshot($faq);
        $contentChanged = $faq->question !== $validated['question'] || $faq->answer !== $validated['answer'];

        DB::transaction(function () use ($request, $faq, $validated, $oldValues, $contentChanged): void {
            $faq->forceFill([
                ...$validated,
                'status' => $contentChanged ? 'draft' : $faq->status,
                'published_at' => $contentChanged ? null : $faq->published_at,
                'verified_at' => $contentChanged ? null : $faq->verified_at,
                'verified_by' => $contentChanged ? null : $faq->verified_by,
                'updated_by' => $request->user()?->id,
            ])->save();

            $this->audit($request, 'faq.updated', $faq, $oldValues, $this->snapshot($faq));
        });

        return back()->with('status', $contentChanged
            ? 'FAQ updated and returned to review.'
            : 'FAQ details saved.');
    }

    public function verify(Request $request, Faq $faq): RedirectResponse
    {
        Gate::authorize('verify', $faq);

        $validated = $request->validate([
            'action' => ['required', Rule::in(['verify', 'withdraw'])],
            'verification_notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $oldValues = $this->snapshot($faq);
        $verified = $validated['action'] === 'verify';

        DB::transaction(function () use ($request, $faq, $validated, $oldValues, $verified): void {
            $faq->forceFill([
                'status' => $verified ? 'published' : 'draft',
                'published_at' => $verified ? ($faq->published_at ?? now()) : null,
                'verified_at' => $verified ? now() : null,
                'verified_by' => $verified ? $request->user()?->id : null,
                'verification_notes' => $this->nullableText($validated['verification_notes'] ?? null),
                'updated_by' => $request->user()?->id,
            ])->save();

            $this->audit(
                $request,
                $verified ? 'faq.verified' : 'faq.withdrawn',
                $faq,
                $oldValues,
                $this->snapshot($faq),
            );
        });

        return back()->with('status', $verified
            ? 'FAQ verified and published.'
            : 'FAQ withdrawn for review.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function contentRules(): array
    {
        return [
            'faq_category_id' => ['nullable', 'integer', Rule::exists('faq_categories', 'id')->where('is_active', true)],
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string', 'max:10000'],
            'sort_order' => ['required', 'integer', 'between:0,10000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Faq $faq): array
    {
        return $faq->only([
            'faq_category_id',
            'question',
            'answer',
            'sort_order',
            'status',
            'published_at',
            'verified_at',
            'verified_by',
            'verification_notes',
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function audit(Request $request, string $action, Faq $faq, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::query()->create([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'subject_type' => Faq::class,
            'subject_id' => $faq->id,
            'description' => Str::headline(str_replace('.', ' ', $action)).': '.$faq->question,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
        ]);
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
