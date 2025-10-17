<?php

namespace Database\Seeders;

use App\Enums\MembershipTypeEnum;
use App\Enums\User\UserNotificationMethodEnum;
use App\Http\Traits\UserSettingTrait;
use App\Models\Address;
use App\Models\Customer;
use App\Models\KeyPlace;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserCompaniesSeeder extends Seeder
{
    use UserSettingTrait;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $user = $this->createPrivateUser($i);
            $company = $this->createCompanyUser($i);

            $address = Address::factory()->create();

            $property = $this->createProperty($address, $company);
            $user->properties()->attach($property->id);

            $customer = $this->createCustomer($address, $company);
            $user->customers()->attach($customer->id);
        }
    }

    private function createCompanyUser($counter): User
    {
        $company = User::factory()->hasInfo(1)->create();
        $company->assignRole('Company');
        $company->email = "company{$counter}@email.com";
        $this->createDefaultSettings($company);
        $company->cellphone = null;
        $company->first_name = $company->first_name.' '.'AB';
        $company->last_name = '';
        $company->save();

        $company->info->update([
            'notification_method' => UserNotificationMethodEnum::Email(),
        ]);

        return $company;
    }

    private function createPrivateUser($counter): User
    {
        $user = User::factory()->hasInfo(1)->create();
        $user->assignRole('Customer');
        $user->email = "company_contact{$counter}@email.com";
        $this->createDefaultSettings($user);
        $user->cellphone = '467264132'.(55 + $counter);
        $user->save();

        return $user;
    }

    private function createProperty(Address $address, User $user): Property
    {
        $property = Property::factory()
            ->assignAddress($address->id)
            ->hasAttached($user)
            ->setMembershipType(MembershipTypeEnum::Company())
            ->create();
        $keyPlace = KeyPlace::whereNull('property_id')->inRandomOrder()->first();
        $keyPlace->update(['property_id' => $property->id]);
        $property->update([
            'property_type_id' => 3,
            'key_information' => [
                ...$property->key_information,
                'key_place' => $keyPlace->id,
            ],
        ]);

        return $property;
    }

    private function createCustomer(Address $address, User $user): Customer
    {
        $customer = Customer::factory()
            ->forUser($user, $address->id)
            ->hasAttached($user)
            ->create();
        $customer->fortnox_id = '13143';
        $customer->membership_type = MembershipTypeEnum::Company();
        $customer->save();

        return $customer;
    }
}
