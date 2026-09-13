<?php

namespace App\Models;

use App\Models\Concerns\NormalizesEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmissionEnquiry extends Model
{
    use NormalizesEmail, SoftDeletes;

    protected $fillable = ['reference_code', 'guardian_name', 'email', 'telephone', 'intended_level', 'intended_term', 'intended_year', 'preferred_contact_method', 'message', 'consent_confirmed', 'status', 'first_viewed_at', 'viewed_by', 'assigned_to', 'source_ip_hash', 'user_agent_hash', 'responded_at', 'closed_at'];

    protected function casts(): array
    {
        return [
            'consent_confirmed' => 'boolean',
            'first_viewed_at' => 'datetime',
            'responded_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function notes(): HasMany
    {
        return $this->hasMany(AdmissionEnquiryNote::class);
    }

    public function emailReplies(): MorphMany
    {
        return $this->morphMany(EmailReply::class, 'replyable');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function viewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewed_by');
    }
}
