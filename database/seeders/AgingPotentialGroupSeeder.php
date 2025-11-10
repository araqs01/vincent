<?php

namespace Database\Seeders;

use App\Models\AgingPotentialGroup;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AgingPotentialGroupSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/catalog/Потенциал выдержки.xlsx');

        if (!file_exists($path)) {
            $this->command->error("❌ Файл не найден: {$path}");
            return;
        }

        $sheet = IOFactory::load($path)->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $currentGroup = null;
        $count = 0;

        foreach ($rows as $index => $row) {
            $donor = trim($row['A'] ?? '');
            $our   = trim($row['B'] ?? '');

            // 🔹 Пропускаем заголовки
            if ($index <= 3) continue;

            // 🔹 Если в колонке A просто число — это новая группа
            if (is_numeric($donor) && $our === '') {
                $currentGroup = (int)$donor;
                continue;
            }

            // 🔹 Если это строка с диапазоном
            if (!$donor || !$our) continue;

            AgingPotentialGroup::create([
                'group_number'    => $currentGroup ?? 0,
                'donor_potential' => $donor,
                'our_potential'   => $our,
            ]);

            $count++;
        }

        $this->command->info("✅ Импортировано {$count} строк (Потенциал выдержки)");
    }
}
