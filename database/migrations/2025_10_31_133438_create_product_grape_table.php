<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_grape', function (Blueprint $table) {
            $table->id();

            // 🔗 Связи
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('grape_id')->constrained('grapes')->cascadeOnDelete();

            // 🍇 Процент сорта (если это купаж)
            $table->decimal('percent', 5, 2)->nullable();

            $table->timestamps();

            // 🔒 Уникальная комбинация, чтобы один и тот же сорт не повторялся в продукте
            $table->unique(['product_id', 'grape_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_grape');
    }
};
