<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = $this->data();

        foreach ($data as $value) {
            $role = Role::firstOrCreate([
                'name' => $value['name'],
            ]);

            $permissions = null;

            switch ($value['name']) {
                case 'Superadmin':
                    // Full access
                    break;
                case 'Employee':
                    $permissions = [PermissionsEnum::AccessPortal()];
                    break;
                case 'Customer':
                    $permissions = [PermissionsEnum::AccessCustomerApp()];
                    break;
                case 'Worker':
                    $permissions = [PermissionsEnum::AccessEmployeeApp()];
                    break;
                default:
                    break;
            }

            if ($permissions) {
                foreach (config('permissions') as $permission) {
                    if (! in_array($permission['value'], $permissions) || ! isset($permission['requires'])) {
                        continue;
                    }

                    $permissions = array_unique(array_merge($permissions, $permission['requires']));
                }

                $role->syncPermissions($permissions);
            }
        }
    }

    public function data()
    {
        return [
            ['name' => 'Superadmin'],
            ['name' => 'Employee'],
            ['name' => 'Customer'],
            ['name' => 'Company'],
            ['name' => 'Worker'],
        ];
    }
}
