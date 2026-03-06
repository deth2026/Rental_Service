<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role',
        'is_verified',
        'profile_picture',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password'    => 'hashed',
        'is_verified' => 'boolean',
    ];
}
