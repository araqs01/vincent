<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚚 Добавляем поставщиков с диапазонами кодов...');

        $suppliers = [
            'A0001 - A0564',
            'K0001 - K1220',
            'L0001 - L0114',
            'M0001 - M0366',
            'N0001 - N1303',
            'O0001 - O1620',
            'P1.0001 - P1.0327',
            'P0001 - P0227',
            'Q0001 - Q2583',
            'R1.0001 - R1.0297',
            'S0001 - S0621',
            'B0001 - B5023',
            'T0001 - T0786',
            'C0001 - C1331',
            'D0001 - D0821',
            'E0001 - E1942',
            'F0001 - F2406',
            'G0001 - G1734',
            'H0001 - H1010',
            'I0001 - I1260'
        ];

        foreach ($suppliers as $name) {
            Supplier::firstOrCreate(
                ['name' => $name],
                [
                    'name' => ['ru' => $name, 'en' => $name],
                    'contact_info' => null,
                    'min_order' => null,
                    'delivery_time' => null,
                    'rating' => null,
                ]
            );
        }

        $this->command->info('✅ Добавлены поставщики: ' . implode(', ', $suppliers));
    }
}
