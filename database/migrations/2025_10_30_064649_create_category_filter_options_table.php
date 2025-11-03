<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('category_filter_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filter_id')->constrained('category_filters')->cascadeOnDelete();
            $table->json('value');
            $table->string('slug')->nullable();
            $table->json('meta')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order_index')->default(1);

            $table->boolean('show_in_header')->default(false);
            $table->timestamps();

            $table->index(['filter_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_filter_options');
    }
};
