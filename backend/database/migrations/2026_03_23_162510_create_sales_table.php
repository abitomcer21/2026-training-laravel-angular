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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->bigInteger('restaurant_id')->references('id')->on('restaurant');
            $table->bigInteger('order_id')->references('id')->on('orders');
            $table->bigInteger('user_id')->references('id')->on('users');
            $table->integer('ticket_number');
            $table->timestamp('value_date');
            $table->integer('total');
            $table->timestamps();
            $table->softDeletes('deleted_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
