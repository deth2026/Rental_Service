<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            if (!Schema::hasColumn('shops', 'img_url')) {
                $table->string('img_url')->nullable()->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            if (Schema::hasColumn('shops', 'img_url')) {
                $table->dropColumn('img_url');
            }
        });
    }
};
