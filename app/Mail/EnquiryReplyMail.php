<?php

namespace App\Mail;

use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnquiryReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $recipientName,
        public readonly string $referenceCode,
        public readonly string $replySubject,
        public readonly string $replyBody,
    ) {}

    public function envelope(): Envelope
    {
        $replyAddress = SiteSetting::query()
            ->where('group_name', 'contact')
            ->where('setting_key', 'primary_email')
            ->value('value_json');
        $email = is_array($replyAddress) ? ($replyAddress['value'] ?? null) : null;

        return new Envelope(
            subject: $this->replySubject,
            replyTo: is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)
                ? [new Address($email)]
                : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.enquiry-reply');
    }
}
