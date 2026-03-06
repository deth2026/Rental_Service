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
            $table->string('name');
            $table->string('type');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('plate_number')->nullable();
            $table->decimal('price_per_day', 10, 2);
            $table->string('fuel_type')->nullable();
            $table->string('transmission')->nullable();
            $table->string('status')->default('Available');
            $table->text('description')->nullable();
            $table->text('image_url')->nullable();  // Changed to TEXT for base64 images
            $table->text('photos')->nullable();      // Changed to TEXT for base64 images
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
