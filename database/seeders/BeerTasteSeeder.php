<?php

namespace Database\Seeders;

use App\Models\BeerTaste;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BeerTasteSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/catalog/beer_tastes.xlsx');

        if (!file_exists($path)) {
            $this->command->warn("⚠️ Файл не найден: $path");
            return;
        }

        $sheet = IOFactory::load($path)->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        unset($rows[1]); // Заголовок

        $count = 0;

        foreach ($rows as $row) {
            $nameRu = trim($row['A'] ?? '');
            $nameEn = trim($row['B'] ?? '');

            if (!$nameRu && !$nameEn) continue;

            BeerTaste::updateOrCreate(
                ['name->en' => $nameEn],
                ['name' => ['en' => $nameEn ?: $nameRu, 'ru' => $nameRu ?: $nameEn]]
            );

            $count++;
        }

        $this->command->info("✅ Добавлено вкусов пива: {$count}");
    }
}
