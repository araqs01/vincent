<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Grape;
use App\Models\GrapeVariant;
use App\Models\Region;
use App\Models\WineDish;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

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

        $rows = array_filter($rows, function ($row) {
            return collect($row)
                ->map(fn($v) => trim((string)$v))
                ->filter()
                ->isNotEmpty();
        });

        array_shift($rows);

        $count = 0;

        DB::transaction(function () use ($rows, &$count) {
            foreach ($rows as $index => $row) {
                try {
                    $categoryName = trim($row['B'] ?? '');
                    $color = trim($row['C'] ?? '');
                    $grapeMix = trim($row['D'] ?? '');
                    $blend = trim($row['E'] ?? '');
                    $name = trim($row['F'] ?? '');
                    $unitMarker = trim($row['G'] ?? 0); // ← колонка "единица"
                    $aromaticity = trim($row['H'] ?? '');
                    $sweetness = trim($row['I'] ?? '');
                    $body = trim($row['J'] ?? '');
                    $tannin = trim($row['K'] ?? '');
                    $acidity = trim($row['L'] ?? '');
                    $effervescence = trim($row['M'] ?? '');
                    $countryName = trim($row['N'] ?? '');
                    $regionName = trim($row['O'] ?? '');
                    $strengthMin = floatval($row['P'] ?? null);
                    $strengthMax = floatval($row['Q'] ?? null);
                    $ageMin = intval($row['R'] ?? null);
                    $ageMax = intval($row['S'] ?? null);
                    $sugar = trim($row['T'] ?? '');
                    $priceMin = floatval($row['U'] ?? null);
                    $priceMax = floatval($row['V'] ?? null);
                    $extraMarker = trim($row['W'] ?? '');
                    $pairings = trim($row['X'] ?? '');


                    // 🔹 Категория
                    $category = Category::where('slug', 'wine')->first();
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

                    // 🔹 Если есть «1» в колонке — тянем характеристики из GrapeVariant
                    $meta = [];
                    if ($unitMarker === '1') {
                        $baseGrapeName = $name ?: ($grapeMix ?: $blend);
                        if ($baseGrapeName) {
                            $variant = GrapeVariant::query()
                                ->whereHas('grape', function ($q) use ($baseGrapeName) {
                                    $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) = ?", [Str::lower($baseGrapeName)]);
                                })
                                ->first();
                            if ($variant) {
                                $metaFromGrape = $variant->meta ?? [];
                                $aromaticity = $metaFromGrape['aromatic'] ?? $aromaticity;
                                $sweetness = $metaFromGrape['sweetness'] ?? $sweetness;
                                $body = $metaFromGrape['body'] ?? $body;
                                $tannin = $metaFromGrape['tannin'] ?? $tannin;
                                $acidity = $metaFromGrape['acidity'] ?? $acidity;
                                $effervescence = $metaFromGrape['sparkling'] ?? $effervescence;

                                $meta = [
                                    'source_grape_variant_id' => $variant->id,
                                    'source_grape_name' => $baseGrapeName,
                                    'import_mode' => 'from_grape_variant'
                                ];

                                Log::info("🍇 Подтянуты характеристики из GrapeVariant для {$baseGrapeName}");
                            } else {
                                Log::warning("⚠️ GrapeVariant не найден для {$baseGrapeName}");
                            }
                        }
                    }

                    // 🔹 Создаём или обновляем блюдо
                    $dish = WineDish::create([
                        'category_id' => $category->id,
                        'region_id' => $region?->id,
                        'type' => $type,
                        'color' => $color,
                        'name' => ['ru' => $name ?: ($grapeMix ?: $blend), 'en' => $name ?: ($grapeMix ?: $blend)],
                        'grape_mix' => ['ru' => $grapeMix ?: $blend, 'en' => $grapeMix ?: $blend],
                        'pairings' => $pairingArray,
                        'aromaticity' => $aromaticity,
                        'sweetness' => $sweetness,
                        'body' => $body,
                        'tannin' => $tannin,
                        'acidity' => $acidity,
                        'effervescence' => $effervescence,
                        'strength_min' => $strengthMin,
                        'strength_max' => $strengthMax,
                        'age_min' => $ageMin,
                        'age_max' => $ageMax,
                        'sugar' => $sugar,
                        'price_min' => $priceMin,
                        'price_max' => $priceMax,
                        'extra_marker' => $extraMarker,
                        'meta' => $meta,
                        'grouping' => $unitMarker,
                    ]);

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

        $this->command->info("📄 Найдено строк после фильтрации: " . count($rows));
    }
}
