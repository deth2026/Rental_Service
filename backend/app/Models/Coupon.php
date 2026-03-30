<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Shop;

class Coupon extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'shop_id',
        'code',
        'discount_percent',
        'valid_from',
        'valid_until',
        'usage_limit',
        'created_at',
        'discount_amount',
        'minimum_amount',
        'is_active'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'is_active' => 'boolean',
        'minimum_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'shop_id' => 'integer',
        'valid_from' => 'date',
        'valid_until' => 'date'
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
