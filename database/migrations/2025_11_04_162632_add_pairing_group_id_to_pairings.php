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
        Schema::table('pairings', function (Blueprint $table) {
            $table->foreignId('pairing_group_id')->nullable()->constrained('pairing_groups')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pairings', function (Blueprint $table) {
            $table->dropForeign('pairing_group_id');
        });
    }
};
