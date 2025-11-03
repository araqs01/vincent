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
        Schema::create('menu_block_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_block_id')->constrained()->cascadeOnDelete();
            $table->json('value'); // {ru, en}
            $table->integer('order_index')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_block_values');
    }
};
