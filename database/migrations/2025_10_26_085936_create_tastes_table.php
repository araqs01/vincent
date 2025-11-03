<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tastes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taste_group_id')->nullable()->constrained()->nullOnDelete();
            $table->json('name'); // {"ru": "Вишня", "en": "Cherry"}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tastes');
    }
};

