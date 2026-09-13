<?php

namespace App\Support;

use App\Models\Media;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class MediaFileManager
{
    /** @var array<string, string> */
    private const EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.oasis.opendocument.text' => 'odt',
    ];

    /** @var array<int, int> */
    private const RESPONSIVE_WIDTHS = [480, 960, 1600];

    private const MAX_IMAGE_EDGE = 2560;

    private const MAX_SOURCE_PIXELS = 40_000_000;

    /** @return array<string, mixed> */
    public function store(UploadedFile $upload, string $disk = 'local'): array
    {
        $mimeType = $this->mimeType($upload);
        $directory = 'media/'.now()->format('Y/m');
        $token = (string) Str::uuid();

        if (str_starts_with($mimeType, 'image/')) {
            return $this->storeOptimizedImage($upload, $mimeType, $disk, $directory, $token);
        }

        $extension = self::EXTENSIONS[$mimeType];
        $storedName = $token.'.'.$extension;
        $storagePath = $directory.'/'.$storedName;

        $this->storePath($upload->getRealPath(), $disk, $storagePath);

        return [
            'disk' => $disk,
            'directory' => $directory,
            'stored_name' => $storedName,
            'original_name' => basename($upload->getClientOriginalName()),
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size_bytes' => filesize($upload->getRealPath()),
            'width' => null,
            'height' => null,
            'checksum_sha256' => hash_file('sha256', $upload->getRealPath()),
        ];
    }

    public function mimeType(UploadedFile $upload): string
    {
        $mimeType = File::mimeType($upload->getRealPath());

        if (! is_string($mimeType) || ! array_key_exists($mimeType, self::EXTENSIONS)) {
            throw ValidationException::withMessages([
                'file' => 'Upload a JPEG, PNG, WebP, PDF, Word, Excel, or OpenDocument file.',
            ]);
        }

        if (str_starts_with($mimeType, 'image/')) {
            $dimensions = @getimagesize($upload->getRealPath());

            if ($dimensions === false || ($dimensions['mime'] ?? null) !== $mimeType) {
                throw ValidationException::withMessages([
                    'file' => 'The uploaded image could not be decoded safely.',
                ]);
            }

            if (($dimensions[0] * $dimensions[1]) > self::MAX_SOURCE_PIXELS) {
                throw ValidationException::withMessages([
                    'file' => 'The uploaded image dimensions are too large to process safely.',
                ]);
            }

            if (! function_exists('imagewebp')) {
                throw ValidationException::withMessages([
                    'file' => 'WebP image processing is unavailable on this server.',
                ]);
            }
        }

        return $mimeType;
    }

    /**
     * @param  array<string, mixed>  $stored
     * @return array<int, array<string, mixed>>
     */
    public function variants(array $stored): array
    {
        return $stored['_variants'] ?? [];
    }

    /**
     * @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    public function attributes(array $stored): array
    {
        unset($stored['_variants']);

        return $stored;
    }

    /** @param array<string, mixed> $stored */
    public function deleteStored(array $stored): void
    {
        if (isset($stored['disk'], $stored['directory'], $stored['stored_name'])) {
            $this->delete($stored['disk'], $stored['directory'], $stored['stored_name']);
        }

        foreach ($this->variants($stored) as $variant) {
            if (isset($variant['disk'], $variant['path'])) {
                Storage::disk($variant['disk'])->delete($variant['path']);
            }
        }
    }

    public function copyToDisk(Media $media, string $targetDisk): bool
    {
        if (! $media->isManagedUpload() || $media->disk === $targetDisk) {
            return false;
        }

        $source = Storage::disk($media->disk)->readStream($media->storagePath());

        if ($source === false) {
            throw new RuntimeException('The stored media file could not be read.');
        }

        try {
            if (! Storage::disk($targetDisk)->put($media->storagePath(), $source)) {
                throw new RuntimeException('The stored media file could not be moved.');
            }
        } finally {
            fclose($source);
        }

        return true;
    }

    public function delete(string $disk, string $directory, string $storedName): void
    {
        Storage::disk($disk)->delete($directory.'/'.$storedName);
    }

    /** @return array<string, mixed> */
    private function storeOptimizedImage(
        UploadedFile $upload,
        string $mimeType,
        string $disk,
        string $directory,
        string $token,
    ): array {
        $source = $this->decodedImage($upload->getRealPath(), $mimeType);
        $source = $this->applyOrientation($source, $upload->getRealPath(), $mimeType);
        [$masterWidth, $masterHeight] = $this->fitDimensions(imagesx($source), imagesy($source), self::MAX_IMAGE_EDGE);
        $master = $this->resizedImage($source, $masterWidth, $masterHeight);
        $storedName = $token.'.webp';
        $storagePath = $directory.'/'.$storedName;
        $temporaryPaths = [];
        $storedPaths = [];

        try {
            $masterPath = $this->temporaryPath('webp');
            $temporaryPaths[] = $masterPath;
            $this->writeWebp($master, $masterPath, 82);
            $this->storePath($masterPath, $disk, $storagePath);
            $storedPaths[] = $storagePath;

            $variants = [];

            foreach (self::RESPONSIVE_WIDTHS as $width) {
                if ($masterWidth <= $width) {
                    continue;
                }

                $height = max(1, (int) round($masterHeight * ($width / $masterWidth)));
                $variantImage = $this->resizedImage($master, $width, $height);
                $variantPath = $this->temporaryPath('webp');
                $temporaryPaths[] = $variantPath;

                try {
                    $this->writeWebp($variantImage, $variantPath, 78);
                } finally {
                    imagedestroy($variantImage);
                }

                $storedVariantPath = $directory.'/variants/'.$token.'-'.$width.'.webp';
                $this->storePath($variantPath, $disk, $storedVariantPath);
                $storedPaths[] = $storedVariantPath;
                $variants[] = [
                    'variant_key' => 'responsive-'.$width,
                    'disk' => $disk,
                    'path' => $storedVariantPath,
                    'mime_type' => 'image/webp',
                    'width' => $width,
                    'height' => $height,
                    'size_bytes' => filesize($variantPath),
                    'checksum_sha256' => hash_file('sha256', $variantPath),
                ];
            }

            return [
                'disk' => $disk,
                'directory' => $directory,
                'stored_name' => $storedName,
                'original_name' => basename($upload->getClientOriginalName()),
                'mime_type' => 'image/webp',
                'extension' => 'webp',
                'size_bytes' => filesize($masterPath),
                'width' => $masterWidth,
                'height' => $masterHeight,
                'checksum_sha256' => hash_file('sha256', $masterPath),
                '_variants' => $variants,
            ];
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk($disk)->delete($path);
            }

            throw $exception;
        } finally {
            imagedestroy($source);
            imagedestroy($master);

            foreach ($temporaryPaths as $path) {
                File::delete($path);
            }
        }
    }

    private function decodedImage(string $sourcePath, string $mimeType): GdImage
    {
        $image = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
        };

        if (! $image instanceof GdImage) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded image could not be decoded safely.',
            ]);
        }

        return $image;
    }

    private function applyOrientation(GdImage $image, string $sourcePath, string $mimeType): GdImage
    {
        if ($mimeType !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($sourcePath);
        $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;

        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, $orientation === 4 ? IMG_FLIP_VERTICAL : IMG_FLIP_HORIZONTAL);
        }

        $angle = match ($orientation) {
            3, 4 => 180,
            5, 6 => -90,
            7, 8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        if (! $rotated instanceof GdImage) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    /** @return array{0: int, 1: int} */
    private function fitDimensions(int $width, int $height, int $maxEdge): array
    {
        $scale = min(1, $maxEdge / max($width, $height));

        return [
            max(1, (int) round($width * $scale)),
            max(1, (int) round($height * $scale)),
        ];
    }

    private function resizedImage(GdImage $source, int $width, int $height): GdImage
    {
        $target = imagecreatetruecolor($width, $height);

        if (! $target instanceof GdImage) {
            throw new RuntimeException('An optimized image buffer could not be created.');
        }

        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefilledrectangle($target, 0, 0, $width, $height, $transparent);

        if (! imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source))) {
            imagedestroy($target);

            throw new RuntimeException('The uploaded image could not be resized.');
        }

        return $target;
    }

    private function writeWebp(GdImage $image, string $path, int $quality): void
    {
        if (! imagewebp($image, $path, $quality)) {
            throw new RuntimeException('The optimized WebP image could not be written.');
        }
    }

    private function storePath(string $sourcePath, string $disk, string $storagePath): void
    {
        $stream = fopen($sourcePath, 'rb');

        if ($stream === false) {
            throw new RuntimeException('The uploaded media could not be read.');
        }

        try {
            if (! Storage::disk($disk)->put($storagePath, $stream)) {
                throw new RuntimeException('The uploaded media could not be stored.');
            }
        } finally {
            fclose($stream);
        }
    }

    private function temporaryPath(string $suffix): string
    {
        $path = tempnam(storage_path('app'), 'scb-media-');

        if ($path === false) {
            throw new RuntimeException('A temporary media file could not be created.');
        }

        $suffixedPath = $path.'.'.$suffix;

        if (! File::move($path, $suffixedPath)) {
            File::delete($path);

            throw new RuntimeException('A temporary media file could not be prepared.');
        }

        return $suffixedPath;
    }
}
