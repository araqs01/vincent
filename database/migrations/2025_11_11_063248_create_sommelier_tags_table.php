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
        Schema::create('sommelier_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('sommelier_groups')->cascadeOnDelete();
            $table->json('name'); // переводы (ru/en)
            $table->string('slug');
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sommelier_tags');
    }
};
