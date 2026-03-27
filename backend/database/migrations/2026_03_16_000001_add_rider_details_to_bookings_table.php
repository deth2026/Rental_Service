<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('bookings', 'rider_details')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('rider_details')->nullable()->after('total_days');
            });
        }

        if (!Schema::hasColumn('bookings', 'daily_rate')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->decimal('daily_rate', 10, 2)->nullable()->after('rider_details');
            });
        }

        if (!Schema::hasColumn('bookings', 'insurance_fee')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->decimal('insurance_fee', 10, 2)->nullable()->after('daily_rate');
            });
        }

        if (!Schema::hasColumn('bookings', 'taxes_fee')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->decimal('taxes_fee', 10, 2)->nullable()->after('insurance_fee');
            });
        }
    }

    public function down(): void
    {
        $columnsToDrop = array_values(array_filter([
            Schema::hasColumn('bookings', 'rider_details') ? 'rider_details' : null,
            Schema::hasColumn('bookings', 'daily_rate') ? 'daily_rate' : null,
            Schema::hasColumn('bookings', 'insurance_fee') ? 'insurance_fee' : null,
            Schema::hasColumn('bookings', 'taxes_fee') ? 'taxes_fee' : null,
        ]));

        if ($columnsToDrop !== []) {
            Schema::table('bookings', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }
};
