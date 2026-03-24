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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreign('restaurante_id')->references('id')->on('restaurants');
            $table->bigInteger('table_id')->references('id')->on('tables');
            $table->bigInteger('opened_by_user_id')->references('id')->on('users');
            $table->bigInteger('closed_by_user_id')->references('id')->on('users')->nullable();
            $table->integer('diners'); 
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
