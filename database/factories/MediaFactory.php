<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Media> */
class MediaFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->slug().'.jpg';

        return [
            'uuid' => (string) Str::uuid(),
            'disk' => 'public',
            'directory' => 'assets/images/school',
            'stored_name' => $name,
            'original_name' => $name,
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size_bytes' => fake()->numberBetween(20000, 500000),
            'checksum_sha256' => hash('sha256', $name),
            'visibility' => 'public',
            'consent_required' => false,
            'consent_confirmed' => false,
            'publication_restricted' => false,
        ];
    }
}
