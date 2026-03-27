<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Bookings: 80% of slow queries hit these
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['shop_id', 'status']);
            $table->index(['shop_id', 'created_at']);
            $table->index('user_id');
            $table->index('vehicle_id');
        });

        // Shops: owner queries
        Schema::table('shops', function (Blueprint $table) {
            $table->index('owner_id');
            $table->index('status');
        });

        // Feedback for dashboard
        Schema::table('feedback', function (Blueprint $table) {
            $table->index('shop_id');
            $table->index('created_at');
        });

        // Vehicle ratings summary
        // Skip ratings if needed - focus on core tables
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['shop_id', 'status']);
            $table->dropIndex(['shop_id', 'created_at']);
            $table->dropIndex('user_id');
            $table->dropIndex('vehicle_id');
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->dropIndex('owner_id');
            $table->dropIndex('status');
        });

        Schema::table('feedback', function (Blueprint $table) {
            $table->dropIndex('shop_id');
            $table->dropIndex('created_at');
        });
    }
};
