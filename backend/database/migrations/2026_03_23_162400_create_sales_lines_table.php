<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_lines', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('restaurant_id')->constrained('restaurants');
            $table->foreignId('sale_id')->constrained('sales');
            $table->foreignId('order_line_id')->constrained('order_lines');
            $table->foreignId('user_id')->constrained('users');
            $table->integer('quantity');
            $table->integer('price');
            $table->integer('tax_percentage');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_lines');
    }
};
