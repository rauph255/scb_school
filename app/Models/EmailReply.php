<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmailReply extends Model
{
    protected $fillable = [
        'replyable_type',
        'replyable_id',
        'user_id',
        'recipient_email',
        'subject',
        'body',
        'status',
        'queued_at',
        'sent_at',
        'failed_at',
        'failure_type',
        'request_id',
    ];

    protected function casts(): array
    {
        return [
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function replyable(): MorphTo
    {
        return $this->morphTo();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function recipientEmail(): Attribute
    {
        return Attribute::set(fn (mixed $value): string => mb_strtolower(trim((string) $value)));
    }
}
