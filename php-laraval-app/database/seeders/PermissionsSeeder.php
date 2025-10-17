<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $date = now();
        $permissions = array_map(function ($permission) use ($date) {
            return [
                'name' => $permission['value'],
                'guard_name' => 'web',
                'created_at' => $date,
                'updated_at' => $date,
            ];
        }, config('permissions'));

        Permission::insert($permissions);
    }
}
