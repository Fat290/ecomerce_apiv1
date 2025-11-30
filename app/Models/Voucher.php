<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'min_order_value',
        'shop_id',
        'start_date',
        'end_date',
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
            'discount_value' => 'decimal:2',
            'min_order_value' => 'decimal:2',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    /**
     * Get the shop that created the voucher.
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
