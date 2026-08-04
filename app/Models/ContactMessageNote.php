<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessageNote extends Model
{
    protected $fillable = ['contact_message_id', 'user_id', 'note', 'is_sensitive'];

    protected function casts(): array
    {
        return ['is_sensitive' => 'boolean'];
    }
}
