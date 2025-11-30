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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('discount_type', ['percent', 'amount']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('min_order_value', 10, 2)->default(0);
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('status', ['active', 'expired', 'disabled'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
