<?php

namespace Database\Seeders;

use App\Models\WineTaste;
use App\Models\WineTasteGroup;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WineTasteSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/catalog/Вино - Шампанское - Вкусы.xlsx');

        if (!file_exists($path)) {
            $this->command->error("❌ Excel-файл не найден: {$path}");
            return;
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $groupMappings = [];
        $groupStartRow = null;

        // 🔹 Ищем нижнюю таблицу ("Вино - группа" в колонке B)
        foreach ($rows as $index => $row) {
            if (trim($row['B'] ?? '') === 'Вино - группа') {
                $groupStartRow = $index + 1;
                break;
            }
        }

        if (!$groupStartRow) {
            $this->command->error("⚠️ Не найдена нижняя таблица 'Вино - группа'");
            return;
        }

        // 🔹 1. Импорт групп
        $this->command->info("🔹 Импорт групп вкусов...");

        for ($i = $groupStartRow; $i <= count($rows); $i++) {
            $row = $rows[$i] ?? null;
            if (!$row) continue;

            $groupName = trim($row['B'] ?? '');
            $typeName = trim($row['C'] ?? '');
            $finalGroup = trim($row['D'] ?? '');
            $finalGroupEn = trim($row['E'] ?? '');

            if (!$groupName || !$typeName) continue;

            $group = WineTasteGroup::create([
                'name' => ['ru' => $groupName],
                'type' => ['ru' => $typeName],
                'final_group' => [
                    'ru' => $finalGroup ?: $finalGroupEn,
                    'en' => $finalGroupEn ?: $finalGroup,
                ],
                'meta' => [],
            ]);

            $groupMappings[$groupName] = $group->id;
        }

        // 🔹 Нормализация ключей
        $normalize = fn($v) => trim(mb_strtolower(
            preg_replace('/\s+/u', ' ', str_replace(["\xC2\xA0", 'ё'], [' ', 'е'], $v ?? ''))
        ));

        $normalizedGroups = collect(WineTasteGroup::all())->mapWithKeys(function ($group) use ($normalize) {
            $name = is_array($group->name) ? ($group->name['ru'] ?? reset($group->name)) : $group->name;
            $type = is_array($group->type) ? ($group->type['ru'] ?? reset($group->type)) : $group->type;

            $keys = [
                $normalize($name),
                $normalize($type),
            ];

            return collect($keys)->mapWithKeys(fn($k) => [$k => $group->id]);
        })->toArray();

        $this->command->info("✅ Группы импортированы: " . count($normalizedGroups));

        // 🔹 2. Импорт вкусов
        $this->command->info("🔹 Импорт вкусов...");

        foreach ($rows as $index => $row) {
            if (trim($row['B'] ?? '') === 'Вино - группа') break;

            $tasteRu = trim($row['B'] ?? '');
            $tasteEn = trim($row['C'] ?? '');
            $group1 = trim($row['D'] ?? '');
            $group2 = trim($row['F'] ?? '');
            $type = trim($row['H'] ?? '');
            $typeEn = trim($row['I'] ?? '');

            if (!$tasteRu) continue;

            $groupId = $this->detectGroupId($group1, $group2, $normalizedGroups);

            WineTaste::create([
                'group_id' => $groupId,
                'name' => ['ru' => $tasteRu, 'en' => $tasteEn ?: $tasteRu],
                'meta' => [
                    'group_1' => $group1,
                    'group_2' => $group2,
                    'type' => $type,
                    'type_en' => $typeEn,
                ],
            ]);
        }

        $this->command->info("🎉 Импорт завершён!");
    }

    /**
     * Универсальный поиск ID группы по Group1/Group2.
     */
    private function detectGroupId(?string $group1, ?string $group2, array $normalizedGroups): ?int
    {
        $normalize = fn($v) => trim(mb_strtolower(
            preg_replace('/\s+/u', ' ', str_replace(["\xC2\xA0", 'ё'], [' ', 'е'], $v ?? ''))
        ));

        $groupAliases = [
            'злаковые' => 'злаковое',
            'фруктовые' => 'фруктовое',
            'цветочные' => 'цветочное',
            'ягодные' => 'ягодное',
            'травяные' => 'травяное',
            'минеральные' => 'минеральное',
            'древесные' => 'древесное',
            'пряные' => 'пряное',
            'землистые' => 'землистое',
            'конфетные' => 'кондитерское',
            'спиртовой' => 'спирт',
        ];

        $g1 = $normalize($group1);
        $g2 = $normalize($group2);

        $g1 = $groupAliases[$g1] ?? $g1;
        $g2 = $groupAliases[$g2] ?? $g2;

        $groupId = $normalizedGroups[$g1] ?? ($normalizedGroups[$g2] ?? null);

        if (!$groupId) {
            foreach ($normalizedGroups as $key => $id) {
                if (str_contains($key, $g1) || str_contains($key, $g2)) {
                    $groupId = $id;
                    break;
                }
            }
        }

        if (!$groupId) {
            dump([
                '⚠️ Не найдено совпадение' => [
                    'Group1' => $group1,
                    'Group2' => $group2,
                    'Normalized1' => $g1,
                    'Normalized2' => $g2,
                ],
            ]);
        }

        return $groupId;
    }
}
