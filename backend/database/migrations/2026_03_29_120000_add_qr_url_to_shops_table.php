<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('shops') || Schema::hasColumn('shops', 'qr_url')) {
            return;
        }

        Schema::table('shops', function (Blueprint $table) {
            $table->string('qr_url')->nullable()->after('img_url');
        });

        if (Schema::hasColumn('shops', 'qr_code')) {
            DB::table('shops')
                ->whereNull('qr_url')
                ->whereNotNull('qr_code')
                ->update(['qr_url' => DB::raw('qr_code')]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('shops') || !Schema::hasColumn('shops', 'qr_url')) {
            return;
        }

        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('qr_url');
        });
    }
};
