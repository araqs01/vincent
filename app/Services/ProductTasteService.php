<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Taste;
use App\Models\TasteGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductTasteService
{
    public static function buildAndAttachTastes(
        Product $product,
        ?string $textTastesCsv,
        ?string $descriptionRu,
        bool $hasOakByFilters = false
    ): void {
        DB::transaction(function () use ($product, $textTastesCsv, $descriptionRu, $hasOakByFilters) {
            // 1️⃣ Извлекаем уже прикрепленные вкусы (например, от сортов винограда)
            $existing = $product->tastes()
                ->pluck('product_taste.intensity_percent', 'tastes.id')
                ->toArray();

            // 2️⃣ Парсим новые вкусы из Vivino / таблицы
            $parsed = static::parseWineTastes($textTastesCsv);

            // 3️⃣ Объединяем два источника
            $combined = static::mergeTasteSources($existing, $parsed);

            if (empty($combined)) {
                $product->tastes()->detach();
                return;
            }

            // 4️⃣ Проверяем выдержку в дубе
            $hasOakText = static::detectOakAgingMention($descriptionRu);
            $hasOak = $hasOakByFilters || $hasOakText;
            $combined = static::applyOakVanillaRules($combined, $hasOak);

            // 5️⃣ Группируем и нормируем
            $grouped = static::groupAndNormalize($combined);

            // 6️⃣ Сохраняем вкусы в product_taste
            static::persistProductTastes($product, $combined);

            // 7️⃣ Обновляем meta.taste_groups
            $meta = $product->meta ?? [];
            $meta['taste_groups'] = $grouped;
            $product->meta = $meta;
            $product->save();
        });
    }

    /* ------------------------- Парсинг CSV ------------------------- */
    protected static function parseWineTastes(?string $csv): array
    {
        if (empty($csv)) return [];

        $pairs = preg_split('/[;,]+/u', $csv);
        $names = collect($pairs)->map(fn($v) => trim($v))->filter()->values();

        $n = $names->count();
        if ($n === 0) return [];

        $step = 1 / $n;
        $value = 1.0;
        $result = [];

        foreach ($names as $name) {
            $norm = static::normalizeTasteKey($name);
            $result[$norm] = round($value, 2);
            $value -= $step;
        }

        return $result;
    }

    /* ------------------------- Объединение источников ------------------------- */
    protected static function mergeTasteSources(array $existing, array $parsed): array
    {
        $merged = [];

        // Сначала добавляем уже существующие вкусы (например, от винограда)
        foreach ($existing as $id => $pct) {
            $taste = Taste::find($id);
            if ($taste) {
                $key = static::normalizeTasteKey($taste->getTranslation('name', 'ru'));
                $merged[$key] = max($merged[$key] ?? 0, abs($pct / 100));
            }
        }

        // Затем добавляем новые (Vivino / текст)
        foreach ($parsed as $key => $val) {
            $merged[$key] = max($merged[$key] ?? 0, $val);
        }

        return $merged;
    }

    /* ------------------------- Дуб / ваниль ------------------------- */
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

    /* ------------------------- Группировка ------------------------- */
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

    /* ------------------------- Сохранение ------------------------- */
    protected static function persistProductTastes(Product $product, array $tags): void
    {
        $max = max($tags);
        $sync = [];

        foreach ($tags as $name => $w) {
            $w = abs($w); // 👈 гарантирует только положительные значения
            [$taste] = static::firstOrCreateTasteWithGroup($name);
            $pct = $max > 0 ? round($w * 100 / $max, 1) : 0;
            $sync[$taste->id] = ['intensity_percent' => $pct];
        }

        // вместо полного sync — мягкое обновление (не удаляет старые связи)
        $product->tastes()->syncWithoutDetaching($sync);
    }

    /* ------------------------- Taste helpers ------------------------- */
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
            ['slug' => Str::slug($groupSlug)],
            ['name' => ['ru' => ucfirst($groupSlug), 'en' => ucfirst($groupSlug)]]
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
