<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grapes', function (Blueprint $table) {
            $table->id();
            $table->json('name'); // {"ru": "Пино Нуар", "en": "Pinot Noir"}
            $table->json('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grapes');
    }
};
