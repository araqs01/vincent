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
        Schema::create('strong_drink_dishes', function (Blueprint $table) {
            $table->id();
            $table->json('type')->nullable();        // тип напитка (Коньяк, Арманьяк, Бренди...)
            $table->string('age')->nullable();       // возраст (до 3 лет, старше 10 и т.п.)
            $table->string('class')->nullable();     // класс (VS, VSOP, XO и т.п.)
            $table->json('taste_tags')->nullable();  // теги вкуса
            $table->string('strength')->nullable();  // крепость (возможно %, диапазон)
            $table->string('drink_type')->nullable();// тип напитка (ликер, бренди, пиво и т.п.)
            $table->json('dishes')->nullable();      // бл
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strong_drink_dishes');
    }
};
