<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->bigInteger('family_id');
            $table->bigInteger('tax_id');
            $table->string('image_src');
            $table->string('name');
            $table->integer('price');
            $table->integer('stock');
            $table->booler('active');
            $table->timestamp('created_at');
            $table->timestamp('update_at');
            $table->timestamps('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
