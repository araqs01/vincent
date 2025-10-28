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
        Schema::create('tastes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taste_group_id')->nullable()->constrained()->nullOnDelete();
            $table->json('name');
            $table->float('weight')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tastes');
    }
};
