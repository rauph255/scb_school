<?php

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'uuid',
        'disk',
        'directory',
        'stored_name',
        'original_name',
        'mime_type',
        'extension',
        'size_bytes',
        'width',
        'height',
        'checksum_sha256',
        'alt_text',
        'caption',
        'credit',
        'focal_x',
        'focal_y',
        'visibility',
        'consent_required',
        'consent_confirmed',
        'consent_reference',
        'publication_restricted',
        'restriction_reason',
        'is_protected_asset',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'consent_required' => 'boolean',
            'consent_confirmed' => 'boolean',
            'publication_restricted' => 'boolean',
            'is_protected_asset' => 'boolean',
            'focal_x' => 'decimal:2',
            'focal_y' => 'decimal:2',
        ];
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('visibility', 'public')
            ->where('publication_restricted', false)
            ->where(function (Builder $query): void {
                $query->where('consent_required', false)
                    ->orWhere('consent_confirmed', true);
            });
    }

    public function storagePath(): string
    {
        return $this->directory.'/'.$this->stored_name;
    }

    public function isManagedUpload(): bool
    {
        return str_starts_with($this->directory, 'media/');
    }

    public function usesLaravelStorage(): bool
    {
        return $this->isManagedUpload() || $this->disk !== 'public';
    }

    public function isPubliclyAvailable(): bool
    {
        return ! $this->trashed()
            && $this->visibility === 'public'
            && ! $this->publication_restricted
            && (! $this->consent_required || $this->consent_confirmed);
    }

    public function publicUrl(): ?string
    {
        if (! $this->isPubliclyAvailable() || ! $this->storedFileExists()) {
            return null;
        }

        return route('media.show', [
            'media' => $this->uuid,
            'v' => substr($this->checksum_sha256, 0, 12),
        ], false);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function responsiveUrl(int $width): ?string
    {
        if (! $this->isImage() || ! $this->isPubliclyAvailable()) {
            return null;
        }

        if ($this->width && $width >= $this->width) {
            return $this->publicUrl();
        }

        return route('media.image', [
            'media' => $this->uuid,
            'width' => $width,
            'v' => substr($this->checksum_sha256, 0, 12),
        ], false);
    }

    public function responsiveSrcset(): ?string
    {
        if (! $this->isImage() || ! $this->isPubliclyAvailable()) {
            return null;
        }

        $widths = collect([480, 960, 1600])
            ->filter(fn (int $width): bool => ! $this->width || $width < $this->width)
            ->mapWithKeys(fn (int $width): array => [$width => $this->responsiveUrl($width)]);

        if ($this->width) {
            $widths->put($this->width, $this->publicUrl());
        }

        return $widths
            ->filter()
            ->sortKeys()
            ->map(fn (string $url, int $width): string => $url.' '.$width.'w')
            ->implode(', ');
    }

    public function storedFileExists(): bool
    {
        if ($this->usesLaravelStorage()) {
            return Storage::disk($this->disk)->exists($this->storagePath());
        }

        return $this->disk === 'public' && File::exists(public_path($this->storagePath()));
    }

    public function adminPreviewUrl(): string
    {
        return route('admin.media.preview', $this, false);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariant::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
