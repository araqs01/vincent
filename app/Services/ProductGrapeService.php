<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Grape;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductGrapeService
{
    /**
     * Привязывает сорта винограда к продукту.
     * Поддерживает как указанные проценты ("Cabernet 70%, Merlot 30%"),
     * так и без процентов ("Cabernet, Merlot, Syrah" → 60/25/15).
     */
    public static function attachGrapes($product, string $grapesCsv): void
    {
        // 🧩 1. Разделяем строку и очищаем имена
        $grapeNames = collect(explode(',', $grapesCsv))
            ->map(function ($name) {
                $name = trim($name ?? '');
                // убираем неразрывные пробелы, табы, двойные пробелы
                $name = preg_replace('/[\x{00A0}\x{200B}\x{202F}\s]+/u', ' ', $name);
                // нормализуем регистр — первая буква заглавная
                return mb_convert_case($name, MB_CASE_TITLE, "UTF-8");
            })
            ->filter()
            ->unique()
            ->values();

        if ($grapeNames->isEmpty()) {
            return;
        }

        // 🧩 2. Создаём / находим объекты Grape
        $grapes = $grapeNames->map(function ($name) {
            return Grape::firstOrCreate(
                ['name->ru' => $name],
                ['name' => ['ru' => $name, 'en' => $name]]
            );
        });

        // 🧩 3. Удаляем старые связи, если есть
        $product->grapes()->detach();

        // 🧩 4. Распределяем проценты и задаём главный сорт
        $percent = round(100 / max(1, $grapes->count()), 2);

        foreach ($grapes as $index => $grape) {
            $product->grapes()->attach($grape->id, [
                'percent' => $percent,
                'main' => $index === 0, // первый сорт делаем главным ✅
            ]);
        }
    }

    /**
     * Распределяет проценты для N сортов, если они не указаны.
     * Основано на сценарии клиента: 2 → 70/30, 3 → 60/25/15, 4 → 50/25/15/10, 5 → 40/25/15/10/10.
     */
    protected static function distributeDefaultPercents(int $count): array
    {
        return match ($count) {
            1 => [100],
            2 => [70, 30],
            3 => [60, 25, 15],
            4 => [50, 25, 15, 10],
            5 => [40, 25, 15, 10, 10],
            6 => [35, 25, 15, 10, 10, 5],
            default => array_fill(0, $count, round(100 / $count))
        };
    }

    /**
     * Рассчитывает вкусовой профиль купажа на основе meta сортов (опционально).
     * Возвращает средневзвешенные показатели (танинность, кислотность и т.п.).
     */
    public static function calculateProfile(Product $product): ?array
    {
        $grapes = $product->grapes()->withPivot('percent')->get();
        if ($grapes->isEmpty()) return null;

        $profile = [
            'tannins' => 0,
            'acidity' => 0,
            'sweetness' => 0,
            'aroma' => 0,
            'body' => 0,
        ];

        $total = 0;
        foreach ($grapes as $grape) {
            $weight = $grape->pivot->percent ?? 0;
            $meta = $grape->variants()->first()?->meta ?? []; // можно взять из GrapeVariant
            foreach ($profile as $k => $_) {
                $profile[$k] += ($meta[$k] ?? 0) * $weight;
            }
            $total += $weight;
        }

        if ($total > 0) {
            foreach ($profile as $k => $v) {
                $profile[$k] = round($v / $total, 1);
            }
        }

        return $profile;
    }
}
