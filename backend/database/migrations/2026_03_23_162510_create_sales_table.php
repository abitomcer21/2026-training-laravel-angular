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
            $table->integer('ticket_number');
            $table->varchar('status');
            $table->foreign('table_id')->references('id')->on('tables');
            $table->foreign('opened_by_user_id')->references('id')->on('users');
            $table->foreign('closed_by_user_id')->references('id')->on('users')->nullable();
            $table->int('diners');
            $table->timestamps('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->integer('total');
            $table->timestamps();
            $table->softDeletes('deleted_at', precision: 0);

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
