<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // JWT tokens can be long (typically 200-1000+ chars), using text for flexibility
            $table->text('token');
            $table->string('device_id')->nullable(); // For mobile device tracking
            $table->string('device_name')->nullable(); // Device name/identifier
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('is_revoked')->default(false);
            $table->foreignId('replaced_by')->nullable()->after('is_revoked')
                ->constrained('refresh_tokens')->onDelete('set null');
            $table->timestamps();

            // Add unique index on token hash for faster lookups (since text can't be unique directly)
            $table->string('token_hash', 64)->unique()->after('token');
            $table->index(['user_id', 'is_revoked']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
