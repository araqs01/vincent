<?php

namespace Database\Seeders;

use App\Models\Pairing;
use App\Models\PairingGroup;
use Illuminate\Database\Seeder;

class PairingSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/catalog/combinations.json');

        if (!file_exists($path)) {
            $this->command->error("❌ Файл не найден: $path");
            return;
        }

        $this->command->info("🍽 Импорт сочетаний и групп из combinations.json...");

        $data = json_decode(file_get_contents($path), true);
        if (!is_array($data)) {
            $this->command->error("⚠️ Ошибка JSON: неверный формат.");
            return;
        }

        $groupsCount = 0;
        $pairingsCount = 0;

        foreach ($data as $item) {
            // 🔹 Группа
            $groupData = $item['group'] ?? null;
            $group = null;

            if ($groupData && !empty($groupData['name'])) {
                $group = PairingGroup::updateOrCreate(
                    ['name->ru' => $groupData['name']],
                    [
                        'name' => [
                            'ru' => $groupData['name'],
                            'en' => $groupData['name_en'] ?? $groupData['name'],
                        ],
                    ]
                );
                $groupsCount++;
            }

            // 🔹 Основные данные сочетания
            $ru = trim($item['name'] ?? '');
            $en = trim($item['name_en'] ?? $ru);
            if (!$ru) continue;

            Pairing::updateOrCreate(
                ['name->ru' => $ru],
                [
                    'name' => [
                        'ru' => $ru,
                        'en' => $en,
                    ],
                    'description' => [
                        'ru' => $item['description'] ?? '',
                        'en' => $item['description_en'] ?? $item['description'] ?? '',
                    ],
                    'body' => [
                        'ru' => $item['body'] ?? '',
                        'en' => $item['body_en'] ?? $item['body'] ?? '',
                    ],
                    'pairing_group_id' => $group?->id,
                ]
            );

            $pairingsCount++;
        }

        $this->command->info("✅ Импорт завершён: групп — {$groupsCount}, сочетаний — {$pairingsCount}");
    }
}
