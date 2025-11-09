<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Region;
use App\Models\Grape;
use App\Models\WineDish;

class WineDishSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/catalog/Вино - Блюда.xlsx');

        if (!file_exists($path)) {
            Log::warning("⚠️ Excel-файл не найден: {$path}");
            return;
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        unset($rows[1]); // пропускаем заголовок

        $count = 0;

        DB::transaction(function () use ($rows, &$count) {
            foreach ($rows as $index => $row) {
                try {
                    $categoryName = trim($row['B'] ?? '');
                    $color        = trim($row['C'] ?? '');
                    $grapeMix     = trim($row['D'] ?? '');
                    $blend        = trim($row['E'] ?? '');
                    $name         = trim($row['F'] ?? '');
                    $countryName  = trim($row['N'] ?? '');
                    $regionName   = trim($row['O'] ?? '');
                    $pairings     = trim($row['X'] ?? '');

                    if (!$categoryName && !$pairings) continue;

                    // 🔹 Категория
                    $category = \App\Models\Category::where('slug', 'wine')->first();

                    if (!$category) {
                        throw new \Exception('❌ Категория "Wine" не найдена в таблице categories');
                    }

                    // 🔹 Регион и страна
                    $region = null;
                    if ($countryName) {
                        $country = Region::firstOrCreate(
                            ['name->ru' => ucfirst($countryName), 'parent_id' => null],
                            ['name' => ['ru' => ucfirst($countryName), 'en' => ucfirst($countryName)]]
                        );

                        if ($regionName) {
                            $region = Region::firstOrCreate(
                                ['name->ru' => ucfirst($regionName), 'parent_id' => $country->id],
                                ['name' => ['ru' => ucfirst($regionName), 'en' => ucfirst($regionName)]]
                            );
                        } else {
                            $region = $country;
                        }
                    }

                    // 🔹 Автоматическое определение типа (игристое, брют, шампанское)
                    $type = null;
                    $nameLower = Str::lower($name . ' ' . $color);
                    if (Str::contains($nameLower, ['игрист', 'брют', 'шамп'])) {
                        $type = 'Игристое';
                    }

                    // 🔹 Преобразуем гастро сочетания
                    $pairingArray = collect(explode(',', $pairings))
                        ->map(fn($v) => trim($v))
                        ->filter()
                        ->unique()
                        ->values()
                        ->toArray();

                    // 🔹 Создаём блюдо
                    $dish = WineDish::updateOrCreate(
                        [
                            'category_id' => $category->id,
                            'name->ru'    => $name ?: ($grapeMix ?: $blend ?: 'Без названия'),
                            'region_id'   => $region?->id,
                        ],
                        [
                            'type'        => $type,
                            'color'       => $color,
                            'name'        => ['ru' => $name ?: ($grapeMix ?: $blend), 'en' => $name ?: ($grapeMix ?: $blend)],
                            'grape_mix'   => ['ru' => $grapeMix ?: $blend, 'en' => $grapeMix ?: $blend],
                            'pairings'    => $pairingArray,
                        ]
                    );

                    // 🔹 Привязка сортов винограда
                    $grapeString = $grapeMix ?: $blend;
                    if ($grapeString) {
                        $grapeNames = collect(preg_split('/[+,\/]+/u', $grapeString))
                            ->map(fn($v) => trim($v))
                            ->filter()
                            ->unique()
                            ->values();

                        if ($grapeNames->isNotEmpty()) {
                            $grapeIds = Grape::query()
                                ->whereIn(DB::raw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ru')))"), $grapeNames->map(fn($n) => Str::lower($n))->toArray())
                                ->pluck('id');

                            if ($grapeIds->isNotEmpty()) {
                                $dish->grapes()->syncWithoutDetaching($grapeIds);
                            }
                        }
                    }

                    $count++;
                } catch (\Throwable $e) {
                    Log::error("💥 Ошибка в строке {$index}: " . $e->getMessage());
                }
            }
        });

        Log::info("🍷 Импорт завершён: {$count} записей добавлено.");
    }
}
