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
            $table->uuid('uuid')->nullable()->unique();
            $table->bigInteger('restaurant_id')->references('id')->on('restaurants');
            $table->unsignedBigInteger('family_id');
            $table->unsignedBigInteger('tax_id');
            $table->foreign('family_id')->references('id')->on('families');
            $table->foreign('tax_id')->references('id')->on('taxes');
            $table->string('image_src');
            $table->string('name');
            $table->integer('price');
            $table->integer('stock');
            $table->boolean('active');
            $table->timestamps();
            $table->softDeletes('deleted_at');
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
