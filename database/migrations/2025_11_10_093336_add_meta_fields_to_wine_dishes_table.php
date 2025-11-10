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
        Schema::table('wine_dishes', function (Blueprint $table) {
            $table->string('aromaticity')->nullable();
            $table->string('sweetness')->nullable();
            $table->string('body')->nullable();
            $table->string('tannin')->nullable();
            $table->string('acidity')->nullable();
            $table->string('effervescence')->nullable();

            $table->float('strength_min')->nullable();
            $table->float('strength_max')->nullable();
            $table->integer('age_min')->nullable();
            $table->integer('age_max')->nullable();

            $table->string('sugar')->nullable();
            $table->float('price_min')->nullable();
            $table->float('price_max')->nullable();
            $table->string('extra_marker')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wine_dishes', function (Blueprint $table) {
            $table->dropColumn('aromaticity');
            $table->dropColumn('sweetness');
            $table->dropColumn('body');
            $table->dropColumn('tannin');
            $table->dropColumn('acidity');
            $table->dropColumn('effervescence');
            $table->dropColumn('strength_min');
            $table->dropColumn('strength_max');
            $table->dropColumn('age_min');
            $table->dropColumn('age_max');
            $table->dropColumn('sugar');
            $table->dropColumn('price_min');
            $table->dropColumn('price_max');
            $table->dropColumn('extra_marker');

        });
    }
};
