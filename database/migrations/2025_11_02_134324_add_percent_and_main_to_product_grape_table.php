<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_grape', function (Blueprint $table) {
            if (!Schema::hasColumn('product_grape', 'percent')) {
                $table->decimal('percent', 5, 2)->nullable()->after('grape_id')
                    ->comment('Доля сорта в купаже (в %)');
            }
            if (!Schema::hasColumn('product_grape', 'main')) {
                $table->boolean('main')->default(false)->after('percent')
                    ->comment('Основной сорт в купаже');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_grape', function (Blueprint $table) {
            $table->dropColumn(['percent', 'main']);
        });
    }
};

