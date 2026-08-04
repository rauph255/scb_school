<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{
    use HasPublicationScope;
    use SoftDeletes;

    protected $fillable = ['faq_category_id', 'question', 'answer', 'sort_order', 'status', 'published_at', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FaqCategory::class, 'faq_category_id');
    }
}
