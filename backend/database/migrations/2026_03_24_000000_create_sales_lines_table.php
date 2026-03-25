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
            $table->bigInteger('resturant_id')->references('id')->on('restaurant');
            $table->bigInteger('sale_id')->references('id')->on('sales');
            $table->bigInteger('order_line_id')->references('id')->on('order_lines');
            $table->bigInteger('user_id')->references('id')->on('users');
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
