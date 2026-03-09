<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Vehicle extends Model
{
    use HasFactory;

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
        'status',
        'description',
        'image_url',
        'photos'
    ];

    protected $casts = [
        'price_per_day' => 'decimal:2'
    ];
}
