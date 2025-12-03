<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'image',
        'parent_id',
    ];

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get the products in this category.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Variant definitions belonging to this category.
     */
    public function variants()
    {
        return $this->hasMany(Variant::class)->orderBy('position');
    }

    /**
     * Collect variants from root ancestors down to this category.
     *
     * @return array<int, \App\Models\Variant>
     */
    public function aggregatedVariants(): array
    {
        $variants = [];
        $this->appendVariantsRecursive($variants);
        return $variants;
    }

    /**
     * Recursively append variants from ancestors to the provided array.
     */
    protected function appendVariantsRecursive(array &$variants): void
    {
        $this->loadMissing(['variants', 'parent']);

        if ($this->parent) {
            $this->parent->appendVariantsRecursive($variants);
        }

        foreach ($this->variants as $variant) {
            $variants[] = $variant;
        }
    }
}
