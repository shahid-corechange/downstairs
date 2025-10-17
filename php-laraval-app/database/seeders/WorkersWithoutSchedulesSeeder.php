<?php

namespace Database\Seeders;

use App\Http\Traits\UserSettingTrait;
use App\Models\Address;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class WorkersWithoutSchedulesSeeder extends Seeder
{
    use UserSettingTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    private int $workerCounter = 21;

    public function run(): void
    {
        if (app()->environment() !== 'testing') {
            User::factory()->count(3)->hasInfo(1)->create()->each(function (User $user) {
                $phone = 665 + $this->workerCounter;
                $user->assignRole('Employee', 'Worker');
                $user->email = "worker{$this->workerCounter}@email.com";
                $user->cellphone = "46726416{$phone}";
                $this->createDefaultSettings($user);
                $user->save();
                $addresses = Address::factory()->count(1)->create();
                Employee::factory()->count(1)->forUser($user, $addresses[0]->id)->create();
                $this->workerCounter++;
            });
        }
    }
}
