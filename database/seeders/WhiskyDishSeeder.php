<?php

namespace Database\Seeders;

use App\Models\WhiskyDish;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WhiskyDishSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/catalog/Виски - Блюда.xlsx');

        if (!file_exists($path)) {
            $this->command->error("❌ Файл не найден: {$path}");
            return;
        }

        $sheet = IOFactory::load($path)->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        unset($rows[1]);
        $count = 0;

        foreach ($rows as $i => $row) {
            $type = trim($row['A'] ?? '');
            $region = trim($row['B'] ?? '');
            if (!$type) continue;

            try {
                WhiskyDish::updateOrCreate(
                    ['type->ru' => $type, 'region->ru' => $region],
                    [
                        'type' => ['ru' => $type, 'en' => $type],
                        'region' => ['ru' => $region, 'en' => $region],

                        'sweetness' => $this->parseRange($row['C'] ?? ''),
                        'smokiness' => $this->parseRange($row['D'] ?? ''),
                        'fruitiness' => $this->parseRange($row['E'] ?? ''),
                        'strength' => $this->parseRange($row['F'] ?? ''),
                        'spiciness' => $this->parseRange($row['G'] ?? ''),
                        'astringency' => $this->parseRange($row['H'] ?? ''),
                        'body' => $this->parseRange($row['I'] ?? ''),
                        'age' => trim($row['J'] ?? ''),

                        'tags' => $this->parseList($row['K'] ?? ''),
                        'snacks' => $this->parseList($row['L'] ?? ''),
                    ]
                );
                $count++;
            } catch (\Throwable $e) {
                dump("Ошибка на строке {$i}: " . $e->getMessage());
            }
        }

        $this->command->info("✅ Импортировано {$count} строк из Виски – Блюда");
    }

    private function parseRange(?string $value): ?array
    {
        $value = trim((string)$value);
        if ($value === '') return null;

        $value = str_replace(',', '.', $value);

        // 2-5 → {"min":2,"max":5}
        if (preg_match('/^(\d+(?:\.\d+)?)[\s-]+(\d+(?:\.\d+)?)/', $value, $m)) {
            return ['min' => (float)$m[1], 'max' => (float)$m[2]];
        }

        // 3+ → {"min":3,"max":null}
        if (preg_match('/^(\d+(?:\.\d+)?)\+$/', $value, $m)) {
            return ['min' => (float)$m[1], 'max' => null];
        }

        // одиночное значение
        return ['min' => (float)$value, 'max' => (float)$value];
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
