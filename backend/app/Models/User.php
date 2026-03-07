<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role',
        'is_verified',
        'img_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'avatar_url',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_verified' => 'boolean',
    ];

    public function getAvatarUrlAttribute(): ?string
    {
        $path = $this->img_url;
        if (!$path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalized = ltrim($path, '/');

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
}
