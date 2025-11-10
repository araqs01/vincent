<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ Сначала создаём базовые роли
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $managerRole = Role::firstOrCreate(['name' => 'Manager']);

        $user = User::first();
        if ($user && !$user->hasRole('Admin')) {
            $user->assignRole($adminRole);
        }

        // 5️⃣ Выведем лог в консоль
        $this->command->info('✅ ShieldSeeder выполнен: роли и права созданы.');
    }
}
