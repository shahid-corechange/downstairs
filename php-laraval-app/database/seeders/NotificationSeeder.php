<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $numberOfInstances = app()->environment() !== 'testing' ? 40 : 5;
        $users = User::permission(PermissionsEnum::AccessCustomerApp(), PermissionsEnum::AccessEmployeeApp())->get();

        foreach ($users as $user) {
            Notification::factory($numberOfInstances)->forUser($user)->create();
        }
    }
}
