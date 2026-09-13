<?php

namespace App\Support;

use App\Models\AdmissionEnquiry;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class AdminAlertFeed
{
    /**
     * @return array{count: int, items: Collection<int, array<string, mixed>>}
     */
    public function for(?User $user): array
    {
        if (! $user) {
            return ['count' => 0, 'items' => collect()];
        }

        $items = collect();
        $count = 0;

        if (Gate::forUser($user)->allows('viewAny', AdmissionEnquiry::class)) {
            $query = AdmissionEnquiry::query()->whereNull('first_viewed_at');
            $count += $query->count();
            $items = $items->merge($query
                ->latest()
                ->limit(6)
                ->get(['id', 'reference_code', 'guardian_name', 'intended_level', 'created_at'])
                ->map(fn (AdmissionEnquiry $enquiry): array => [
                    'id' => 'admission-'.$enquiry->id,
                    'type' => 'Admission enquiry',
                    'title' => $enquiry->guardian_name,
                    'detail' => $enquiry->intended_level ?: $enquiry->reference_code,
                    'url' => route('admin.admissions.show', $enquiry),
                    'created_at' => $enquiry->created_at,
                    'icon' => 'clipboard-list',
                    'tone' => 'gold',
                ]));
        }

        if (Gate::forUser($user)->allows('viewAny', ContactMessage::class)) {
            $query = ContactMessage::query()->whereNull('first_viewed_at');
            $count += $query->count();
            $items = $items->merge($query
                ->latest()
                ->limit(6)
                ->get(['id', 'reference_code', 'full_name', 'subject', 'created_at'])
                ->map(fn (ContactMessage $message): array => [
                    'id' => 'message-'.$message->id,
                    'type' => 'Contact message',
                    'title' => $message->full_name,
                    'detail' => $message->subject,
                    'url' => route('admin.contact-messages.show', $message),
                    'created_at' => $message->created_at,
                    'icon' => 'mail',
                    'tone' => 'crimson',
                ]));
        }

        return [
            'count' => $count,
            'items' => $items->sortByDesc('created_at')->take(8)->values(),
        ];
    }
}
