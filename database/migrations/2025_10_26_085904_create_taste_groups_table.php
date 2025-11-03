<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('taste_groups', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // fruit, spice, floral...
            $table->json('name'); // {"ru": "Фрукты", "en": "Fruits"}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taste_groups');
    }
};

