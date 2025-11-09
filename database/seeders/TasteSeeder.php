<?php

namespace Database\Seeders;

use App\Models\Taste;
use App\Models\TasteGroup;
use App\Models\TasteGroupSpirit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class TasteSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/catalog/bouqets.json');
        $zipPath = database_path('seeders/catalog/images_bouqets.zip');
        $tempDir = storage_path('app/tmp_bouquets_images');

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
        }

        // 📖 Читаем JSON
        $data = json_decode(File::get($jsonPath), true);
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
            $group = null;
            $groupData = $item['group'] ?? null;
            if ($groupData && !empty($groupData['name'])) {
                $slug = Str::slug(mb_strtolower($groupData['name_en'] ?? $groupData['name']));
                $group = TasteGroup::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => [
                            'ru' => $groupData['name'],
                            'en' => $groupData['name_en'] ?? $groupData['name'],
                        ],
                    ]
                );

                // 🖼 Добавляем изображение группы
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

            /*
            |--------------------------------------------------------------------------
            | 2️⃣ TasteGroupSpirit — группа для крепких напитков
            |--------------------------------------------------------------------------
            */
            $groupSpirit = null;
            $groupSpiritData = $item['group_spirit'] ?? null;
            if ($groupSpiritData && !empty($groupSpiritData['name'])) {
                $slugSpirit = Str::slug(mb_strtolower($groupSpiritData['name_en'] ?? $groupSpiritData['name']));
                $groupSpirit = TasteGroupSpirit::updateOrCreate(
                    ['slug' => $slugSpirit],
                    [
                        'name' => [
                            'ru' => $groupSpiritData['name'],
                            'en' => $groupSpiritData['name_en'] ?? $groupSpiritData['name'],
                        ],
                    ]
                );

                // 🖼 Добавляем изображение группы для спиртов
                if (!empty($groupSpiritData['image'])) {
                    $spiritImagePath = $this->resolveImagePath($tempDir, $groupSpiritData['image']);
                    if ($spiritImagePath && file_exists($spiritImagePath)) {
                        $groupSpirit->clearMediaCollection('hero_image');
                        $media = $groupSpirit->addMedia($spiritImagePath)
                            ->preservingOriginal()
                            ->toMediaCollection('hero_image');

                        if ($media && empty($media->uuid)) {
                            $media->uuid = (string) Str::uuid();
                            $media->save();
                        }

                        $this->command->info("🍸 Добавлено изображение к группе спиртов: {$groupSpiritData['name']}");
                    } else {
                        $this->command->warn("⚠️ Файл изображения группы спиртов не найден: {$groupSpiritData['image']}");
                    }
                }
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

            // 🖼 Добавляем изображение к самому вкусу
            if (!empty($item['image'])) {
                $tasteImagePath = $this->resolveImagePath($tempDir, $item['image']);
                if ($tasteImagePath && file_exists($tasteImagePath)) {
                    $taste->clearMediaCollection('hero_image');
                    $media = $taste->addMedia($tasteImagePath)
                        ->preservingOriginal()
                        ->toMediaCollection('hero_image');

                    if ($media && empty($media->uuid)) {
                        $media->uuid = (string) Str::uuid();
                        $media->save();
                    }

                    $this->command->info("🖼 Добавлено изображение к вкусу: {$nameRu}");
                } else {
                    $this->command->warn("⚠️ Файл изображения вкуса не найден: {$item['image']}");
                }
            }

            $count++;
        }

        // 🧹 Удаляем временную директорию
        if (File::exists($tempDir)) {
            File::deleteDirectory($tempDir);
            $this->command->info("🧹 Временная папка удалена: {$tempDir}");
        }

        $this->command->info("✅ Импорт вкусов завершён. Добавлено/обновлено: {$count}");
    }

    /**
     * 🔍 Преобразует путь из JSON ("images/bouquets/apricot-1.jpg")
     *      в реальный путь в распакованной папке.
     */
    private function resolveImagePath(string $tempDir, string $jsonPath): ?string
    {
        $relative = ltrim($jsonPath, '/');
        $path1 = $tempDir . DIRECTORY_SEPARATOR . $relative;
        $path2 = $tempDir . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $relative;

        if (file_exists($path1)) return $path1;
        if (file_exists($path2)) return $path2;

        return null;
    }
}

