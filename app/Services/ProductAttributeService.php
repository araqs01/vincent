<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Support\Str;

class ProductAttributeService
{
    public static function extractAndAttachAttributes($product, string $name): void
    {
        $normalized = mb_strtolower($name);

        $colorMap = [
            'белое' => 'Белое',
            'розовое' => 'Розовое',
            'красное' => 'Красное',
        ];

        $sweetnessMap = [
            'экстра брют' => 'Экстра Брют',
            'брют' => 'Брют',
            'сухое' => 'Сухое',
            'полусухое' => 'Полусухое',
            'полусладкое' => 'Полусладкое',
            'сладкое' => 'Сладкое',
        ];

        $attributeValueIds = [];

        // 🎨 Цвет вина
        foreach ($colorMap as $key => $value) {
            if (Str::contains($normalized, $key)) {
                $attributeValueIds[] = self::getOrCreateValue('Цвет вина', $value)->id;
            }
        }

        // 🍬 Тип / сладость
        foreach ($sweetnessMap as $key => $value) {
            if (Str::contains($normalized, $key)) {
                $attributeValueIds[] = self::getOrCreateValue('Тип (сахар)', $value)->id;
            }
        }

        // 🔗 Привязка
//        if (!empty($attributeValueIds)) {
//            $product->attributeValues()->syncWithoutDetaching($attributeValueIds);
//        }
        if (!empty($attributeValueIds)) {
            foreach ($attributeValueIds as $id) {
                // если уже есть связь — пропускаем
                if (!$product->attributeValues()->where('attribute_value_id', $id)->exists()) {
                    $product->attributeValues()->attach($id);
                }
            }
        }

    }

    protected static function getOrCreateValue(string $attributeName, string $value): AttributeValue
    {
        // 🔹 Найдём или создадим атрибут
        $attribute = Attribute::firstOrCreate(
            ['name->ru' => $attributeName],
            [
                'name' => ['ru' => $attributeName, 'en' => $attributeName],
                'slug' => Str::slug($attributeName),
                'is_filterable' => true,
                'is_visible' => true,
            ]
        );

        // 🔹 Найдём существующее значение (по JSON)
        $valueModel = AttributeValue::where('attribute_id', $attribute->id)
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(value, '$.ru')) = ?", [$value])
            ->first();

        // 🔹 Если не найдено — создаём
        if (!$valueModel) {
            $valueModel = AttributeValue::create([
                'attribute_id' => $attribute->id,
                'value' => ['ru' => $value, 'en' => $value],
                'slug' => Str::slug($value),
            ]);
        }

        return $valueModel;
    }

}
