<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
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
        'valid_from' => 'date',
        'valid_until' => 'date'
    ];
}
