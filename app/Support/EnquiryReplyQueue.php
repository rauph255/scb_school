<?php

namespace App\Support;

use App\Jobs\SendEnquiryReply;
use App\Models\AdmissionEnquiry;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\EmailReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnquiryReplyQueue
{
    public function queue(
        Request $request,
        AdmissionEnquiry|ContactMessage $replyable,
        string $subject,
        string $body,
    ): EmailReply {
        return DB::transaction(function () use ($request, $replyable, $subject, $body): EmailReply {
            $requestId = (string) $request->headers->get('X-Request-Id', Str::uuid());
            $reply = $replyable->emailReplies()->create([
                'user_id' => $request->user()?->id,
                'recipient_email' => $replyable->email,
                'subject' => trim($subject),
                'body' => trim($body),
                'status' => 'queued',
                'queued_at' => now(),
                'request_id' => $requestId,
            ]);

            $replyable->forceFill([
                'status' => $replyable instanceof AdmissionEnquiry ? 'in_progress' : 'assigned',
                'assigned_to' => $replyable->assigned_to ?? $request->user()?->id,
            ])->save();

            AuditLog::query()->create([
                'actor_id' => $request->user()?->id,
                'action' => $replyable instanceof AdmissionEnquiry ? 'admission_enquiry.email_queued' : 'contact_message.email_queued',
                'subject_type' => $replyable::class,
                'subject_id' => $replyable->id,
                'description' => 'Email reply queued for '.$replyable->reference_code,
                'old_values' => null,
                'new_values' => ['email_reply_id' => $reply->id, 'status' => 'queued'],
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'request_id' => $requestId,
            ]);

            SendEnquiryReply::dispatch($reply->id)->afterCommit();

            return $reply;
        });
    }
}
