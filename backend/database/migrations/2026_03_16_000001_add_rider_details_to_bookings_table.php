<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('rider_details')->nullable()->after('total_days');
            $table->decimal('daily_rate', 10, 2)->nullable()->after('rider_details');
            $table->decimal('insurance_fee', 10, 2)->nullable()->after('daily_rate');
            $table->decimal('taxes_fee', 10, 2)->nullable()->after('insurance_fee');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['rider_details', 'daily_rate', 'insurance_fee', 'taxes_fee']);
        });
    }
};