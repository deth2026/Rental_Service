<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'name',
        'status', 
        'shop_id',
        'latitude',
        'longitude',
        'created_at'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'shop_id' => 'integer',
        'created_at' => 'datetime'
    ];
}
