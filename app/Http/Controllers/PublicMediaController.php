<?php

namespace App\Http\Controllers;

use App\Models\Download;
use App\Models\Media;
use App\Models\MediaVariant;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PublicMediaController extends Controller
{
    public function show(Media $media): Response
    {
        abort_unless($media->isPubliclyAvailable() && $media->storedFileExists(), 404);

        return $this->fileResponse($media, inline: true);
    }

    public function image(Media $media, int $width): Response
    {
        abort_unless(in_array($width, [480, 960, 1600], true), 404);
        abort_unless($media->isImage() && $media->isPubliclyAvailable() && $media->storedFileExists(), 404);

        $variant = $media->variants()
            ->where('variant_key', 'responsive-'.$width)
            ->first();

        if (! $variant || ! Storage::disk($variant->disk)->exists($variant->path)) {
            return $this->fileResponse($media, inline: true);
        }

        return $this->variantResponse($media, $variant);
    }

    public function download(Download $download): Response
    {
        abort_unless(
            Download::query()->published()->whereKey($download->id)->exists(),
            404,
        );

        $media = $download->media;

        abort_unless(
            $media
                && $this->isDocumentMime($media->mime_type)
                && $media->isPubliclyAvailable()
                && $media->storedFileExists(),
            404,
        );

        Download::query()->whereKey($download->id)->increment('download_count');

        return $this->fileResponse($media, inline: false);
    }

    private function fileResponse(Media $media, bool $inline): Response
    {
        $headers = [
            'Cache-Control' => $media->isImage()
                ? 'public, max-age=31536000, immutable'
                : 'private, no-store',
            'Content-Type' => $media->mime_type,
            'ETag' => '"'.$media->checksum_sha256.'"',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($media->usesLaravelStorage()) {
            return Storage::disk($media->disk)->response(
                $media->storagePath(),
                $media->original_name,
                $headers,
                $inline ? 'inline' : 'attachment',
            );
        }

        $path = $this->legacyPublicPath($media);

        return $inline
            ? response()->file($path, $headers)
            : response()->download($path, $media->original_name, $headers);
    }

    private function variantResponse(Media $media, MediaVariant $variant): Response
    {
        return Storage::disk($variant->disk)->response(
            $variant->path,
            pathinfo($media->original_name, PATHINFO_FILENAME).'-'.$variant->width.'.webp',
            [
                'Cache-Control' => 'public, max-age=31536000, immutable',
                'Content-Type' => $variant->mime_type,
                'ETag' => '"'.$variant->checksum_sha256.'"',
                'Vary' => 'Accept',
                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline',
        );
    }

    private function isDocumentMime(string $mimeType): bool
    {
        return in_array($mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.oasis.opendocument.text',
        ], true);
    }

    private function legacyPublicPath(Media $media): string
    {
        $publicRoot = realpath(public_path());
        $path = realpath(public_path($media->storagePath()));

        abort_unless(
            $publicRoot !== false
                && $path !== false
                && str_starts_with($path, $publicRoot.DIRECTORY_SEPARATOR),
            404,
        );

        return $path;
    }
}
