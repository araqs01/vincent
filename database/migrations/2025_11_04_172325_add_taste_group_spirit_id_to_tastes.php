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
        Schema::table('tastes', function (Blueprint $table) {
            $table->foreignId('taste_group_spirit_id')->nullable()->constrained('taste_group_spirits')->nullOnDelete();
            $table->boolean('is_spirit')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tastes', function (Blueprint $table) {
            $table->dropForeign('tastes_taste_group_spirit_id_foreign');
            $table->dropColumn('is_spirit');
        });
    }
};
