php artisan migrate:fresh --seed

php artisan shield:generate --all

sail php artisan tinker

        use Spatie\Permission\Models\Role;
        use Spatie\Permission\Models\Permission;
        $role = Role::where('name', 'Admin')->first();
        $role->givePermissionTo(Permission::all());

