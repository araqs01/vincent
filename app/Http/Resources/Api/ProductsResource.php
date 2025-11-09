<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\TasteGroup;

class ProductsResource extends JsonResource
{
    public function toArray($request)
    {
        $meta = $this->meta ?? [];

        // 🔹 Получаем taste_groups (ограничим 4 лидирующими)
        $tasteGroups = collect($meta['taste_groups'] ?? [])
            ->sortByDesc(fn($v, $k) => $v)
            ->take(4);

        // 🔹 Загружаем все TasteGroup одним запросом
        $allGroups = TasteGroup::with('media')->get()->keyBy('slug');

        // 🔹 Строим taste_group_icons с URL, названием и процентом
        $tasteGroupIcons = $tasteGroups->mapWithKeys(function ($value, $slug) use ($allGroups) {
            $group = $allGroups->get($slug);
            return [
                $slug => [
                    'icon' => $group?->getFirstMediaUrl('hero_image') ?: null,
                    'name' => $group?->name ?? ucfirst(str_replace('-', ' ', $slug)),
                    'percent' => $value,
                ],
            ];
        });

        // 🔹 Taste scales (всегда 5 шкал, даже если 0)
        $defaultScales = [
            'Фруктовость' => 0,
            'Сладость' => 0,
            'Полнотелость' => 0,
            'Танинность' => 0,
            'Кислотность' => 0,
        ];

        $tasteScales = array_merge($defaultScales, $meta['taste_scales'] ?? []);
        $tasteScales = array_intersect_key($tasteScales, $defaultScales); // только нужные 5

        // 🧴 Объёмы и цены (из variants)
        $volumes = $this->whenLoaded('variants', function () {
            return $this->variants
                ->filter(fn($v) => $v->volume)
                ->map(fn($v) => [
                    'volume' => rtrim($v->volume, 'л'),
                    'price' => $v->final_price ?? $v->price ?? null,
                ])
                ->values();
        });

        return [
            'id'           => $this->id,
            'slug'         => $this->slug,
            'name'         => $this->name,
            'short_specs' => $this->short_specs,
            'full_specs' => $this->full_specs,
            'image'        => $this->getFirstMediaUrl('images') ?: null,
            'price'        => $this->price,
            'final_price'  => $this->final_price,
            'has_discount' => $this->hasDiscount(),
            'volumes' => $volumes,
            // 🍇 Основные связи

            'category' => $this->whenLoaded('category', fn() => $this->category->name),

            'brand' => $this->whenLoaded('brand', fn() => $this->brand->name),
            'region' => $this->whenLoaded('region', fn() => $this->region->name),

            // 🍇 Грозди
            'grapes' => $this->whenLoaded('grapes', fn() =>
            $this->grapes->pluck('name')->join(', ')
            ),

            // ⭐ Рейтинг
            'vivino_rating'        => $meta['vivino_rating'] ?? null,
            'manufacturer_rating'  => $meta['manufacturer_rating'] ?? null,

            // 🍷 Вкусовые группы (топ-4)
            'taste_groups'      => $tasteGroups,
            'taste_group_icons' => $tasteGroupIcons,

            // 📊 Вкусовые шкалы
            'taste_scales' => $tasteScales,
        ];
    }
}
