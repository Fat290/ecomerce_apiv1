<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'buyer_id',
        'shop_id',
        'items',
        'total_amount',
        'shipping_fee',
        'payment_method',
        'status',
        'shipping_address',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'items' => 'array',
            'total_amount' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'shipping_address' => 'array',
        ];
    }

    /**
     * Get the buyer user.
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the shop that receives the order.
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get the transactions for the order.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
