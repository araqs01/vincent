<?php

namespace App\Services;

use App\Models\Grape;
use App\Models\GrapeVariant;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductGrapeService
{
    /**
     * Привязывает к продукту сорта винограда (и их варианты), а также вкусы.
     */
    public static function attachGrapes(Product $product, string $grapeString): void
    {
        if (empty(trim($grapeString))) {
            return;
        }

        try {
            DB::transaction(function () use ($product, $grapeString) {

                // 🔹 Нормализация
                $raw = trim($grapeString);
                $raw = preg_replace('/[\x{00A0}\x{2000}-\x{200D}\x{202F}\x{205F}\x{3000}]+/u', ' ', $raw);
                $raw = preg_replace('/\s{2,}/u', ' ', $raw);
                $raw = Str::lower(str_replace(['–', '—', '-'], ' ', $raw));

                $specialGroups = [
                    'белые сорта винограда'   => 'Белые сорта винограда',
                    'красные сорта винограда' => 'Красные сорта винограда',
                    'мускатные сорта винограда' => 'Мускатные сорта винограда',
                    'смесь сортов винограда'  => 'Смесь сортов винограда',
                    'разные сорта винограда'  => 'Разные сорта винограда',
                ];

                $chunks = Str::contains($raw, ',')
                    ? array_map('trim', explode(',', $raw))
                    : preg_split('/\s+/u', $raw);

                $chunks = array_values(array_filter($chunks));

                $grapeIds = [];
                $variantIds = [];

                $allGrapes = Grape::all()->map(function ($g) {
                    return [
                        'id' => $g->id,
                        'ru' => Str::lower($g->getTranslation('name', 'ru')),
                        'en' => Str::lower($g->getTranslation('name', 'en')),
                    ];
                });

                $combined = [];
                for ($i = 0; $i < count($chunks); $i++) {
                    $word = trim($chunks[$i]);
                    $next = $chunks[$i + 1] ?? null;
                    $pair = $next ? trim("$word $next") : $word;

                    $foundPair = $allGrapes->first(fn($g) => $g['ru'] === $pair || $g['en'] === $pair);
                    if ($foundPair) {
                        $combined[] = $pair;
                        $i++;
                    } else {
                        $combined[] = $word;
                    }
                }

                foreach ($specialGroups as $key => $displayName) {
                    if (Str::contains($raw, $key)) {
                        $groupGrape = Grape::query()
                            ->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ru'))) = ?", [Str::lower($displayName)])
                            ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) = ?", [Str::lower($displayName)])
                            ->first();
                        if ($groupGrape) {
                            $grapeIds[] = $groupGrape->id;
                        }
                    }
                }


                foreach ($combined as $name) {
                    $found = $allGrapes->first(fn($g) => $g['ru'] === $name || $g['en'] === $name);
                    if (!$found) {
                        $found = Grape::query()
                            ->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ru'))) LIKE ?", ["%$name%"])
                            ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", ["%$name%"])
                            ->first();
                    }

                    if ($found) {
                        $grapeIds[] = $found['id'];
                    }
                }

                if (!empty($grapeIds)) {

                    $product->refresh();
                    $product->grapes()->syncWithoutDetaching($grapeIds);

                    $variants = \App\Models\GrapeVariant::whereIn('grape_id', $grapeIds)->pluck('id');
                    if ($variants->isNotEmpty()) {
                        $product->grapeVariants()->syncWithoutDetaching($variants);
                        static::attachVariantTastes($product, $variants->all());
                    }
                }
            });
        } catch (\Throwable $e) {
            dump('❌ rollback:', $e->getMessage(), $e->getTraceAsString());
        }

    }

    /**
     * Подключает вкусы от вариантов винограда.
     */
    protected static function attachVariantTastes(Product $product, array $variantIds): void
    {
        if (empty($variantIds)) return;

        // 1️⃣ Получаем все вкусы, связанные с вариантами винограда
        $tasteIds = \DB::table('grape_variant_taste')
            ->whereIn('grape_variant_id', $variantIds)
            ->pluck('taste_id')
            ->unique()
            ->values();

        if ($tasteIds->isEmpty()) {
            return;
        }

        // 2️⃣ Количество вкусов
        $M = $tasteIds->count();
        $sync = [];

        // 3️⃣ Настраиваем шаги для двух серий (нечётные / чётные)
        $step = 1 / $M;
        $oddValue = 1.0;   // Нечётные: 1, 0.9, 0.8, ...
        $evenValue = 0.5;  // Чётные: 0.5, 0.4, 0.3, ...

        foreach ($tasteIds->values() as $i => $tasteId) {
            $x = $i + 1;

            if ($x % 2 === 1) {
                $value = max(0, $oddValue);
                $oddValue -= $step;
            } else {
                $value = max(0, $evenValue);
                $evenValue -= $step;
            }

            $sync[$tasteId] = [
                'intensity_percent' => round($value * 100, 1),
            ];
        }

        // 4️⃣ Привязываем вкусы к продукту
        $product->tastes()->syncWithoutDetaching($sync);

        \Log::info("✅ Добавлено вкусов: {$M} для продукта ID {$product->id}");
    }
}

