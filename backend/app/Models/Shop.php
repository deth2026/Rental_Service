<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class Shop extends Model
{
    use HasFactory;

    private static array $shopColumnCache = [];

    protected $with = ['owner'];

    protected $appends = ['img_url_full', 'qr_url_full'];

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
        'map_url',
        'total_reviews',
        'status',
        'qr_url',
        'qr_code'
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

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function getImgUrlFullAttribute(): ?string
    {
        return $this->resolveUploadedFileUrl($this->img_url ?? '');
    }

    public function getQrUrlAttribute(): ?string
    {
        if (self::hasShopColumn('qr_url')) {
            return $this->attributes['qr_url'] ?? null;
        }
        if (self::hasShopColumn('qr_code')) {
            return $this->attributes['qr_code'] ?? null;
        }
        return null;
    }

    public function setQrUrlAttribute(?string $value): void
    {
        if (self::hasShopColumn('qr_url')) {
            $this->attributes['qr_url'] = $value;
            return;
        }
        if (self::hasShopColumn('qr_code')) {
            $this->attributes['qr_code'] = $value;
        }
    }

    public function getQrUrlFullAttribute(): ?string
    {
        return $this->resolveUploadedFileUrl($this->qr_url ?? '');
    }

    private function resolveUploadedFileUrl(?string $value): ?string
    {
        $clean = trim((string) ($value ?? ''));
        if ($clean === '') {
            return null;
        }

        if (filter_var($clean, FILTER_VALIDATE_URL)) {
            return $clean;
        }

        $normalized = ltrim(str_replace('\\', '/', $clean), '/');
        if (str_starts_with($normalized, 'storage/')) {
            return asset($normalized);
        }

        return asset('storage/' . $normalized);
    }

    private static function hasShopColumn(string $column): bool
    {
        if (!array_key_exists($column, self::$shopColumnCache)) {
            self::$shopColumnCache[$column] = Schema::hasColumn('shops', $column);
        }

        return self::$shopColumnCache[$column];
    }
}
