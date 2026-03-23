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

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function latestStatusLog()
    {
        return $this->hasOne(BookingStatusLog::class)->latestOfMany('changed_at');
    }

    public function bookingStatusLogs()
    {
        return $this->hasMany(BookingStatusLog::class)->orderBy('changed_at', 'desc');
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }
}
