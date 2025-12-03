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
        Schema::create('business_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('shops', function (Blueprint $table) {
            if (Schema::hasColumn('shops', 'business_type')) {
                $table->dropColumn('business_type');
            }

            $table->foreignId('business_type_id')
                ->nullable()
                ->after('description')
                ->constrained('business_types')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            if (Schema::hasColumn('shops', 'business_type_id')) {
                $table->dropConstrainedForeignId('business_type_id');
            }

            if (!Schema::hasColumn('shops', 'business_type')) {
                $table->string('business_type')->nullable()->after('description');
            }
        });

        Schema::dropIfExists('business_types');
    }
};
