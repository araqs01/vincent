<?php

namespace Database\Seeders;

use App\Helpers\TasteHelper;
use App\Models\Category;
use App\Models\Grape;
use App\Models\GrapeVariant;
use App\Models\Region;
use App\Models\Taste;
use App\Models\TasteGroup;
use App\Models\Pairing;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GrapeSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/public/grapes.xlsx');

        if (!file_exists($path)) {
            $this->command->error("❌ Файл не найден : $path");
            return;
        }

        $this->command->info("🔄 Импорт сортов винограда...");

        // 🔧 Создаём группы вкусов из словаря
        $dictionary = config('taste_dictionary');
        foreach ($dictionary as $slug => $data) {
            TasteGroup::firstOrCreate(
                ['slug' => $slug],
                ['name' => ['ru' => ucfirst($slug), 'en' => ucfirst($slug)]]
            );
        }

        // 📖 Чтение Excel
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        unset($rows[1]); // пропускаем заголовок

        $count = 0;

        foreach ($rows as $row) {
            $categoryName = trim($row['A'] ?? '');
            $grapeNameRu = trim($row['B'] ?? '');
            $grapeNameEn = trim($row['C'] ?? '');
            $aromatic  = $this->parseFloat($row['D'] ?? null);
            $sweetness = $this->parseFloat($row['E'] ?? null);
            $body      = $this->parseFloat($row['F'] ?? null);
            $tannin    = $this->parseFloat($row['G'] ?? null);
            $acidity   = $this->parseFloat($row['H'] ?? null);
            $sparkling = $this->parseFloat($row['I'] ?? null);
            $country = trim($row['J'] ?? '');             // Страна
            $regionName = trim($row['K'] ?? '');          // Регион
            $tastesString = trim($row['P'] ?? '');        // Вкусы
            $pairingsString = trim($row['Q'] ?? '');      // Гастрономические сочетания

            if (!$grapeNameRu) continue;

            // 🍇 Сорт винограда
            $grape = Grape::firstOrCreate(
                ['name->ru' => $grapeNameRu],
                ['name' => ['ru' => $grapeNameRu, 'en' => $grapeNameEn ?: $grapeNameRu]]
            );

            // 🏷 Категория
            $category = match (mb_strtolower($categoryName)) {
                'вино' => Category::find(1),
                default => Category::find(1),
            };

            // 🌍 Страна / Регион
            $region = null;
            if ($country || $regionName) {
                $countryRegion = Region::firstOrCreate(
                    ['name->ru' => $country],
                    ['name' => ['ru' => $country, 'en' => $country]]
                );

                if ($regionName) {
                    $region = Region::firstOrCreate(
                        ['name->ru' => $regionName, 'parent_id' => $countryRegion->id],
                        [
                            'name' => ['ru' => $regionName, 'en' => $regionName],
                            'parent_id' => $countryRegion->id,
                        ]
                    );
                } else {
                    $region = $countryRegion;
                }
            }

            // 🔸 Вариант сорта
            $variant = GrapeVariant::firstOrCreate([
                'grape_id' => $grape->id,
                'region_id' => $region?->id,
                'category_id' => $category?->id,
            ], [
                'meta' => [
                    'aromatic'   => $aromatic,
                    'sweetness'  => $sweetness,
                    'body'       => $body,
                    'tannin'     => $tannin,
                    'acidity'    => $acidity,
                    'sparkling'  => $sparkling,
                ],
            ]);

            // 🍓 Вкусы
            if ($tastesString) {
                $tasteNames = array_map('trim', explode(',', $tastesString));

                foreach ($tasteNames as $tasteName) {
                    if (!$tasteName) continue;

                    // 🧭 Определяем группу вкуса
                    $groupSlug = TasteHelper::detectGroup($tasteName) ?? 'other';

                    $group = TasteGroup::firstOrCreate(
                        ['slug' => $groupSlug],
                        ['name' => ['ru' => ucfirst($groupSlug), 'en' => ucfirst($groupSlug)]]
                    );

                    // 🧠 Определяем язык (русский или английский)
                    $isRussian = preg_match('/[а-яё]/iu', $tasteName);

                    if ($isRussian) {
                        // Вкус на русском → переводим на английский
                        $ruName = $tasteName;
                        $enName = TasteHelper::translate($tasteName, 'en');
                    } else {
                        // Вкус на английском → переводим на русский
                        $enName = $tasteName;
                        $ruName = TasteHelper::translate($tasteName, 'ru');
                    }

                    // 🍷 Создаём или обновляем вкус
                    $taste = Taste::firstOrCreate(
                        ['name->en' => $enName],
                        [
                            'name' => [
                                'ru' => $ruName ?: $tasteName,
                                'en' => $enName,
                            ],
                            'taste_group_id' => $group?->id,
                        ]
                    );

                    // 🔗 Привязка к варианту
                    $variant->tastes()->syncWithoutDetaching([$taste->id]);

                    // 🧠 Логируем неизвестные вкусы
                    if ($groupSlug === 'other') {
                        \Log::info("🆕 Unknown taste detected: {$tasteName}");
                    }
                }
            }

            // 🍽 Гастрономические сочетания
            if ($pairingsString) {
                $pairingNames = array_map('trim', explode(',', $pairingsString));
                foreach ($pairingNames as $pairingName) {
                    if (!$pairingName) continue;

                    $pairing = Pairing::firstOrCreate(
                        ['name->ru' => $pairingName],
                        ['name' => ['ru' => $pairingName, 'en' => $pairingName]]
                    );
                    $variant->pairings()->syncWithoutDetaching([$pairing->id]);
                }
            }

            $count++;
        }

        $this->command->info("✅ Импорт завершён. Добавлено вариантов: {$count}");
    }

    private function parseFloat($value): ?float
{
    if ($value === null || $value === '') {
        return null;
    }

    // 🟢 1. Если это объект DateTime (PhpSpreadsheet иногда возвращает)
    if ($value instanceof \DateTimeInterface) {
        $day = (int)$value->format('d');
        $month = (int)$value->format('m');
        return round($day + $month / 10, 1);
    }

    $value = trim((string)$value);

    // 🟡 2. Английский месяц (например, "1-May")
    if (preg_match('/^(\d{1,2})[-\s]?(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)/i', $value, $m)) {
        $monthMap = [
            'jan' => 0.1, 'feb' => 0.2, 'mar' => 0.3, 'apr' => 0.4,
            'may' => 0.5, 'jun' => 0.6, 'jul' => 0.7, 'aug' => 0.8,
            'sep' => 0.9, 'oct' => 1.0, 'nov' => 1.1, 'dec' => 1.2,
        ];
        $base = (float)$m[1];
        $suffix = $monthMap[strtolower($m[2])];
        return round($base + $suffix, 1);
    }

    // 🔵 3. Русский месяц (например, "01.май")
    if (preg_match('/^(\d{1,2})\.(янв|фев|мар|апр|май|июн|июл|авг|сен|окт|ноя|дек)/ui', $value, $m)) {
        $monthMap = [
            'янв' => 0.1, 'фев' => 0.2, 'мар' => 0.3, 'апр' => 0.4,
            'май' => 0.5, 'июн' => 0.6, 'июл' => 0.7, 'авг' => 0.8,
            'сен' => 0.9, 'окт' => 1.0, 'ноя' => 1.1, 'дек' => 1.2,
        ];
        $base = (float)$m[1];
        $suffix = $monthMap[mb_strtolower($m[2])];
        return round($base + $suffix, 1);
    }

    // ⚪ 4. Формат "01.08.2025"
    if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $value, $m)) {
        $day = (int)$m[1];
        $month = (int)$m[2];
        return round($day + $month / 10, 1);
    }

    // 🟠 5. Excel internal numeric date (45444)
    if (is_numeric($value) && $value > 40000) {
        $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
        $day = (int)$dt->format('d');
        $month = (int)$dt->format('m');
        return round($day + $month / 10, 1);
    }

    // ⚫ 6. Простое число или "1,5"
    $value = str_replace(',', '.', $value);
    return is_numeric($value) ? (float)$value : null;
}

}


