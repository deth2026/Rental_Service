<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamageReport extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'booking_id',
        'description',
        'image_url',
        'repair_cost',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'repair_cost' => 'decimal:2'
    ];
}
