<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view products', 'create products', 'edit products', 'delete products',
            'view categories', 'edit categories',
            'view suppliers', 'edit suppliers',
            'view users', 'edit users',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        echo "✅ Permissions created" . PHP_EOL;
    }
}
