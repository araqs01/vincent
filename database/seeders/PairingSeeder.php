<?php

namespace Database\Seeders;

use App\Models\Pairing;
use App\Models\PairingGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class PairingSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/catalog/combinations.json');
        $zipPath = database_path('seeders/catalog/images.zip');
        $tempDir = storage_path('app/tmp_combinations_images');

        if (!file_exists($jsonPath)) {
            $this->command->error("❌ Файл не найден: $jsonPath");
            return;
        }

        // 🗜 Распаковка ZIP с изображениями
        if (file_exists($zipPath)) {
            $this->command->info("🗜 Распаковка images.zip...");
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            File::makeDirectory($tempDir, 0775, true);

            $zip = new ZipArchive();
            if ($zip->open($zipPath) === true) {
                $zip->extractTo($tempDir);
                $zip->close();
                $this->command->info("📦 Изображения успешно распакованы во временную папку: {$tempDir}");
            } else {
                $this->command->warn("⚠️ Не удалось распаковать ZIP: {$zipPath}");
            }
        } else {
            $this->command->warn("⚠️ ZIP-файл с изображениями не найден: {$zipPath}");
        }

        // 📖 Читаем JSON
        $data = json_decode(file_get_contents($jsonPath), true);
        if (!is_array($data)) {
            $this->command->error("⚠️ Ошибка JSON: неверный формат.");
            return;
        }

        $groupsCount = 0;
        $pairingsCount = 0;

        foreach ($data as $item) {
            $groupData = $item['group'] ?? null;
            $group = null;

            // 🔹 Импорт группы сочетаний
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

                // 🖼 Добавляем изображение группы по JSON-пути
                if (!empty($groupData['image'])) {
                    $groupImagePath = $this->resolveImagePath($tempDir, $groupData['image']);
                    if ($groupImagePath && file_exists($groupImagePath)) {
                        $group->clearMediaCollection('hero_image');
                        $media = $group->addMedia($groupImagePath)
                            ->preservingOriginal()
                            ->toMediaCollection('hero_image');

                        if ($media && empty($media->uuid)) {
                            $media->uuid = (string) Str::uuid();
                            $media->save();
                        }

                        $this->command->info("📸 Добавлено изображение к группе: {$groupData['name']}");
                    } else {
                        $this->command->warn("⚠️ Файл изображения группы не найден: {$groupData['image']}");
                    }
                }
            }

            // 🔹 Импорт самого сочетания
            $ru = trim($item['name'] ?? '');
            $en = trim($item['name_en'] ?? $ru);
            if (!$ru) continue;

            $pairing = Pairing::updateOrCreate(
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

            // 🖼 Добавляем изображение сочетания по JSON-пути
            if (!empty($item['image'])) {
                $pairingImagePath = $this->resolveImagePath($tempDir, $item['image']);
                if ($pairingImagePath && file_exists($pairingImagePath)) {
                    $pairing->clearMediaCollection('hero_image');
                    $media = $pairing->addMedia($pairingImagePath)
                        ->preservingOriginal()
                        ->toMediaCollection('hero_image');

                    if ($media && empty($media->uuid)) {
                        $media->uuid = (string) Str::uuid();
                        $media->save();
                    }

                    $this->command->info("🖼 Добавлено изображение к сочетанию: {$ru}");
                } else {
                    $this->command->warn("⚠️ Файл изображения сочетания не найден: {$item['image']}");
                }
            }
        }

        // 🧹 Очистка временной директории
        if (File::exists($tempDir)) {
            File::deleteDirectory($tempDir);
            $this->command->info("🧹 Временная папка удалена: {$tempDir}");
        }

        $this->command->info("✅ Импорт завершён: групп — {$groupsCount}, сочетаний — {$pairingsCount}");
    }

    /**
     * 🔍 Преобразует путь из JSON ("images/combinations/ryba-2.jpg")
     *      в реальный путь в распакованной папке.
     */
    private function resolveImagePath(string $tempDir, string $jsonPath): ?string
    {
        // Убираем ведущие слэши
        $relative = ltrim($jsonPath, '/');

        // Варианты путей — с и без вложенной "images"
        $path1 = $tempDir . DIRECTORY_SEPARATOR . $relative;
        $path2 = $tempDir . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $relative;

        if (file_exists($path1)) return $path1;
        if (file_exists($path2)) return $path2;

        return null;
    }

}
