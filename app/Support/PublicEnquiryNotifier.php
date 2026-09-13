<?php

namespace App\Support;

use App\Mail\PublicSubmissionReceived;
use App\Models\AdmissionEnquiry;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PublicEnquiryNotifier
{
    public function admission(AdmissionEnquiry $enquiry): void
    {
        $this->queue(
            'admission enquiry',
            $enquiry->reference_code,
            route('admin.admissions.show', $enquiry),
        );
    }

    public function contact(ContactMessage $message): void
    {
        $this->queue(
            'contact message',
            $message->reference_code,
            route('admin.contact-messages.show', $message),
        );
    }

    private function queue(string $type, string $reference, string $adminUrl): void
    {
        $recipient = SiteSetting::query()
            ->where('group_name', 'contact')
            ->where('setting_key', 'primary_email')
            ->value('value_json');
        $email = is_array($recipient) ? ($recipient['value'] ?? null) : null;

        if (! is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return;
        }

        try {
            Mail::to($email)->queue(new PublicSubmissionReceived($type, $reference, $adminUrl));
        } catch (Throwable $exception) {
            Log::warning('Public submission notification could not be queued.', [
                'reference' => $reference,
                'exception' => $exception::class,
            ]);
        }
    }
}
