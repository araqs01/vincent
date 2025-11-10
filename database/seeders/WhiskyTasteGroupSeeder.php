<?php

namespace Database\Seeders;

use App\Models\WhiskyTasteGroup;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WhiskyTasteGroupSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/catalog/whisky_taste_groups.xlsx');

        if (!file_exists($path)) {
            $this->command->warn("⚠️ Файл не найден: $path");
            return;
        }

        $sheet = IOFactory::load($path)->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        unset($rows[1]); // Пропускаем заголовок

        $count = 0;

        foreach ($rows as $row) {
            $nameEn = trim($row['A'] ?? '');
            $typeEn = trim($row['B'] ?? '');
            $typeRu = trim($row['C'] ?? '');
            $nameRu = trim($row['D'] ?? '');

            if (!$nameEn && !$nameRu) {
                continue;
            }

            WhiskyTasteGroup::updateOrCreate(
                [
                    'name->en' => $nameEn,
                ],
                [
                    'name' => ['en' => $nameEn, 'ru' => $nameRu ?: $nameEn],
                    'type' => ['en' => $typeEn ?: null, 'ru' => $typeRu ?: null],
                ]
            );

            $count++;
        }

        $this->command->info("✅ Добавлено групп: {$count}");
    }
}
