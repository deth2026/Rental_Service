<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->date('valid_from');
            $table->date('valid_until');
            $table->unsignedInteger('usage_limit')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->decimal('minimum_amount', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
