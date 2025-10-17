<?php

namespace Database\Seeders;

use App\Models\LeaveRegistration;
use Illuminate\Database\Seeder;

class LeaveRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment() !== 'testing') {
            LeaveRegistration::factory(10)->create();
        }
    }
}
