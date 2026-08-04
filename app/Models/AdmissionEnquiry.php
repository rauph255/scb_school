<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmissionEnquiry extends Model
{
    use SoftDeletes;

    protected $fillable = ['reference_code', 'guardian_name', 'email', 'telephone', 'intended_level', 'intended_term', 'intended_year', 'preferred_contact_method', 'message', 'consent_confirmed', 'status', 'assigned_to', 'source_ip_hash', 'user_agent_hash', 'responded_at', 'closed_at'];

    protected function casts(): array
    {
        return [
            'consent_confirmed' => 'boolean',
            'responded_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function notes(): HasMany
    {
        return $this->hasMany(AdmissionEnquiryNote::class);
    }
}
