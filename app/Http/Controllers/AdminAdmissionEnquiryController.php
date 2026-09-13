<?php

namespace App\Http\Controllers;

use App\Models\AdmissionEnquiry;
use App\Models\AuditLog;
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

class AdminAdmissionEnquiryController extends Controller
{
    public function index(AdminExperienceController $experience): Response
    {
        Gate::authorize('viewAny', AdmissionEnquiry::class);

        return $experience->render('admin-admissions');
    }

    public function show(Request $request, AdmissionEnquiry $admissionEnquiry): View
    {
        Gate::authorize('view', $admissionEnquiry);
        $this->markViewed($request, $admissionEnquiry);

        $admissionEnquiry->load([
            'assignedTo:id,name,email',
            'notes' => fn ($query) => $query->with('user:id,name,email')->oldest(),
            'emailReplies' => fn ($query) => $query->with('author:id,name')->latest(),
        ]);

        return view('admin.admissions.show', [
            'enquiry' => $admissionEnquiry,
            'assignees' => $this->eligibleAssignees(),
            'activeNav' => 'admissions',
        ]);
    }

    public function export(Request $request, SafeCsvExporter $exporter): StreamedResponse
    {
        Gate::authorize('export', AdmissionEnquiry::class);

        $count = AdmissionEnquiry::query()->count();

        AuditLog::query()->create([
            'actor_id' => $request->user()?->id,
            'action' => 'admission_enquiry.exported',
            'subject_type' => AdmissionEnquiry::class,
            'subject_id' => null,
            'description' => 'Admission enquiries exported: '.$count.' records',
            'old_values' => null,
            'new_values' => ['record_count' => $count],
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
        ]);

        $rows = AdmissionEnquiry::query()
            ->with('assignedTo:id,name')
            ->select([
                'id',
                'reference_code',
                'guardian_name',
                'email',
                'telephone',
                'intended_level',
                'intended_term',
                'intended_year',
                'preferred_contact_method',
                'message',
                'consent_confirmed',
                'status',
                'assigned_to',
                'responded_at',
                'closed_at',
                'created_at',
            ])
            ->lazyById(200)
            ->map(fn (AdmissionEnquiry $enquiry): array => [
                $enquiry->reference_code,
                $enquiry->created_at,
                $enquiry->guardian_name,
                $enquiry->email,
                $enquiry->telephone,
                $enquiry->intended_level,
                $enquiry->intended_term,
                $enquiry->intended_year,
                $enquiry->preferred_contact_method,
                $enquiry->status,
                $enquiry->assignedTo?->name,
                $enquiry->responded_at,
                $enquiry->closed_at,
                $enquiry->consent_confirmed,
                $enquiry->message,
            ]);

        return $exporter->download(
            'admission-enquiries-'.today()->format('Y-m-d').'.csv',
            [
                'Reference',
                'Received at',
                'Guardian name',
                'Email',
                'Telephone',
                'Intended level',
                'Intended term',
                'Intended year',
                'Preferred contact method',
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

    public function updateAssignment(Request $request, AdmissionEnquiry $admissionEnquiry): RedirectResponse
    {
        Gate::authorize('assign', $admissionEnquiry);

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

        if ($assignee && ! $assignee->hasPermission('admissions.manage')) {
            throw ValidationException::withMessages([
                'assigned_to' => 'Select an active user who can manage admission enquiries.',
            ]);
        }

        $oldValues = $admissionEnquiry->only(['assigned_to']);

        DB::transaction(function () use ($admissionEnquiry, $request, $assignee, $oldValues): void {
            $admissionEnquiry->forceFill(['assigned_to' => $assignee?->id])->save();

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'admission_enquiry.assignment_updated',
                'subject_type' => AdmissionEnquiry::class,
                'subject_id' => $admissionEnquiry->id,
                'description' => 'Admission enquiry assignment updated: '.$admissionEnquiry->reference_code,
                'old_values' => $oldValues,
                'new_values' => $admissionEnquiry->only(['assigned_to']),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return back()->with('status', 'Admission enquiry assignment saved.');
    }

    public function updateStatus(Request $request, AdmissionEnquiry $admissionEnquiry): RedirectResponse
    {
        Gate::authorize('updateStatus', $admissionEnquiry);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['new', 'in_progress', 'responded', 'closed'])],
        ]);

        $oldValues = $admissionEnquiry->only(['status', 'assigned_to', 'responded_at', 'closed_at']);

        DB::transaction(function () use ($admissionEnquiry, $request, $validated, $oldValues): void {
            $status = $validated['status'];

            $admissionEnquiry->forceFill([
                'status' => $status,
                'assigned_to' => $status === 'new'
                    ? $admissionEnquiry->assigned_to
                    : ($admissionEnquiry->assigned_to ?? $request->user()?->id),
                'responded_at' => $status === 'responded' ? now() : $admissionEnquiry->responded_at,
                'closed_at' => $status === 'closed' ? now() : ($status === 'new' ? null : $admissionEnquiry->closed_at),
            ])->save();

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'admission_enquiry.status_updated',
                'subject_type' => AdmissionEnquiry::class,
                'subject_id' => $admissionEnquiry->id,
                'description' => 'Admission enquiry status updated: '.$admissionEnquiry->reference_code.' to '.str($status)->replace('_', ' ')->title(),
                'old_values' => $oldValues,
                'new_values' => $admissionEnquiry->only(['status', 'assigned_to', 'responded_at', 'closed_at']),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        return back()->with('status', 'Admission enquiry updated.');
    }

    public function storeNote(Request $request, AdmissionEnquiry $admissionEnquiry): RedirectResponse
    {
        Gate::authorize('storeNote', $admissionEnquiry);

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
            'is_sensitive' => ['sometimes', 'boolean'],
        ]);

        DB::transaction(function () use ($admissionEnquiry, $request, $validated): void {
            $note = $admissionEnquiry->notes()->create([
                'user_id' => $request->user()?->id,
                'note' => $validated['note'],
                'is_sensitive' => (bool) ($validated['is_sensitive'] ?? true),
            ]);

            if (! $admissionEnquiry->assigned_to) {
                $admissionEnquiry->forceFill(['assigned_to' => $request->user()?->id])->save();
            }

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => 'admission_enquiry.note_created',
                'subject_type' => AdmissionEnquiry::class,
                'subject_id' => $admissionEnquiry->id,
                'description' => 'Admission enquiry note added: '.$admissionEnquiry->reference_code,
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

        return back()->with('status', 'Admission note saved.');
    }

    public function reply(Request $request, AdmissionEnquiry $admissionEnquiry, EnquiryReplyQueue $replies): RedirectResponse
    {
        Gate::authorize('reply', $admissionEnquiry);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $replies->queue($request, $admissionEnquiry, $validated['subject'], $validated['body']);

        return back()->with('status', 'Email reply queued for delivery.');
    }

    private function eligibleAssignees()
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles.permissions', fn ($query) => $query->where('slug', 'admissions.manage'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function markViewed(Request $request, AdmissionEnquiry $admissionEnquiry): void
    {
        if ($admissionEnquiry->first_viewed_at) {
            return;
        }

        DB::transaction(function () use ($request, $admissionEnquiry): void {
            $viewedAt = now();
            $updated = AdmissionEnquiry::query()
                ->whereKey($admissionEnquiry->id)
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
                'action' => 'admission_enquiry.first_viewed',
                'subject_type' => AdmissionEnquiry::class,
                'subject_id' => $admissionEnquiry->id,
                'description' => 'Admission enquiry opened: '.$admissionEnquiry->reference_code,
                'old_values' => ['first_viewed_at' => null, 'viewed_by' => null],
                'new_values' => ['first_viewed_at' => $viewedAt, 'viewed_by' => $request->user()?->id],
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => (string) $request->headers->get('X-Request-Id', Str::uuid()),
            ]);
        });

        $admissionEnquiry->refresh();
    }
}
