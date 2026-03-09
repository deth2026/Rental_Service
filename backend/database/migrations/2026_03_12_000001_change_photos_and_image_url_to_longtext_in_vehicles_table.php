<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the existing columns from string to longText
        DB::statement('ALTER TABLE vehicles MODIFY photos LONGTEXT NULL');
        DB::statement('ALTER TABLE vehicles MODIFY image_url LONGTEXT NULL');
    }

    public function down(): void
    {
        // Revert back to string
        DB::statement('ALTER TABLE vehicles MODIFY photos VARCHAR(255) NULL');
        DB::statement('ALTER TABLE vehicles MODIFY image_url VARCHAR(255) NULL');
    }
};

