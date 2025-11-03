<?php

namespace App\Services;

use App\Models\Product;
use App\Models\GrapeVariant;

class ProductGrapeVariantService
{
    public static function updateGrapeProfile($product): void
    {
        $grapes = $product->grapes()->get();
        if ($grapes->isEmpty()) return;

        $regionId = $product->region_id;
        $product->grapeVariants()->detach();

        $percent = round(100 / max(1, $grapes->count()), 2);

        foreach ($grapes as $index => $grape) {
            // 1️⃣ Пробуем найти по региону
            $variant = GrapeVariant::where('grape_id', $grape->id)
                ->where('region_id', $regionId)
                ->first();

            // 2️⃣ Если не найден — пробуем без региона
            if (!$variant) {
                $variant = GrapeVariant::where('grape_id', $grape->id)
                    ->whereNull('region_id')
                    ->first();
            }

            // 3️⃣ Если всё ещё нет — создаём новый
            if (!$variant) {
                $variant = GrapeVariant::create([
                    'grape_id' => $grape->id,
                    'region_id' => $regionId,
                    'meta' => [
                        'body' => 0,
                        'aroma' => 0,
                        'tannin' => 0,
                        'acidity' => 0,
                        'sweetness' => 0,
                    ],
                ]);
            }

            // 4️⃣ Добавляем связь
            $product->grapeVariants()->attach($variant->id, [
                'percent' => $percent,
                'main' => $index === 0,
            ]);
        }
    }

    public static function calculateProfile(Product $product): array
    {
        $profile = ['body' => 0, 'aroma' => 0, 'tannin' => 0, 'acidity' => 0, 'sweetness' => 0];

        $variants = $product->grapeVariants()->with('grape')->get();

        if ($variants->isEmpty()) {
            return $profile;
        }

        foreach ($variants as $variant) {
            $meta = $variant->meta ?? [];
            foreach ($profile as $key => &$val) {
                if (isset($meta[$key])) {
                    $val += $meta[$key];
                }
            }
        }

        // Среднее значение
        foreach ($profile as &$val) {
            $val = round($val / max(1, $variants->count()), 2);
        }

        return $profile;
    }
}
