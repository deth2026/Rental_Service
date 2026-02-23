<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingStatusLog extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'booking_id',
        'status',
        'changed_at'
    ];

    protected $casts = [
        'changed_at' => 'datetime'
    ];
}
