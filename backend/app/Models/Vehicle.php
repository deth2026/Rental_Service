<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'type',
        'brand',
        'model',
        'year',
        'price_per_day',
        'fuel_type',
        'transmission',
        'seats',
        'status',
        'image_url'
    ];

    protected $casts = [
        'price_per_day' => 'decimal:2',
        'year' => 'integer'
    ];
}
