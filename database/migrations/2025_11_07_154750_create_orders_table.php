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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->json('items'); // Array of objects: productId, qty, price
            $table->decimal('total_amount', 10, 2);
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->string('payment_method');
            $table->enum('status', ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'])->default('pending');
            $table->json('shipping_address'); // Shipping address object
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
