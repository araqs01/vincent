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
        Schema::create('whiskies_base', function (Blueprint $table) {
            $table->id();
            $table->json('name')->nullable();
            $table->json('manufacturer')->nullable();
            $table->boolean('is_blended')->default(false);

            $table->decimal('sweetness', 3, 1)->nullable();
            $table->decimal('smoky', 3, 1)->nullable();
            $table->decimal('fruity', 3, 1)->nullable();
            $table->decimal('spicy', 3, 1)->nullable();
            $table->decimal('floral', 3, 1)->nullable();
            $table->decimal('woody', 3, 1)->nullable();
            $table->decimal('grainy', 3, 1)->nullable();
            $table->decimal('creamy', 3, 1)->nullable();
            $table->decimal('sulphury', 3, 1)->nullable();
            $table->decimal('smooth', 3, 1)->nullable();
            $table->decimal('finish_length', 3, 1)->nullable();
            $table->decimal('bitterness', 3, 1)->nullable();
            $table->decimal('dryness', 3, 1)->nullable();
            $table->decimal('body', 3, 1)->nullable();

            $table->json('country')->nullable();
            $table->boolean('for_cigar')->default(false);
            $table->json('blend_included')->nullable();
            $table->json('blend_with')->nullable();
            $table->json('awards')->nullable();

            $table->text('aroma')->nullable();
            $table->text('taste')->nullable();
            $table->text('aftertaste')->nullable();

            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whisky_bases');
    }
};
