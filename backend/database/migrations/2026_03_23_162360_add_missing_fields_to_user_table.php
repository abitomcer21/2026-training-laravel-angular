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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->after('password');
            $table->string('image_src')->after('role')->nullable();
            $table->foreignId('restaurant_id')->constrained('restaurants')->after('image_src')->nullable();
            $table->string('pin')->after('restaurant_id')->nullable();
            $table->softDeletes('deleted_at')->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['restaurant_id']);
            $table->dropColumn('restaurant_id');
            $table->dropColumn('role');
            $table->dropColumn('image_src');
            $table->dropColumn('pin');
            $table->dropSoftDeletes();
        });
    }
};