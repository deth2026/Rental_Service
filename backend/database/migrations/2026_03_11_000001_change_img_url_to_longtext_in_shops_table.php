<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the existing column from string to longText
        DB::statement('ALTER TABLE shops MODIFY img_url LONGTEXT NULL');
    }

    public function down(): void
    {
        // Revert back to string
        DB::statement('ALTER TABLE shops MODIFY img_url VARCHAR(255) NULL');
    }
};

