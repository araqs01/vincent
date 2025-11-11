<?php

namespace Database\Seeders;

use App\Models\WhiskyTaste;
use App\Models\WhiskyTasteGroup;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WhiskyTasteSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/catalog/whisky_tastes.xlsx');

        if (!file_exists($path)) {
            $this->command->warn("⚠️ Файл не найден: $path");
            return;
        }

        $sheet = IOFactory::load($path)->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        unset($rows[1]); // пропуск заголовков

        $count = 0;

        foreach ($rows as $row) {
            $nameRu = trim($row['A'] ?? '');
            $status = trim($row['B'] ?? '');
            $nameEn = trim($row['C'] ?? '');
            $groupRu = trim($row['D'] ?? '');
            $groupEn = trim($row['E'] ?? '');
            $weight = floatval($row['F'] ?? null);
            $typeRu = trim($row['G'] ?? '');
            $typeEn = trim($row['H'] ?? '');

            if (!$nameRu && !$nameEn) {
                continue;
            }

            // 🔹 Создаём / находим группу
            $group = null;
            if ($groupEn || $groupRu) {
                $group = WhiskyTasteGroup::where('name->en', $groupEn)->first();
            }

            // 🔹 Создаём вкус
            $whiskey =WhiskyTaste::create([
                    'name'  => ['en' => $nameEn ?: $nameRu, 'ru' => $nameRu ?: $nameEn],
                    'group' => ['en' => $groupEn ?: null, 'ru' => $groupRu ?: null],
                    'type'  => ['en' => $typeEn ?: null, 'ru' => $typeRu ?: null],
                    'weight' => $weight ?: null,
                    'group_id' => $group?->id,
                ]
            );

            $whiskey->save();
            $count++;
        }

        $this->command->info("✅ Добавлено вкусов: {$count}");
    }
}
