<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('bookings', 'shop_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->unsignedBigInteger('shop_id')->nullable()->after('vehicle_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bookings', 'shop_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('shop_id');
            });
        }
    }
};
