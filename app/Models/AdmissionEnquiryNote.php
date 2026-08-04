<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdmissionEnquiryNote extends Model
{
    protected $fillable = ['admission_enquiry_id', 'user_id', 'note', 'is_sensitive'];

    protected function casts(): array
    {
        return ['is_sensitive' => 'boolean'];
    }
}
