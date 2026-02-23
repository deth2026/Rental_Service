<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_id',
        'booking_id',
        'subject',
        'message',
        'rating',
        'stats',
        'admin_reply'
    ];

    protected $casts = [
        'rating' => 'integer'
    ];
}
