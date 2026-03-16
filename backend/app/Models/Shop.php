<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Shop extends Model
{
    use HasFactory;

    protected $appends = ['img_url_full'];

    protected $fillable = [
        'owner_id',
        'city_id',
        'name',
        'description',
        'address',
        'location',
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

    /**
     * Get the full image URL for display
     * This accessor is automatically included in API responses
     */
    public function getImgUrlFullAttribute(): ?string
    {
        $path = $this->img_url;
        if (!$path) {
            return null;
        }

        // If it's already a full URL, return as-is
        if (Str::startsWith($path, ['http://', 'https://', 'data:'])) {
            return $path;
        }

        // Clean the path and prepend storage URL
        $cleanPath = ltrim(str_replace('\\', '/', (string) $path), '/');
        return asset('storage/' . $cleanPath);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
