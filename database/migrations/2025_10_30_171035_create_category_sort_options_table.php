<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('category_sort_options', function (Blueprint $table) {
            $table->id();

            // связь с группой сортировки
            $table->foreignId('group_id')
                ->constrained('category_sort_groups')
                ->cascadeOnDelete();

            // например: price_asc, newest, taste_aromatic
            $table->string('key');

            // название: { ru, en }
            $table->json('title');

            // поле сортировки в таблице товаров (price, rating, volume и т.п.)
            $table->string('field')->nullable();

            // направление сортировки
            $table->enum('direction', ['asc', 'desc'])->default('asc');

            // тип поведения: scale (ползунок), boolean (тумблер), custom (ручная логика)
            $table->enum('type', ['scale', 'boolean', 'custom'])->default('scale');

            // для визуализации на фронте (dropdown / scale / toggle)
            $table->string('ui_type')->default('dropdown');

            // дополнительные данные, например scale_labels
            $table->json('meta')->nullable();

            $table->unsignedInteger('order_index')->default(1);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['group_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_sort_options');
    }
};
