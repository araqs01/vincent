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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // 🌐 Переводимые поля
            $table->json('name');
            $table->json('description')->nullable();

            // 🔗 Связи
            $table->string('slug')->unique();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('brand_line_id')->nullable()->constrained()->nullOnDelete(); // добавлено
            $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('manufacturer_id')->nullable()->constrained()->nullOnDelete();

            // 💰 Цены
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('final_price', 12, 2)->nullable();

            // 📊 Метаданные
            $table->float('rating')->nullable();
            $table->string('status')->default('active');
            $table->json('meta')->nullable(); // универсальные параметры (винтаж, серия, крепость и т.д.)
            $table->string('alcohol_strength')->nullable();

            $table->timestamps();
        });
    }



/**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
