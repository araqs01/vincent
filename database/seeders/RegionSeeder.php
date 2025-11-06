<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\File;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/catalog/regions.xlsx');

        if (!File::exists($path)) {
            $this->command->error("❌ Файл не найден: $path");
            return;
        }

        $this->command->info("🔄 Импорт регионов из Excel...");

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        unset($rows[1]); // Пропускаем заголовки

        $count = 0;
        $skipWords = ['1-й уровень', '2-й уровень', '3-й уровень', '4-й уровень', '5-й уровень'];


        $debugCount = 0;
        $parentByLevel = [];

        foreach ($rows as $row) {
            $levels = [
                $this->splitNames($row['A'] ?? ''),
                $this->splitNames($row['B'] ?? ''),
                $this->splitNames($row['C'] ?? ''),
                $this->splitNames($row['D'] ?? ''),
                $this->splitNames($row['E'] ?? ''),
            ];

            $maxLevel = count($levels);

            for ($i = 0; $i < $maxLevel; $i++) {
                $names = $levels[$i];
                if (empty($names)) continue;

                foreach ($names as $index => $nameRaw) {
                    if (in_array(mb_strtolower($nameRaw), array_map('mb_strtolower', $skipWords))) continue;

                    $parsed = $this->parseRegionName($nameRaw);
                    $nameRu = $parsed['ru'];
                    $nameEn = $parsed['en'];

                    $parent = $parentByLevel[$i - 1] ?? null;

                    $region = Region::firstOrCreate(
                        [
                            'name->ru' => $nameRu,
                            'parent_id' => $parent?->id,
                        ],
                        [
                            'name' => [
                                'ru' => $nameRu,
                                'en' => $nameEn ?: $nameRu,
                            ],
                            'parent_id' => $parent?->id,
                        ]
                    );

                    $parentByLevel[$i] = $region;
                }

            }
        }



        $this->command->info("✅ Импорт завершён. Добавлено или обновлено регионов: {$count}");
    }

    private function splitNames(string $value): array
    {
        if (!$value) return [];

        $value = trim($value);

        // Возвращаем всё как одно имя, даже если есть запятая
        return [$value];
    }



    /**
     * Парсит имя региона и определяет ru/en варианты.
     *
     * Примеры:
     *  "Франция" → ['ru' => 'Франция', 'en' => 'France']
     *  "Юго запад Франции, Сюд уэст" → ['ru' => 'Юго запад Франции', 'en' => 'Сюд уэст']
     *  "О-Медок, О'Медок" → ['ru' => 'О-Медок', 'en' => "О'Медок"]
     */
    private function parseRegionName(string $value): array
    {
        $value = trim($value);
        if (!$value) return ['ru' => null, 'en' => null];

        // Если есть запятая, пробуем разделить на две части
        if (str_contains($value, ',')) {
            [$first, $second] = array_map('trim', explode(',', $value, 2));

            // если вторая часть содержит латиницу → считаем её английским вариантом
            if (preg_match('/[A-Za-z]/', $second)) {
                return ['ru' => $first, 'en' => $second];
            }

            // если обе части кириллица → считаем альтернативными русскими названиями
            if (preg_match('/[А-Яа-яЁё]/u', $second)) {
                return ['ru' => $first, 'en' => $second];
            }
        }

        // иначе просто одно имя
        return ['ru' => $value, 'en' => $value];
    }

}
