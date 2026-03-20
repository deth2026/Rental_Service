<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationRecord extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'role',
        'shop_id',
        'title',
        'message',
        'type',
        'related_id',
        'related_type',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }
}
