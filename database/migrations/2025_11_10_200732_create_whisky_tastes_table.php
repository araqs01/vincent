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
        Schema::create('whisky_tastes', function (Blueprint $table) {
            $table->id();
            $table->json('name')->nullable();   // {"ru":"Горький шоколад","en":"dark chocolate"}
            $table->json('group')->nullable();  // {"ru":"Шоколадные","en":"chocolates"}
            $table->json('type')->nullable();   // {"ru":"Сливочный","en":"creamy"}
            $table->float('weight')->nullable();
            $table->foreignId('group_id')
                ->nullable()
                ->constrained('whisky_taste_groups')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whisky_tastes');
    }
};
