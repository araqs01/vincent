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
        Schema::create('taste_group_spirits', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // fruit, spice, floral...
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('image')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taste_group_spirits');
    }
};
