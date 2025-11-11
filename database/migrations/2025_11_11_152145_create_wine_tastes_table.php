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
        Schema::create('wine_tastes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable()->constrained('wine_taste_groups')->nullOnDelete();
            $table->json('name'); // Белый виноград / White grapes
            $table->json('meta')->nullable(); // Доп. инфо: цвет, категория и т.д.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wine_tastes');
    }
};
