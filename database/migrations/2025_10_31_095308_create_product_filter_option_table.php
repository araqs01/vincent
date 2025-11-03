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
        Schema::create('product_filter_option', function (Blueprint $table) {
            $table->id();

            // 🔗 связи
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_filter_option_id')->constrained()->cascadeOnDelete();

            // ✅ короткое имя уникального индекса
            $table->unique(['product_id', 'category_filter_option_id'], 'prod_filter_unique');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_filter_option');
    }
};
