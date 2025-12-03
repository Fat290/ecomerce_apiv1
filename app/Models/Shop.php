<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'owner_id',
        'name',
        'logo',
        'banner',
        'description',
        'business_type_id',
        'join_date',
        'address',
        'rating',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'join_date' => 'date',
        ];
    }

    /**
     * Get the owner of the shop.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the products in the shop.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Business type metadata.
     */
    public function businessType()
    {
        return $this->belongsTo(BusinessType::class);
    }

    /**
     * Get the vouchers created by the shop.
     */
    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    /**
     * Get the orders received by the shop.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
