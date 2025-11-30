<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'buyer_id',
        'rating',
        'comment',
        'reply',
    ];

    /**
     * Get the product being reviewed.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the buyer who wrote the review.
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}
