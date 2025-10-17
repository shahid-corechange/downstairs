<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Team with 2 workers
        Team::factory(2)
            ->create()
            ->each(function ($team) {
                $users = User::permission(PermissionsEnum::AccessEmployeeApp())
                    ->get()->random(2);
                $team->users()->attach($users);
            });

        // Team with 3 workers
        Team::factory(2)
            ->create()
            ->each(function ($team) {
                $users = User::permission(PermissionsEnum::AccessEmployeeApp())
                    ->get()->random(3);
                $team->users()->attach($users);
            });
    }
}
