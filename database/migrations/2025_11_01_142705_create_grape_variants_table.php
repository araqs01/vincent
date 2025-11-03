<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grape_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grape_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->json('meta')->nullable(); // {"sweetness": 2.5, "body": 3, "aromatic": 4, ...}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grape_variants');
    }
};

