<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vehicles', 'total_vehicles')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->unsignedInteger('total_vehicles')->default(1)->after('transmission');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vehicles', 'total_vehicles')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->dropColumn('total_vehicles');
            });
        }
    }
};
