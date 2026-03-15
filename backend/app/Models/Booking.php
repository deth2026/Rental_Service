<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'shop_id',
        'coupon_id',
        'start_date',
        'total_days',
        'total_price',
        'status',
        'deposit_amount',
        'deposit_status'
    ];

    protected $casts = [
        'deposit_amount' => 'decimal:2',
        'start_date' => 'date',
        'total_days' => 'integer',
        'total_price' => 'decimal:2'
    ];

    public function vehicle(): BelongsTo
=======
    public function vehicle(): BelongsTo
>>>>>>> dashboard/admin
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
=======
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
>>>>>>> dashboard/admin
    {
        return $this->belongsTo(Coupon::class);
    }
}


