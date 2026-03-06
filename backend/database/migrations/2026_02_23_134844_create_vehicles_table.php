<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->string('type', 100);
            $table->string('brand', 100);
            $table->string('model', 120);
            $table->unsignedSmallInteger('year');
            $table->decimal('price_per_day', 10, 2);
            $table->string('fuel_type', 40);
            $table->string('transmission', 40);
            $table->unsignedTinyInteger('seats')->default(5);
            $table->string('status', 40)->default('available');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('image_url', 2048)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
