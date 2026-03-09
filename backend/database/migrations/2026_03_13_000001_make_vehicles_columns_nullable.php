<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make vehicle columns nullable to allow empty/optional values
        DB::statement('ALTER TABLE vehicles MODIFY type VARCHAR(255) NULL');
        DB::statement('ALTER TABLE vehicles MODIFY brand VARCHAR(255) NULL');
        DB::statement('ALTER TABLE vehicles MODIFY model VARCHAR(255) NULL');
        DB::statement('ALTER TABLE vehicles MODIFY year SMALLINT(5) UNSIGNED NULL');
        DB::statement('ALTER TABLE vehicles MODIFY price_per_day DECIMAL(10,2) NULL');
        DB::statement('ALTER TABLE vehicles MODIFY status VARCHAR(255) NULL');
    }

    public function down(): void
    {
        // Revert back to NOT NULL
        DB::statement('ALTER TABLE vehicles MODIFY type VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE vehicles MODIFY brand VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE vehicles MODIFY model VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE vehicles MODIFY year SMALLINT(5) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE vehicles MODIFY price_per_day DECIMAL(10,2) NOT NULL');
        DB::statement('ALTER TABLE vehicles MODIFY status VARCHAR(255) NOT NULL DEFAULT \'available\'');
    }
};
