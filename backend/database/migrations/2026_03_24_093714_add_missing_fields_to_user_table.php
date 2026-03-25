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
        Schema::table('user', function (Blueprint $table) {
            $table->string('uuid')->unique()->after('id');
            $table->string('role');
            $table->string('image_src');
            $table->bigInteger('restaurant_id')->references('id')->on('restaurants');
            $table->string('pin');
            $table->softDeletes('deleted_at')->after('update_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            //
        });
    }
};