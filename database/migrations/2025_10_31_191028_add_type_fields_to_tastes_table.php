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
            $table->string('type')->nullable()->after('name'); // fruit, spice, etc.
            $table->string('group')->nullable()->after('type'); // grape, product
            $table->unsignedTinyInteger('intensity_default')->default(50)->after('group');
        });
    }

    public function down(): void
    {
        Schema::table('tastes', function (Blueprint $table) {
            $table->dropColumn(['type', 'group', 'intensity_default']);
        });
    }
};
