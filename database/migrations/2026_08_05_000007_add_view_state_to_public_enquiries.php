<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_enquiries', function (Blueprint $table): void {
            $table->timestamp('first_viewed_at')->nullable()->after('status')->index();
            $table->foreignId('viewed_by')->nullable()->after('first_viewed_at')->constrained('users')->nullOnDelete();
        });

        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->timestamp('first_viewed_at')->nullable()->after('status')->index();
            $table->foreignId('viewed_by')->nullable()->after('first_viewed_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('viewed_by');
            $table->dropColumn('first_viewed_at');
        });

        Schema::table('admission_enquiries', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('viewed_by');
            $table->dropColumn('first_viewed_at');
        });
    }
};
