<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->string('name');
            $table->string('legal_name');
            $table->string('tax_id');
            $table->string('email');
            $table->string('password');
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
