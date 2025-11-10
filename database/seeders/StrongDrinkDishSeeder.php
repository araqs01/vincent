<?php

namespace Database\Seeders;

use App\Models\StrongDrinkDish;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StrongDrinkDishSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/catalog/Крепкие напитки - Пиво - блюда.xlsx');

        if (!file_exists($path)) {
            $this->command->error("❌ Файл не найден: {$path}");
            return;
        }

        $sheet = IOFactory::load($path)->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);


        $currentType = null;
        $count = 0;

        foreach ($rows as $row) {
            $type = trim($row['A'] ?? '');
            $age = trim($row['B'] ?? '');
            $class = trim($row['C'] ?? '');
            $tasteTags = trim($row['D'] ?? '');
            $strength = trim($row['E'] ?? '');
            $drinkType = trim($row['F'] ?? '');
            $dish = trim($row['J'] ?? ''); // колонка блюд

            if ($type !== '') {
                $currentType = $type; // новый напиток
                continue;
            }

            if (!$currentType || !$dish) continue;

            StrongDrinkDish::create([
                'type' => ['ru' => $currentType, 'en' => $currentType],
                'age' => $age ?: null,
                'class' => $class ?: null,
                'taste_tags' => $this->parseList($tasteTags),
                'strength' => $strength ?: null,
                'drink_type' => $drinkType ?: null,
                'dishes' => $this->parseList($dish),
            ]);

            $count++;
        }

        $this->command->info("✅ Импортировано {$count} строк из {$path}");
    }

    private function parseList(?string $value): array
    {
        return collect(preg_split('/[,;]+/u', (string)$value))
            ->map(fn($v) => trim($v))
            ->filter()
            ->values()
            ->toArray();
    }
}
