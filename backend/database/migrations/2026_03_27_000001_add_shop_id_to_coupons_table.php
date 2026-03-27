<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('coupons') || Schema::hasColumn('coupons', 'shop_id')) {
            return;
        }

        Schema::table('coupons', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('id')->constrained('shops')->nullOnDelete();
            $table->index('shop_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('coupons') || !Schema::hasColumn('coupons', 'shop_id')) {
            return;
        }

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->dropIndex(['shop_id']);
            $table->dropColumn('shop_id');
        });
    }
};
