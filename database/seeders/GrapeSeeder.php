<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Grape;
use App\Models\GrapeVariant;
use App\Models\Region;
use App\Models\Taste;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class GrapeSeeder extends Seeder
{

    /**
     * @throws Exception
     */
    public function run(): void
    {
        $path = database_path('seeders/catalog/grapes.xlsx');
        $zipPath = database_path('seeders/catalog/grape_images.zip');
        $tempDir = storage_path('app/tmp_grape_images');

        if (!file_exists($path)) {
            $this->command->error("❌ Файл не найден: $path");
            return;
        }

        $this->command->info("🔄 Импорт сортов винограда...");

        // 📦 Распаковка архива изображений (если есть)
        if (file_exists($zipPath)) {
            if (!is_dir($tempDir) || count(glob("$tempDir/*")) === 0) {
                $this->command->info("📦 Распаковка архива изображений сортов...");
                $zip = new \ZipArchive();
                if ($zip->open($zipPath) === true) {
                    $zip->extractTo($tempDir);
                    $zip->close();
                    $this->command->info("✅ Архив распакован в: $tempDir");
                } else {
                    $this->command->warn("⚠️ Не удалось открыть архив: $zipPath");
                }
            }
        }

        $this->command->info("📖 Чтение Excel-файла (grapes.xlsx)...");

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(false);     // читаем все ячейки, включая скрытые
        $reader->setLoadAllSheets(true);     // подгружаем все листы, если они есть

        $spreadsheet = $reader->load($path);

        $sheetNames = $spreadsheet->getSheetNames();
        $this->command->info("📄 Найдено листов: " . count($sheetNames) . " (" . implode(', ', $sheetNames) . ")");

        $sheet = $spreadsheet->getSheetByName($sheetNames[0]);
        $rows = $sheet->toArray(null, true, true, true);

        $totalRows = count($rows);
        $this->command->info("📊 Всего строк в Excel: {$totalRows}");

        $count = 0;

        foreach ($rows as $row) {
            $categoryName = trim($row['A'] ?? '');
            $wineType = trim($row['B'] ?? '');
            $color = trim($row['C'] ?? '');
            $grapeNameRu = trim($row['D'] ?? '');
            $blend = trim($row['E'] ?? '');
            $grapeNameEn = trim($row['F'] ?? '');
            $displayName = trim($row['G'] ?? '');

            $aromatic = $this->parseFloat($row['H'] ?? null);
            $sweetness = $this->parseFloat($row['I'] ?? null);
            $body = $this->parseFloat($row['J'] ?? null);
            $tannin = $this->parseFloat($row['K'] ?? null);
            $acidity = $this->parseFloat($row['L'] ?? null);
            $sparkling = $this->parseFloat($row['M'] ?? null);

            $country = trim($row['N'] ?? '');
            $regionName = trim($row['O'] ?? '');
            $sugar = trim($row['P'] ?? '');
            $strengthMin = floatval($row['Q'] ?? null);
            $ageMin = intval($row['R'] ?? null);
            $oakAging = trim($row['S'] ?? '');
            $series = trim($row['T'] ?? '');
            $storage = trim($row['U'] ?? '');
            $mainTaste = trim($row['V'] ?? '');
            $aging = trim($row['W'] ?? '');
            $similarWines = trim($row['X'] ?? '');
            $similarGrapes = trim($row['Y'] ?? '');
            $tastesString = trim($row['Z'] ?? '');
            $pairingsString = trim($row['AA'] ?? '');

            // 🍇 Сорт винограда
            if (!$grapeNameRu && $grapeNameEn) {
                $grapeNameRu = $grapeNameEn;
            }

            if (!$grapeNameRu && !$grapeNameEn) {
                $this->command->warn("⏭ Пропущена строка без имени сорта (строка {$count})");
                continue;
            }

            $grape = Grape::firstOrCreate(
                ['name->ru' => $grapeNameRu],
                [
                    'name' => [
                        'ru' => $grapeNameRu,
                        'en' => $grapeNameEn ?: $grapeNameRu,
                    ],
                    'description' => [
                        'ru' => $displayName ?: $grapeNameRu,
                        'en' => $displayName ?: ($grapeNameEn ?: $grapeNameRu),
                    ],
                ]
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
            ],
                [
                    'meta' => [
                        'wine_type' => $wineType,
                        'color' => $color,
                        'blend' => $blend,
                        'aromatic' => $aromatic,
                        'sweetness' => $sweetness,
                        'body' => $body,
                        'tannin' => $tannin,
                        'acidity' => $acidity,
                        'sparkling' => $sparkling,
                        'sugar' => $sugar,
                        'strength_min' => $strengthMin,
                        'age_min' => $ageMin,
                        'oak_aging' => $oakAging,
                        'series' => $series,
                        'storage_potential' => $storage,
                        'main_taste' => $mainTaste,
                        'aging' => $aging,
                        'similar_wines' => collect(explode(',', $similarWines))->map(fn($v) => trim($v))->filter()->values(),
                        'similar_grapes' => collect(explode(',', $similarGrapes))->map(fn($v) => trim($v))->filter()->values(),
                    ]
                ]);

            // 🖼 Поиск и привязка изображения
            if (is_dir($tempDir)) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($tempDir, \FilesystemIterator::SKIP_DOTS)
                );

                $grapeName = mb_strtolower($grapeNameRu);
                $regionNameLower = mb_strtolower($regionName ?? '');
                $matchedFile = null;
                $exactMatchScore = 0;

                foreach ($files as $fileInfo) {
                    $file = $fileInfo->getFilename();
                    if (!preg_match('/\.(jpg|jpeg|png|webp)$/i', $file)) continue;

                    $base = mb_strtolower(pathinfo($file, PATHINFO_FILENAME));
                    $base = preg_replace('/^\d+[_\s-]*/u', '', $base);

                    $score = 0;
                    if (Str::contains($base, $grapeName)) $score += 10;
                    if ($regionNameLower && Str::contains($base, $regionNameLower)) $score += 5;

                    if ($score > $exactMatchScore) {
                        $exactMatchScore = $score;
                        $matchedFile = $fileInfo->getPathname();
                    }
                }

                if ($matchedFile) {
                    $filePath = Str::startsWith($matchedFile, $tempDir)
                        ? $matchedFile
                        : $tempDir . '/' . ltrim($matchedFile, '/');

                    if (!file_exists($filePath)) {
                        $this->command->warn("⚠️ Файл не найден: {$filePath}");
                        continue;
                    }

                    try {
                        if ($regionNameLower && Str::contains(mb_strtolower($matchedFile), $regionNameLower)) {
                            $media = $variant->addMedia($filePath)
                                ->preservingOriginal()
                                ->toMediaCollection('hero_image');
                        } elseif (!$grape->hasMedia('hero_image')) {
                            $media = $grape->addMedia($filePath)
                                ->preservingOriginal()
                                ->toMediaCollection('hero_image');
                        }

                        if (isset($media) && empty($media->uuid)) {
                            $media->uuid = (string)Str::uuid();
                            $media->save();
                        }

                        $this->command->info("📸 Добавлено изображение: {$grapeNameRu}");
                    } catch (\Exception $e) {
                        $this->command->warn("⚠️ Ошибка добавления изображения для {$grapeNameRu}: {$e->getMessage()}");
                    }
                } else {
                    $this->command->warn("❌ Фото не найдено для сорта: {$grapeNameRu}");
                }
            }

            // 🍷 Вкусы (если указаны)
            if ($tastesString) {
                $tastes = collect(explode(',', $tastesString))
                    ->map(fn($t) => trim(mb_strtolower($t)))
                    ->filter();

                $tasteIds = [];
                foreach ($tastes as $tasteName) {
                    $taste = Taste::query()
                        ->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ru'))) = ?", [$tasteName])
                        ->first();

                    // ⚠️ Если вкус не найден — просто пропускаем
                    if (!$taste) {
                        $this->command->warn("⏭ Вкус не найден в справочнике: {$tasteName}");
                        continue;
                    }

                    $tasteIds[] = $taste->id;
                }

                if (!empty($tasteIds)) {
                    $variant->tastes()->sync($tasteIds);
                }
            }

            $count++;
        }

        $this->command->info("✅ Импорт завершён. Добавлено вариантов: {$count}");

        // 🧹 Очистка временной директории
        if (is_dir($tempDir)) {
            try {
                File::deleteDirectory($tempDir);
                $this->command->info("🧹 Временные изображения удалены: {$tempDir}");
            } catch (\Exception $e) {
                $this->command->warn("⚠️ Не удалось удалить временные файлы: " . $e->getMessage());
            }
        }
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


