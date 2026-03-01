<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'shop_id',
        'name',
        'type',
        'brand',
        'model',
        'plate_number',
        'price_per_day',
        'fuel_type',
        'transmission',
        'status',
        'description',
        'image_url',
        'photos'
    ];

    protected $casts = [
        'price_per_day' => 'decimal:2'
    ];
}
