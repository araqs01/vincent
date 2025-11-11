<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CategoriesAndMenuSeeder::class,
            PairingSeeder::class,
            TasteSeeder::class,
            RegionSeeder::class,
            GrapeSeeder::class,
            WineDishSeeder::class,
            ShieldSeeder::class,
            WhiskyTasteGroupSeeder::class,
            WhiskyTasteSeeder::class,
            BeerTasteSeeder::class,
            WhiskyBaseSeeder::class,
            WhiskyDishSeeder::class,
            StrongDrinkDishSeeder::class,
            AgingPotentialGroupSeeder::class,
            SommelierSeeder::class,
            SupplierSeeder::class
        ]);
    }
}
