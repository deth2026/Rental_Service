<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'city_id',
        'name',
        'description',
        'address',
        'phone',
        'img_url',
        'latitude',
        'longitude',
        'total_reviews',
        'status'
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'total_reviews' => 'integer'
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
