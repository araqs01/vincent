<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('category_sort_groups', function (Blueprint $table) {
            $table->id();

            // связь с категорией
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();

            // ключ группы (внутренний идентификатор)
            $table->string('key');

            // локализованное название группы (например: "Основные сортировки", "Вкусовые характеристики")
            $table->json('title');

            // dropdown / scale-group / toggle и т.д.
            $table->string('ui_type')->default('dropdown');

            // порядковый номер, активность
            $table->unsignedInteger('order_index')->default(1);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // индекс для сортировки и фильтрации
            $table->index(['category_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_sort_groups');
    }
};
