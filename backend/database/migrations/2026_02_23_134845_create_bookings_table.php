<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->date('start_date');
            $table->unsignedInteger('total_days');
            $table->decimal('total_price', 10, 2);
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->string('deposit_status')->default('unpaid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
