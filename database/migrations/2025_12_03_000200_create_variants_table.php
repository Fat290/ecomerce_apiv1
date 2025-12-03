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
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'image')) {
                $table->string('image')->nullable()->after('name');
            }
        });

        if (Schema::hasTable('variants')) {
            return;
        }

        if (Schema::hasTable('category_variants')) {
            Schema::rename('category_variants', 'variants');

            Schema::table('variants', function (Blueprint $table) {
                if (!Schema::hasColumn('variants', 'options')) {
                    $table->json('options')->nullable();
                }

                if (!Schema::hasColumn('variants', 'is_required')) {
                    $table->boolean('is_required')->default(false);
                }

                if (!Schema::hasColumn('variants', 'position')) {
                    $table->unsignedInteger('position')->default(0);
                }

                if (!Schema::hasColumn('variants', 'created_at')) {
                    $table->timestamps();
                }

                $table->unique(['category_id', 'name']);
            });

            return;
        }

        if (Schema::hasTable('variant')) {
            $this->ensureVariantColumns();
            return;
        }

        if (Schema::hasTable('category_variants')) {
            Schema::rename('category_variants', 'variant');
            $this->ensureVariantColumns();
            return;
        }

        if (Schema::hasTable('variants')) {
            Schema::rename('variants', 'variant');
            $this->ensureVariantColumns();
            return;
        }

        Schema::create('variant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('name');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['category_id', 'name'], 'variant_category_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variant');

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'image')) {
                $table->dropColumn('image');
            }
        });
    }

    /**
     * Ensure the single `variant` table has all expected columns/indexes.
     */
    protected function ensureVariantColumns(): void
    {
        Schema::table('variant', function (Blueprint $table) {
            if (!Schema::hasColumn('variant', 'category_id')) {
                $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            }

            if (!Schema::hasColumn('variant', 'name')) {
                $table->string('name');
            }

            if (!Schema::hasColumn('variant', 'options')) {
                $table->json('options')->nullable();
            }

            if (!Schema::hasColumn('variant', 'is_required')) {
                $table->boolean('is_required')->default(false);
            }

            if (!Schema::hasColumn('variant', 'position')) {
                $table->unsignedInteger('position')->default(0);
            }

            if (
                !Schema::hasColumn('variant', 'created_at')
                && !Schema::hasColumn('variant', 'updated_at')
            ) {
                $table->timestamps();
            }
        });
    }
};
