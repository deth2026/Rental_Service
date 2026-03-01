<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyPoint extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'points',
        'updated_at'
    ];

    protected $casts = [
        'points' => 'integer',
        'updated_at' => 'datetime'
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
