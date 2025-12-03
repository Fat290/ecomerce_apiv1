<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shop_id',
        'category_id',
        'name',
        'description',
        'images',
        'price',
        'stock',
        'status',
        'rating',
        'sold_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $appends = [
        'resolved_variants',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'price' => 'decimal:2',
            'rating' => 'decimal:2',
        ];
    }

    /**
     * Get the shop that owns the product.
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get the category of the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the reviews for the product.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Users who have wishlisted this product.
     */
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function variantOptions()
    {
        return $this->hasMany(ProductVariantOption::class);
    }

    public function getResolvedVariantsAttribute(): array
    {
        $category = $this->relationLoaded('category')
            ? $this->category
            : $this->category()->first();

        if (!$category) {
            return [];
        }

        $categoryVariants = collect($category->aggregatedVariants());
        $overrideCollection = $this->relationLoaded('variantOptions')
            ? collect($this->variantOptions)
            : $this->variantOptions()->get();

        $overrides = $overrideCollection->keyBy('variant_id');

        return $categoryVariants->map(function ($variant) use ($overrides) {
            $data = $variant->toArray();

            if ($overrides->has($variant->id)) {
                $override = $overrides->get($variant->id);
                $data['options'] = $override->options;
                $data['is_required'] = $override->is_required;
            }

            return $data;
        })->values()->all();
    }
}
