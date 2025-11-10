<?php

namespace Database\Seeders;

use App\Models\WhiskyBase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WhiskyBaseSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/catalog/Виски - Базовая таблица.xlsx');

        if (!file_exists($path)) {
            $this->command->error("❌ Excel-файл не найден: {$path}");
            return;
        }

        $this->command->info("🥃 Импорт виски из '{$path}' ...");

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        unset($rows[1]); // пропускаем заголовки
        $count = 0;

        foreach ($rows as $index => $row) {
            try {
                $name = trim($row['A'] ?? '');
                $manufacturer = trim($row['B'] ?? '');
                $isBlended = $this->parseBoolean($row['C'] ?? '');
                $sweetness = $this->parseFloat($row['D'] ?? null);
                $smoky = $this->parseFloat($row['E'] ?? null);
                $fruity = $this->parseFloat($row['F'] ?? null);
                $spicy = $this->parseFloat($row['G'] ?? null);
                $floral = $this->parseFloat($row['H'] ?? null);
                $woody = $this->parseFloat($row['I'] ?? null);
                $grainy = $this->parseFloat($row['J'] ?? null);
                $creamy = $this->parseFloat($row['K'] ?? null);
                $sulphury = $this->parseFloat($row['L'] ?? null);
                $smooth = $this->parseFloat($row['M'] ?? null);
                $finishLength = $this->parseFloat($row['N'] ?? null);
                $bitterness = $this->parseFloat($row['O'] ?? null);
                $dryness = $this->parseFloat($row['P'] ?? null);
                $body = $this->parseFloat($row['Q'] ?? null);
                $country = trim($row['R'] ?? '');
                $forCigar = $this->parseBoolean($row['S'] ?? '');
                $blendIncluded = $this->parseJsonList($row['T'] ?? '');
                $blendWith = $this->parseJsonList($row['U'] ?? '');
                $awards = $this->parseJsonList($row['V'] ?? '');
                $aroma = trim($row['W'] ?? '');
                $taste = trim($row['X'] ?? '');
                $aftertaste = trim($row['Y'] ?? '');

                if (!$name) continue;

                WhiskyBase::updateOrCreate(
                    ['name->ru' => $name],
                    [
                        'name' => ['ru' => $name, 'en' => $name],
                        'manufacturer' => ['ru' => $manufacturer, 'en' => $manufacturer],
                        'is_blended' => $isBlended,
                        'sweetness' => $sweetness,
                        'smoky' => $smoky,
                        'fruity' => $fruity,
                        'spicy' => $spicy,
                        'floral' => $floral,
                        'woody' => $woody,
                        'grainy' => $grainy,
                        'creamy' => $creamy,
                        'sulphury' => $sulphury,
                        'smooth' => $smooth,
                        'finish_length' => $finishLength,
                        'bitterness' => $bitterness,
                        'dryness' => $dryness,
                        'body' => $body,
                        'country' => ['ru' => $country, 'en' => $country],
                        'for_cigar' => $forCigar,
                        'blend_included' => $blendIncluded,
                        'blend_with' => $blendWith,
                        'awards' => $awards,
                        'aroma' => $aroma,
                        'taste' => $taste,
                        'aftertaste' => $aftertaste,
                    ]
                );

                $count++;
            } catch (\Throwable $e) {
                Log::error("💥 Ошибка в строке {$index}: " . $e->getMessage());
            }
        }

        $this->command->info("✅ Импорт завершён. Добавлено или обновлено: {$count} записей.");
    }

    private function parseFloat($value): ?float
    {
        if ($value === null || $value === '') return null;
        $value = str_replace(',', '.', trim((string)$value));
        return is_numeric($value) ? (float)$value : null;
    }

    private function parseBoolean($value): bool
    {
        $value = mb_strtolower(trim((string)$value));
        return in_array($value, ['1', 'yes', 'да', 'true', '+'], true);
    }

    private function parseJsonList($value): array
    {
        if (!$value) return [];
        $items = preg_split('/[,;]+/u', $value);
        return collect($items)
            ->map(fn($v) => trim($v))
            ->filter()
            ->values()
            ->toArray();
    }
}
