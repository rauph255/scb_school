<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\User;
use App\Support\EnquiryReplyQueue;
use App\Support\SafeCsvExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminContactMessageController extends Controller
{
    public function index(AdminExperienceController $experience): Response
    {
        Gate::authorize('viewAny', ContactMessage::class);

        return $experience->render('admin-contact-messages');
    }

    public function show(Request $request, ContactMessage $contactMessage): View
    {
        Gate::authorize('view', $contactMessage);
        $this->markViewed($request, $contactMessage);

        $contactMessage->load([
            'assignedTo:id,name,email',
            'notes' => fn ($query) => $query->with('user:id,name,email')->oldest(),
            'emailReplies' => fn ($query) => $query->with('author:id,name')->latest(),
        ]);

        return view('admin.contact-messages.show', [
            'message' => $contactMessage,
            'assignees' => $this->eligibleAssignees(),
            'activeNav' => 'contact-messages',
        ]);
    }

    public function export(Request $request, SafeCsvExporter $exporter): StreamedResponse
    {
        Gate::authorize('export', ContactMessage::class);

        $count = ContactMessage::query()->count();

        AuditLog::query()->create([
            'actor_id' => $request->user()?->id,
            'action' => 'contact_message.exported',
            'subject_type' => ContactMessage::class,
            'subject_id' => null,
            'description' => 'Contact messages exported: '.$count.' records',
            'old_values' => null,
            'new_values' => ['record_count' => $count],
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
        ]);

        $rows = ContactMessage::query()
            ->with('assignedTo:id,name')
            ->select([
                'id',
                'reference_code',
                'full_name',
                'email',
                'telephone',
                'subject',
                'message',
                'consent_confirmed',
                'status',
                'assigned_to',
                'responded_at',
                'closed_at',
                'created_at',
            ])
            ->lazyById(200)
            ->map(fn (ContactMessage $message): array => [
                $message->reference_code,
                $message->created_at,
                $message->full_name,
                $message->email,
                $message->telephone,
                $message->subject,
                $message->status,
                $message->assignedTo?->name,
                $message->responded_at,
                $message->closed_at,
                $message->consent_confirmed,
                $message->message,
            ]);

        return $exporter->download(
            'contact-messages-'.today()->format('Y-m-d').'.csv',
            [
                'Reference',
                'Received at',
                'Full name',
                'Email',
                'Telephone',
                'Subject',
                'Status',
                'Assigned to',
                'Responded at',
                'Closed at',
                'Consent confirmed',
                'Message',
            ],
            $rows,
        );
    }

    public function updateAssignment(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        Gate::authorize('assign', $contactMessage);

        $validated = $request->validate([
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->whereNull('deleted_at')),
            ],
        ]);

        $assignee = isset($validated['assigned_to'])
            ? User::query()->findOrFail($validated['assigned_to'])
            : null;

        if ($assignee && ! $assignee->hasPermission('contacts.manage')) {
            throw ValidationException::withMessages([
                'assigned_to' => 'Select an active user who can manage contact messages.',
            ]);
        }

        $oldValues = $contactMessage->only(['assigned_to']);

        DB::transaction(function () use ($contactMessage, $request, $assignee, $oldValues): void {
            $contactMessage->forceFill(['assigned_to' => $assignee?->id])->save();

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'contact_message.assignment_updated',
                'subject_type' => ContactMessage::class,
                'subject_id' => $contactMessage->id,
                'description' => 'Contact message assignment updated: '.$contactMessage->reference_code,
                'old_values' => $oldValues,
                'new_values' => $contactMessage->only(['assigned_to']),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return back()->with('status', 'Contact message assignment saved.');
    }

    public function updateStatus(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        Gate::authorize('updateStatus', $contactMessage);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['new', 'assigned', 'responded', 'closed', 'spam'])],
        ]);

        $oldValues = $contactMessage->only(['status', 'assigned_to', 'responded_at', 'closed_at']);

        DB::transaction(function () use ($contactMessage, $request, $validated, $oldValues): void {
            $status = $validated['status'];

            $contactMessage->forceFill([
                'status' => $status,
                'assigned_to' => in_array($status, ['assigned', 'responded', 'closed'], true)
                    ? ($contactMessage->assigned_to ?? $request->user()?->id)
                    : $contactMessage->assigned_to,
                'responded_at' => $status === 'responded' ? now() : $contactMessage->responded_at,
                'closed_at' => in_array($status, ['closed', 'spam'], true) ? now() : ($status === 'new' ? null : $contactMessage->closed_at),
            ])->save();

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'contact_message.status_updated',
                'subject_type' => ContactMessage::class,
                'subject_id' => $contactMessage->id,
                'description' => 'Contact message status updated: '.$contactMessage->reference_code.' to '.str($status)->replace('_', ' ')->title(),
                'old_values' => $oldValues,
                'new_values' => $contactMessage->only(['status', 'assigned_to', 'responded_at', 'closed_at']),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return back()->with('status', 'Contact message updated.');
    }

    public function storeNote(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        Gate::authorize('storeNote', $contactMessage);

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
            'is_sensitive' => ['sometimes', 'boolean'],
        ]);

        DB::transaction(function () use ($contactMessage, $request, $validated): void {
            $note = $contactMessage->notes()->create([
                'user_id' => $request->user()?->id,
                'note' => $validated['note'],
                'is_sensitive' => (bool) ($validated['is_sensitive'] ?? true),
            ]);

            if (! $contactMessage->assigned_to) {
                $contactMessage->forceFill(['assigned_to' => $request->user()?->id])->save();
            }

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'contact_message.note_created',
                'subject_type' => ContactMessage::class,
                'subject_id' => $contactMessage->id,
                'description' => 'Contact message note added: '.$contactMessage->reference_code,
                'old_values' => null,
                'new_values' => [
                    'note_id' => $note->id,
                    'is_sensitive' => $note->is_sensitive,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return back()->with('status', 'Message note saved.');
    }

    public function reply(Request $request, ContactMessage $contactMessage, EnquiryReplyQueue $replies): RedirectResponse
    {
        Gate::authorize('reply', $contactMessage);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $replies->queue($request, $contactMessage, $validated['subject'], $validated['body']);

        return back()->with('status', 'Email reply queued for delivery.');
    }

    private function eligibleAssignees()
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles.permissions', fn ($query) => $query->where('slug', 'contacts.manage'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function markViewed(Request $request, ContactMessage $contactMessage): void
    {
        if ($contactMessage->first_viewed_at) {
            return;
        }

        DB::transaction(function () use ($request, $contactMessage): void {
            $viewedAt = now();
            $updated = ContactMessage::query()
                ->whereKey($contactMessage->id)
                ->whereNull('first_viewed_at')
                ->update([
                    'first_viewed_at' => $viewedAt,
                    'viewed_by' => $request->user()?->id,
                ]);

            if ($updated === 0) {
                return;
            }

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'contact_message.first_viewed',
                'subject_type' => ContactMessage::class,
                'subject_id' => $contactMessage->id,
                'description' => 'Contact message opened: '.$contactMessage->reference_code,
                'old_values' => ['first_viewed_at' => null, 'viewed_by' => null],
                'new_values' => ['first_viewed_at' => $viewedAt, 'viewed_by' => $request->user()?->id],
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        $contactMessage->refresh();
    }
}
