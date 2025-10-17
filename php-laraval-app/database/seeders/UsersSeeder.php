<?php

namespace Database\Seeders;

use App\Enums\MembershipTypeEnum;
use App\Http\Traits\UserSettingTrait;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\KeyPlace;
use App\Models\Property;
use App\Models\User;
use Faker\Generator;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    use UserSettingTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    private int $workerCounter = 1;

    private int $customerCounter = 1;

    public function run(Generator $faker): void
    {
        // Create Superadmin user first
        $superadmin = User::factory()->hasInfo(1)->create([
            'email' => 'admin@downstairs.se',
            'cellphone' => '46703181848',
        ]);
        $superadmin->assignRole('Superadmin');
        $this->createDefaultSettings($superadmin);

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

        User::factory()->count(2)->hasInfo(1)->create()->each(function (User $user) {
            $phone = 774 + $this->customerCounter;
            $user->assignRole('Customer');
            $user->email = "customer{$this->customerCounter}@email.com";
            $this->createDefaultSettings($user);
            $user->cellphone = "46726416{$phone}";
            $user->save();
            $addresses = Address::factory()->count(2)->create();

            foreach ($addresses as $address) {
                Property::factory()
                    ->count(1)
                    ->assignAddress($address->id)
                    ->setMembershipType(MembershipTypeEnum::Private())
                    ->hasAttached($user)
                    ->create()
                    ->each(function (Property $property) {
                        $keyPlace = KeyPlace::whereNull('property_id')->inRandomOrder()->first();
                        $keyPlace->update(['property_id' => $property->id]);
                        $property->update(['key_information' => ['key_place' => $keyPlace->id]]);
                    });
                Customer::factory()
                    ->count(1)
                    ->forUser($user, $address->id)
                    ->hasAttached($user)
                    ->create();
            }

            $this->customerCounter++;
        });
    }
}
