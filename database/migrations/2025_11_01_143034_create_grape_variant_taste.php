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
        Schema::create('grape_variant_taste', function (Blueprint $table) {
            $table->foreignId('grape_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('taste_id')->constrained()->cascadeOnDelete();
            $table->float('intensity_default')->default(50);
            $table->primary(['grape_variant_id', 'taste_id']); // составной ключ
            $table->unsignedSmallInteger('order_index')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grape_variant_taste');
    }
};
