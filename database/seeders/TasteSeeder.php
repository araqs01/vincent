<?php

namespace Database\Seeders;

use App\Models\Taste;
use App\Models\TasteGroup;
use App\Models\TasteGroupSpirit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TasteSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/catalog/bouqets.json');

        if (!File::exists($path)) {
            $this->command->error("❌ Файл не найден: $path");
            return;
        }

        $data = json_decode(File::get($path), true);
        if (!is_array($data)) {
            $this->command->error("❌ Ошибка JSON в bouquets.json");
            return;
        }

        $this->command->info("🔄 Импорт вкусов и групп...");

        $count = 0;

        foreach ($data as $item) {
            $nameRu = trim($item['name'] ?? '');
            $nameEn = trim($item['name_en'] ?? '');
            $isSpirit = $item['is_spirit'] ?? false;

            if (!$nameRu) continue;

            /*
            |--------------------------------------------------------------------------
            | 1️⃣ TasteGroup — основная группа
            |--------------------------------------------------------------------------
            */
            $groupData = $item['group'] ?? null;
            $group = null;

            if ($groupData && !empty($groupData['name'])) {
                $slug = $groupData['name_en'] ?? Str::slug($groupData['name']);
                $group = TasteGroup::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => [
                            'ru' => $groupData['name'],
                            'en' => $groupData['name_en'] ?? $groupData['name'],
                        ],
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 2️⃣ TasteGroupSpirit — группа для крепких напитков
            |--------------------------------------------------------------------------
            */
            $groupSpiritData = $item['group_spirit'] ?? null;
            $groupSpirit = null;

            if ($groupSpiritData && !empty($groupSpiritData['name'])) {
                $slugSpirit = $groupSpiritData['name_en'] ?? Str::slug($groupSpiritData['name']);
                $groupSpirit = TasteGroupSpirit::updateOrCreate(
                    ['slug' => $slugSpirit],
                    [
                        'name' => [
                            'ru' => $groupSpiritData['name'],
                            'en' => $groupSpiritData['name_en'] ?? $groupSpiritData['name'],
                        ],
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 3️⃣ Taste — сам вкус
            |--------------------------------------------------------------------------
            */
            $taste = Taste::updateOrCreate(
                ['name->ru' => $nameRu],
                [
                    'name' => [
                        'ru' => $nameRu,
                        'en' => $nameEn ?: $nameRu,
                    ],
                    'taste_group_id' => $group?->id,
                    'taste_group_spirit_id' => $groupSpirit?->id,
                    'is_spirit' => $isSpirit,
                ]
            );

            $count++;
        }

        $this->command->info("✅ Импорт вкусов завершён. Добавлено/обновлено: {$count}");
    }
}
