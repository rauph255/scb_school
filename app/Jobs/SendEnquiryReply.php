<?php

namespace App\Jobs;

use App\Mail\EnquiryReplyMail;
use App\Models\AdmissionEnquiry;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\EmailReply;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEnquiryReply implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 120, 600];

    public function __construct(public readonly int $emailReplyId) {}

    public function handle(): void
    {
        $reply = EmailReply::query()->with('replyable')->findOrFail($this->emailReplyId);

        if ($reply->status === 'sent') {
            return;
        }

        $replyable = $reply->replyable;

        if (! $replyable instanceof AdmissionEnquiry && ! $replyable instanceof ContactMessage) {
            $reply->forceFill(['status' => 'failed', 'failed_at' => now(), 'failure_type' => 'missing_replyable'])->save();

            return;
        }

        $recipientName = $replyable instanceof AdmissionEnquiry
            ? $replyable->guardian_name
            : $replyable->full_name;

        $reply->forceFill(['status' => 'sending', 'failed_at' => null, 'failure_type' => null])->save();

        try {
            Mail::to($reply->recipient_email)->send(new EnquiryReplyMail(
                $recipientName,
                $replyable->reference_code,
                $reply->subject,
                $reply->body,
            ));
        } catch (Throwable $exception) {
            $reply->forceFill(['status' => 'queued'])->save();

            throw $exception;
        }

        DB::transaction(function () use ($reply, $replyable): void {
            $sentAt = now();
            $reply->forceFill(['status' => 'sent', 'sent_at' => $sentAt])->save();
            $replyable->forceFill([
                'status' => 'responded',
                'responded_at' => $sentAt,
                'assigned_to' => $replyable->assigned_to ?? $reply->user_id,
            ])->save();

            AuditLog::query()->create([
                'actor_id' => $reply->user_id,
                'action' => $replyable instanceof AdmissionEnquiry ? 'admission_enquiry.email_sent' : 'contact_message.email_sent',
                'subject_type' => $replyable::class,
                'subject_id' => $replyable->id,
                'description' => 'Email reply sent for '.$replyable->reference_code,
                'old_values' => ['email_reply_status' => 'queued'],
                'new_values' => ['email_reply_id' => $reply->id, 'email_reply_status' => 'sent'],
                'request_id' => $reply->request_id,
                'created_at' => $sentAt,
            ]);
        });
    }

    public function failed(?Throwable $exception): void
    {
        EmailReply::query()->whereKey($this->emailReplyId)->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_type' => $exception ? $exception::class : 'unknown',
        ]);
    }
}
