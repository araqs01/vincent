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
        if (empty($grapeString)) {
            return;
        }

        DB::transaction(function () use ($product, $grapeString) {
            // 🔹 Разделяем строку "Cabernet Sauvignon, Merlot"
            $grapeNames = collect(explode(',', $grapeString))
                ->map(fn($v) => trim($v))
                ->filter()
                ->unique();

            $grapeIds = [];
            $variantIds = [];

            foreach ($grapeNames as $name) {
                $nameNorm = Str::lower(trim($name));

                // 🔸 Поиск сорта
                $grape = Grape::query()
                    ->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ru'))) = ?", [$nameNorm])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) = ?", [$nameNorm])
                    ->first();

                if (!$grape) {
                    $grape = Grape::create([
                        'name' => [
                            'ru' => ucfirst($nameNorm),
                            'en' => ucfirst($nameNorm),
                        ],
                    ]);
                }

                $grapeIds[] = $grape->id;

                // 🔹 Добавляем все варианты данного сорта
                if ($grape->variants()->exists()) {
                    foreach ($grape->variants as $variant) {
                        $variantIds[] = $variant->id;
                    }
                }
            }

            // 🧩 Связываем с продуктом
            if (!empty($grapeIds)) {
                $product->grapes()->syncWithoutDetaching($grapeIds);
            }

            if (!empty($variantIds)) {
                $product->grapeVariants()->syncWithoutDetaching($variantIds);
            }

            // 🍇 Теперь подтягиваем вкусы из вариантов
            static::attachVariantTastes($product, $variantIds);
        });
    }

    /**
     * Подключает вкусы от вариантов винограда.
     */
    protected static function attachVariantTastes(Product $product, array $variantIds): void
    {
        if (empty($variantIds)) return;

        // Находим все вкусы, связанные с этими вариантами
        $tasteData = DB::table('grape_variant_taste')
            ->whereIn('grape_variant_id', $variantIds)
            ->get(['taste_id', 'intensity_default']);

        if ($tasteData->isEmpty()) return;

        $sync = [];
        foreach ($tasteData as $row) {
            $tasteId = $row->taste_id;
            $intensity = (float)($row->intensity_default ?? 50);
            $sync[$tasteId] = ['intensity_percent' => $intensity];
        }

        // 🧠 Добавляем вкусы к продукту (не затирая существующие)
        $product->tastes()->syncWithoutDetaching($sync);
    }
}
