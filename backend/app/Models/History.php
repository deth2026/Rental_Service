<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'shop_id',
        'booking_id',
        'rating',
        'comment',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'rating' => 'integer'
    ];
}
