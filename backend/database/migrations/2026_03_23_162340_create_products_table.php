<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('restaurant_id')->constrained('restaurants');
            $table->uuid('family_id');
            $table->foreign('family_id')->references('uuid')->on('families');
            $table->uuid('tax_id');
            $table->foreign('tax_id')->references('uuid')->on('taxes');
            $table->string('image_src');
            $table->string('name');
            $table->integer('price');
            $table->integer('stock');
            $table->boolean('active');
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
