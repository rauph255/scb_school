<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('disk', 64);
            $table->string('directory');
            $table->string('stored_name');
            $table->string('original_name');
            $table->string('mime_type', 150);
            $table->string('extension', 20);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->char('checksum_sha256', 64);
            $table->string('alt_text', 500)->nullable();
            $table->text('caption')->nullable();
            $table->string('credit')->nullable();
            $table->decimal('focal_x', 5, 2)->nullable();
            $table->decimal('focal_y', 5, 2)->nullable();
            $table->string('visibility', 32)->default('private');
            $table->boolean('consent_required')->default(false);
            $table->boolean('consent_confirmed')->default(false);
            $table->string('consent_reference')->nullable();
            $table->boolean('publication_restricted')->default(false);
            $table->text('restriction_reason')->nullable();
            $table->boolean('is_protected_asset')->default(false);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['disk', 'directory', 'stored_name']);
            $table->index(['visibility', 'publication_restricted', 'deleted_at']);
            $table->index(['consent_required', 'consent_confirmed']);
        });

        Schema::create('media_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('variant_key', 100);
            $table->string('disk', 64);
            $table->string('path', 1024);
            $table->string('mime_type', 150);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('size_bytes');
            $table->char('checksum_sha256', 64);
            $table->timestamps();

            $table->unique(['media_id', 'variant_key']);
        });

        Schema::create('media_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('usable_type', 190);
            $table->unsignedBigInteger('usable_id');
            $table->string('field_name', 100);
            $table->timestamp('created_at')->nullable();

            $table->unique(['media_id', 'usable_type', 'usable_id', 'field_name']);
            $table->index(['usable_type', 'usable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_usages');
        Schema::dropIfExists('media_variants');
        Schema::dropIfExists('media');
    }
};
