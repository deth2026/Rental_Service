<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'rider_details',
        'daily_rate',
        'insurance_fee',
        'taxes_fee',
        'total_price',
        'status',
        'deposit_amount',
        'deposit_status'
    ];

    protected $casts = [
        'deposit_amount' => 'decimal:2',
        'start_date' => 'date',
        'total_days' => 'integer',
        'daily_rate' => 'decimal:2',
        'insurance_fee' => 'decimal:2',
        'taxes_fee' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}


