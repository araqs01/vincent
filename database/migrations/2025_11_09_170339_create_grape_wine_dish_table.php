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
        Schema::create('grape_wine_dish', function (Blueprint $table) {
            $table->foreignId('grape_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wine_dish_id')->constrained()->cascadeOnDelete();
            $table->primary(['grape_id', 'wine_dish_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grape_wine_dish');
    }
};
