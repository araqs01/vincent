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

        Schema::create('whisky_dishes', function (Blueprint $table) {
            $table->id();

            $table->json('type')->nullable(); // Тип виски (например: односолодовый, купажированный)
            $table->json('region')->nullable(); // Регион

            $table->json('sweetness')->nullable();
            $table->json('smokiness')->nullable();
            $table->json('fruitiness')->nullable();
            $table->json('strength')->nullable();
            $table->json('spiciness')->nullable();
            $table->json('astringency')->nullable();
            $table->json('body')->nullable();

            $table->longText('age')->nullable();

            $table->json('tags')->nullable();   // Теги
            $table->json('snacks')->nullable(); // Закуски

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whisky_dishes');
    }
};
