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
            $table->string('uuid');
            $table->integer('ticket_number');
            $table->varchar('status');
            $table->bigInteger('table_id');
            $table->bigInteger('opened_by_user_id');
            $table->bigInteger('closed_by_user_id');
            $table->int('diners');
            $table->timestamps('opened_at');
            $table->timestamps('closed_at');
            $table->integer('total');
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
        Schema::dropIfExists('sales');
    }
};
