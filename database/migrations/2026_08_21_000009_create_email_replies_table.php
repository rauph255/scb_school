<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_replies', function (Blueprint $table): void {
            $table->id();
            $table->string('replyable_type', 190);
            $table->unsignedBigInteger('replyable_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient_email');
            $table->string('subject');
            $table->text('body');
            $table->string('status', 32)->default('queued');
            $table->timestamp('queued_at');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('failure_type', 190)->nullable();
            $table->char('request_id', 36)->nullable();
            $table->timestamps();

            $table->index(['replyable_type', 'replyable_id', 'created_at'], 'email_replies_subject_lookup');
            $table->index(['status', 'queued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_replies');
    }
};
