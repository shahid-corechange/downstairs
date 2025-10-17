<?php

namespace Database\Seeders;

use App\Models\UnassignSubscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class UnassignSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment() !== 'testing') {
            $user = User::role('Customer')->first();
            UnassignSubscription::factory(10)
                ->forUser($user)
                ->create();
        }
    }
}
