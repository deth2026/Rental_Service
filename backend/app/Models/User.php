<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public static function hasLastLoginColumn(): bool
    {
        static $hasLastLoginColumn = null;

        if ($hasLastLoginColumn === null) {
            $hasLastLoginColumn = Schema::hasColumn((new static())->getTable(), 'last_login');
        }

        return $hasLastLoginColumn;
    }

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

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role',
        'is_verified',
        'img_url',
        'profile_picture',
        'job_title',
        'bio',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'avatar_url',
    ];

    protected $casts = [
        'password'    => 'hashed',
        'is_verified' => 'boolean',
    ];

    public function getAvatarUrlAttribute(): ?string
    {
        $path = $this->profile_picture ?: $this->img_url;
        if (!$path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalized = ltrim(str_replace('\\', '/', (string) $path), '/');

        if (Str::startsWith($normalized, 'storage/')) {
            return asset($normalized);
        }

        if (Storage::disk('public')->exists($normalized)) {
            return asset('storage/' . $normalized);
        }

        if (Storage::disk('public')->exists('avatars/' . $normalized)) {
            return asset('storage/avatars/' . $normalized);
        }

        return asset($normalized);
    }

    /**
     * Get the shop that owns this user (if user is a shop owner)
     */
    public function shop()
    {
        return $this->hasOne(Shop::class, 'owner_id');
    }
}
