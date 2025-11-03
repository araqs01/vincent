<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('category_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->json('title');
            $table->string('mode')->default('discrete');
            $table->string('source_model')->nullable();
            $table->json('config')->nullable();
            $table->string('ui_type')->default('dropdown');

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order_index')->default(1);

            $table->timestamps();
            $table->unique(['category_id', 'key']);
            $table->index(['category_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_filters');
    }
};
