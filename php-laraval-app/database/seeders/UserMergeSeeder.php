<?php

namespace Database\Seeders;

use App\Enums\User\UserStatusEnum;
use App\Http\Traits\UserSettingTrait;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Property;
use App\Models\Team;
use App\Models\User;
use Hash;
use Illuminate\Database\Seeder;

class UserMergeSeeder extends Seeder
{
    use UserSettingTrait;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->data1();
        $this->data2();
        $this->data3();
        $this->data4();
        $this->updatCellphone();
    }

    private function data1()
    {
        $data = [
            'first_name' => 'Patrick',
            'last_name' => 'Bågendahl',
            'email' => 'patrick.bagendahl@gmail.com',
            'cellphone' => '46703181848',
        ];

        $user = User::where('email', $data['email'])
            ->orWhere('cellphone', $data['cellphone'])->first();
        if ($user) {
            $user->assignRole('Superadmin');
            $customer = $user->customers()->first();
            Employee::factory()->forUser($user, $customer->address->id)->create();
        }
    }

    private function data2()
    {
        $data = [
            'first_name' => 'Magnus',
            'last_name' => 'Bernåker',
            'email' => 'magnus.bernaker@downstairs.se',
            'cellphone' => '46706939492',
        ];

        $user = User::where('email', $data['email'])
            ->orWhere('cellphone', $data['cellphone'])->first();

        if ($user) {
            $user->update($data);
            $user->assignRole('Superadmin');
        }
    }

    private function data3()
    {
        $data = [
            'first_name' => 'Sebastian',
            'last_name' => 'Strandberg',
            'email' => 'sebastian@duadigital.com',
            'cellphone' => '46726413250',
            'identity_number' => '198610154950',
            'password' => Hash::make('Seb123'),
            'status' => UserStatusEnum::Active(),
            'email_verified_at' => now(),
            'identity_number_verified_at' => now(),
        ];

        $user = User::create($data);
        $user->info()->create(
            [
                'avatar' => null,
                'language' => 'en_US',
                'timezone' => 'Europe/Stockholm',
                'currency' => 'SEK',
                'marketing' => 0,
            ]
        );
        $user->assignRole(['Superadmin', 'Customer']);
        $address = Address::create([
            'city_id' => 55871,
            'address' => 'Diagnosvägen 11',
            'postal_code' => '41685',
            'area' => null,
            'latitude' => 57.7143915,
            'longitude' => 11.994737,
        ]);
        Property::factory()->assignAddress($address->id)->hasAttached($user)->create();
        Customer::factory()->forUser($user, $address->id)->hasAttached($user)->create();
        Employee::factory()->forUser($user, $address->id)->create();
        $team = Team::factory(1)->create();
        $team[0]->users()->attach($user);
    }

    private function data4()
    {
        $data = [
            'first_name' => 'Miles',
            'last_name' => 'Morales',
            'email' => 'miles@example.com',
            'cellphone' => '46726413233',
            'identity_number' => generate_swedish_ssn(),
            'password' => Hash::make('password'),
            'status' => UserStatusEnum::Active(),
            'email_verified_at' => now(),
            'identity_number_verified_at' => now(),
        ];

        $user = User::create($data);
        $user->info()->create(
            [
                'avatar' => null,
                'language' => 'en_US',
                'timezone' => 'Europe/Stockholm',
                'currency' => 'SEK',
                'marketing' => 0,
            ]
        );
        $user->assignRole(['Superadmin', 'Customer']);
        $address = Address::create([
            'city_id' => 55871,
            'address' => 'Diagnosvägen 11',
            'postal_code' => '41685',
            'area' => null,
            'latitude' => 57.7143915,
            'longitude' => 11.994737,
        ]);
        Property::factory()->assignAddress($address->id)->hasAttached($user)->create();
        Customer::factory()->forUser($user, $address->id)->hasAttached($user)->create();
        Employee::factory()->forUser($user, $address->id)->create();
        $team = Team::factory(1)->create();
        $team[0]->users()->attach($user);
    }

    private function updatCellphone()
    {
        $users = [

            [
                'email' => 'peter@bernaker.com',
                'cellphone' => '46707603891',
            ],

            [
                'email' => 'iyad@downstairs.se',
                'cellphone' => '46733220332',
            ],
        ];

        foreach ($users as $user) {
            $user = User::where('email', $user['email'])->first();

            if ($user) {
                $user->update(['cellphone' => $user['cellphone']]);
            }
        }
    }
}
