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
        Schema::create('wine_dishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('color')->nullable();
            $table->string('type')->nullable(); // например: Игристое, Брют, Просекко
            $table->json('name')->nullable();        // название вина, если есть
            $table->json('grape_mix')->nullable();   // текст купажа
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->json('pairings')->nullable();    // блюда
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wine_dishes');
    }
};
