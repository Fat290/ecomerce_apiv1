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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['order_update', 'order_placed', 'chat_message', 'promotion', 'product_review', 'shop_review', 'system'])->default('system');
            $table->json('data')->nullable(); // Store additional data like order_id, chat_id, etc.
            $table->string('action_url')->nullable(); // URL to navigate when notification is clicked
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
