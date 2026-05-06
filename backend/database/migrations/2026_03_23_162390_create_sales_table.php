<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('restaurant_id')->constrained('restaurants');
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('user_id')->constrained('users');
            $table->integer('ticket_number');
            $table->timestamp('value_date');
            $table->integer('total');
            $table->timestamps();
            $table->softDeletes('deleted_at');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
