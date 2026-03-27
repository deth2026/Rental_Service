<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\Rating;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Vehicle extends Model
{
    use HasFactory;

    protected $appends = ['image_url_full', 'photo_urls', 'vehicle_name', 'is_available'];

    public function getCreatedAtColumn()
    {
        static $createdColumn = null;
        if ($createdColumn === null) {
            $columns = Schema::getColumnListing($this->getTable());
            $createdColumn = in_array('created_at', $columns, true) ? 'created_at' : 'create_at';
        }

        return $createdColumn;
    }

    public function getUpdatedAtColumn()
    {
        static $updatedColumn = null;
        if ($updatedColumn === null) {
            $columns = Schema::getColumnListing($this->getTable());
            $updatedColumn = in_array('updated_at', $columns, true) ? 'updated_at' : 'update_at';
        }

        return $updatedColumn;
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function ratings(): HasManyThrough
    {
        return $this->hasManyThrough(
            Rating::class,
            Booking::class,
            'vehicle_id', // Foreign key on bookings table
            'booking_id', // Foreign key on ratings table
            'id', // Local key on vehicles table
            'id' // Intermediate key on bookings table
        );
    }

    /**
     * Direct ratings relationship (since ratings table now has vehicle_id)
     */
    public function directRatings(): HasMany
    {
        return $this->hasMany(Rating::class, 'vehicle_id');
    }

    /**
     * Get the full image URL for display
     * This accessor is automatically included in API responses
     */
    public function getImageUrlFullAttribute(): ?string
    {
        $path = $this->image_url;
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

    /**
     * Get the full photo URLs array for display
     */
    public function getPhotoUrlsAttribute(): array
    {
        $photos = $this->photos;
        
        if (is_string($photos)) {
            $photos = json_decode($photos, true);
        }
        
        if (!is_array($photos) || empty($photos)) {
            return [];
        }

        $urls = [];
        foreach ($photos as $photo) {
            if (!$photo) continue;
            
            // If it's already a full URL, keep it
            if (Str::startsWith($photo, ['http://', 'https://', 'data:'])) {
                $urls[] = $photo;
            } else {
                // Clean the path and prepend storage URL
                $cleanPath = ltrim(str_replace('\\', '/', (string) $photo), '/');
                $urls[] = asset('storage/' . $cleanPath);
            }
        }

        return $urls;
    }

    /**
     * Get the display name for the vehicle
     * Uses custom name first, falls back to brand + model
     */
    public function getVehicleNameAttribute(): string
    {
        $name = trim((string) ($this->name ?? ''));
        if ($name !== '') {
            return $name;
        }
        
        $brandModel = trim(implode(' ', array_filter([
            $this->brand ?? '',
            $this->model ?? '',
        ])));
        
        return $brandModel ?: 'Unnamed Vehicle';
    }

    /**
     * Check if vehicle is available for booking
     * A vehicle is unavailable if it has only 1 total_vehicles and has an active booking
     */
    public function getIsAvailableAttribute(): bool
    {
        $totalVehicles = (int) ($this->total_vehicles ?? 1);
        
        // If more than 1 vehicle, always available
        if ($totalVehicles > 1) {
            return true;
        }
        
        // If only 1 vehicle, check for active bookings
        $activeStatuses = ['pending', 'confirmed', 'rented', 'completed'];
        $hasActiveBooking = Booking::where('vehicle_id', $this->id)
            ->whereIn('status', $activeStatuses)
            ->exists();
        
        return !$hasActiveBooking;
    }
    protected $fillable = [
        'shop_id',
        'name',
        'type',
        'brand',
        'model',
        'plate_number',
        'year',
        'price_per_day',
        'fuel_type',
        'transmission',
        'total_vehicles',
        'seats',
        'rider_details',
        'insurance_fee',
        'taxes_fee',
        'status',
        'description',
        'image_url',
        'photos'
    ];

    protected $casts = [
        'price_per_day' => 'decimal:2',
        'total_vehicles' => 'integer',
        'insurance_fee' => 'decimal:2',
        'taxes_fee' => 'decimal:2'
    ];
}
