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
        Schema::create('wine_taste_groups', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('type')->nullable(); // Ягодное, Фруктовое и т.п.
            $table->json('final_group')->nullable(); // Древесное, Землистое, и т.д.
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wine_taste_groups');
    }
};
