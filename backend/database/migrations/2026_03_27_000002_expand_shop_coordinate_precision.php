<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE shops MODIFY latitude DECIMAL(11,7) NULL');
        DB::statement('ALTER TABLE shops MODIFY longitude DECIMAL(11,7) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE shops MODIFY latitude DECIMAL(10,7) NULL');
        DB::statement('ALTER TABLE shops MODIFY longitude DECIMAL(10,7) NULL');
    }
};
