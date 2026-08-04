<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code', 40)->unique();
            $table->string('guardian_name');
            $table->string('email');
            $table->string('telephone', 40);
            $table->string('intended_level', 150)->nullable();
            $table->string('intended_term', 100)->nullable();
            $table->unsignedSmallInteger('intended_year')->nullable();
            $table->string('preferred_contact_method', 32)->nullable();
            $table->text('message')->nullable();
            $table->boolean('consent_confirmed');
            $table->string('status', 32)->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->char('source_ip_hash', 64)->nullable();
            $table->char('user_agent_hash', 64)->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['assigned_to', 'status', 'created_at']);
            $table->index('email');
            $table->index('telephone');
            $table->index('intended_year');
        });

        Schema::create('admission_enquiry_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_enquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note');
            $table->boolean('is_sensitive')->default(true);
            $table->timestamps();

            $table->index(['admission_enquiry_id', 'created_at']);
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code', 40)->unique();
            $table->string('full_name');
            $table->string('email');
            $table->string('telephone', 40)->nullable();
            $table->string('subject');
            $table->text('message');
            $table->boolean('consent_confirmed');
            $table->string('status', 32)->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->char('source_ip_hash', 64)->nullable();
            $table->char('user_agent_hash', 64)->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['assigned_to', 'status', 'created_at']);
            $table->index('email');
        });

        Schema::create('contact_message_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note');
            $table->boolean('is_sensitive')->default(true);
            $table->timestamps();

            $table->index(['contact_message_id', 'created_at']);
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('source_path', 700)->unique();
            $table->string('target_url', 2048);
            $table->unsignedSmallInteger('http_status')->default(301);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('hit_count')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'source_path'], 'redirects_active_source');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->string('subject_type', 190)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description', 500)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->char('request_id', 36)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['actor_id', 'created_at']);
            $table->index(['subject_type', 'subject_id', 'created_at'], 'audit_logs_subject_lookup');
            $table->index(['action', 'created_at']);
            $table->index('request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('contact_message_notes');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('admission_enquiry_notes');
        Schema::dropIfExists('admission_enquiries');
    }
};
