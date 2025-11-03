<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            // Базовые поля
            $table->string('volume')->nullable()->comment('Объем, например 0.75 л');
            $table->decimal('price', 12, 2)->nullable()->comment('Базовая цена');
            $table->decimal('final_price', 12, 2)->nullable()->comment('Цена со скидкой, если есть');

            // Доп. атрибуты
            $table->string('sku')->nullable()->comment('Артикул');
            $table->string('barcode')->nullable()->comment('Штрихкод / EAN');
            $table->integer('stock')->nullable()->default(0)->comment('Количество на складе');

            // JSON-метаданные
            $table->json('meta')->nullable()->comment('Дополнительные данные варианта');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
