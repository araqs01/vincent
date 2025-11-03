<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Taste;
use App\Models\TasteGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Финальная версия логики вкусов:
 * - Использует только wine_tastes (VivinoLink — просто ссылка)
 * - Расчёт весов из строки формата "apple - 9; honey - 7; citrus - 4"
 * - Сохраняет веса в product_taste.intensity_percent
 * - Группирует по taste_group_id, нормирует по группе-лидеру
 */
class ProductTasteService
{
    public static function buildAndAttachTastes(
        Product $product,
        ?string $textTastesCsv,
        ?string $descriptionRu,
        bool $hasOakByFilters = false
    ): void {
        DB::transaction(function () use ($product, $textTastesCsv, $descriptionRu, $hasOakByFilters) {

            // 1️⃣ Расчёт тегов из wine_tastes
            $tastes = static::parseWineTastes($textTastesCsv);

            if (empty($tastes)) {
                $product->tastes()->detach();
                return;
            }

            // 2️⃣ Учитываем дуб / ваниль
            $hasOakText = static::detectOakAgingMention($descriptionRu);
            // если есть выдержка по фильтрам или упоминание в тексте
            $hasOak = $hasOakByFilters || $hasOakText;
            $tastes = static::applyOakVanillaRules($tastes, $hasOak);

            // 3️⃣ Группировка и нормализация
            $grouped = static::groupAndNormalize($tastes);

            // 4️⃣ Сохранение в product_taste
            static::persistProductTastes($product, $tastes);

            // 5️⃣ Сохранение taste_groups в meta
            $meta = $product->meta ?? [];
            $meta['taste_groups'] = $grouped;
            $product->meta = $meta;
            $product->save();
        });
    }

    /* ---------------- Парсинг wine_tastes ---------------- */
    protected static function parseWineTastes(?string $csv): array
    {
        if (empty($csv)) return [];

        $pairs = preg_split('/[;,]+/u', $csv);
        $result = [];

        foreach ($pairs as $pair) {
            $pair = trim($pair);
            if ($pair === '') continue;

            if (preg_match('/^(.+?)\s*[-–—]\s*(\d+)$/u', $pair, $m)) {
                $name = trim($m[1]);
                $score = (int)$m[2];
            } else {
                $name = trim($pair);
                $score = 1;
            }

            $value = min(1.0, $score / 10); // например, 9 → 0.9
            $norm = static::normalizeTasteKey($name);
            $result[$norm] = max($result[$norm] ?? 0, $value);
        }

        return $result;
    }

    /* ---------------- Дуб/Ваниль ---------------- */
    protected static function detectOakAgingMention(?string $text): bool
    {
        if (!$text) return false;
        $t = mb_strtolower($text);
        return Str::contains($t, [
            'выдерж', 'баррик', 'дубов', 'oak', 'barrique', 'barrel', 'aged in oak'
        ]);
    }

    protected static function applyOakVanillaRules(array $tags, bool $hasOak): array
    {
        $oakKey = static::normalizeTasteKey('дуб');
        $vanillaKey = static::normalizeTasteKey('ваниль');

        if ($hasOak) {
            $tags[$oakKey] = max($tags[$oakKey] ?? 0, 1.0);
            $tags[$vanillaKey] = max($tags[$vanillaKey] ?? 0, 0.8);
        } else {
            unset($tags[$oakKey], $tags[$vanillaKey]);
        }

        return $tags;
    }

    /* ---------------- Группировка ---------------- */
    protected static function groupAndNormalize(array $tags): array
    {
        if (empty($tags)) return [];

        $groups = [];
        foreach ($tags as $name => $w) {
            [$taste, $group] = static::firstOrCreateTasteWithGroup($name);
            $gName = $group->getTranslation('name', 'ru') ?? 'other';
            $groups[$gName]['_sum'] = ($groups[$gName]['_sum'] ?? 0) + $w;
            $groups[$gName]['items'][$taste->id] = [
                'name' => $taste->getTranslation('name', 'ru'),
                'weight' => $w,
            ];
        }

        if (empty($groups)) return [];

        $leader = max(array_column($groups, '_sum'));
        $out = [];

        foreach ($groups as $gName => $data) {
            $groupPct = $leader > 0 ? round($data['_sum'] * 100 / $leader, 1) : 0;
            $sumItems = array_sum(array_column($data['items'], 'weight'));

            $inner = [];
            foreach ($data['items'] as $tid => $row) {
                $inner[$tid] = [
                    'name' => $row['name'],
                    'percent' => $sumItems > 0 ? round($row['weight'] * 100 / $sumItems, 1) : 0,
                ];
            }

            uasort($inner, fn($a, $b) => $b['percent'] <=> $a['percent']);
            $out[$gName] = [
                'group_percent' => $groupPct,
                'items' => array_slice($inner, 0, 4, true),
            ];
        }

        uasort($out, fn($a, $b) => $b['group_percent'] <=> $a['group_percent']);
        return $out;
    }

    /* ---------------- Сохранение ---------------- */
    protected static function persistProductTastes(Product $product, array $tags): void
    {
        $max = max($tags);
        $sync = [];

        foreach ($tags as $name => $w) {
            [$taste] = static::firstOrCreateTasteWithGroup($name);
            $pct = $max > 0 ? round($w * 100 / $max, 1) : 0;
            $sync[$taste->id] = ['intensity_percent' => $pct];
        }

        // сортируем по интенсивности, чтобы top-4 были в начале
        arsort($sync);
        $product->tastes()->sync($sync);
    }

    /* ---------------- Taste helpers ---------------- */
    protected static array $tasteCache = [];

    protected static function firstOrCreateTasteWithGroup(string $name): array
    {
        $norm = static::normalizeTasteKey($name);

        if (isset(static::$tasteCache[$norm])) {
            return static::$tasteCache[$norm];
        }

        $groupSlug = \App\Helpers\TasteHelper::detectGroup($norm) ?? 'other';
        $groupSlug = mb_strtolower($groupSlug);

        $group = TasteGroup::firstOrCreate(
            ['name->en' => ucfirst($groupSlug)],
            [
                'name' => ['ru' => ucfirst($groupSlug), 'en' => ucfirst($groupSlug)],
                'slug' => Str::slug($groupSlug),
            ]
        );

        $taste = Taste::query()
            ->where(function ($q) use ($norm) {
                $q->whereRaw("LOWER(TRIM(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ru')))) = ?", [$norm])
                    ->orWhereRaw("LOWER(TRIM(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')))) = ?", [$norm]);
            })
            ->first();

        if (!$taste) {
            $taste = Taste::create([
                'name' => ['ru' => $norm, 'en' => $norm],
                'taste_group_id' => $group->id,
            ]);
        } elseif ($taste->taste_group_id !== $group->id) {
            $taste->update(['taste_group_id' => $group->id]);
        }

        return static::$tasteCache[$norm] = [$taste, $group];
    }


    protected static function normalizeTasteKey(string $s): string
    {
        $s = trim(mb_strtolower($s));
        return str_replace(['ё'], ['е'], $s);
    }
}
