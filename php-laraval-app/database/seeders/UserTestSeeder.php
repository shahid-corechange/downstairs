<?php

namespace Database\Seeders;

use App\Enums\MembershipTypeEnum;
use App\Http\Traits\UserSettingTrait;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\KeyPlace;
use App\Models\Product;
use App\Models\Property;
use App\Models\Subscription;
use App\Models\SubscriptionProduct;
use App\Models\SubscriptionStaffDetails;
use App\Models\Team;
use App\Models\User;
use App\Services\Subscription\SubscriptionService;
use Carbon\Carbon;
use Hash;
use Illuminate\Database\Seeder;

class UserTestSeeder extends Seeder
{
    use UserSettingTrait;

    /**
     * Run the database seeds.
     */
    public function run(SubscriptionService $subscriptionService): void
    {
        if (app()->environment() !== 'testing') {
            $users = $this->getUsers();

            foreach ($users as $user) {
                $this->assignRole($user);
                $address = Address::find(1);
                $property = Property::factory()
                    ->assignAddress($address->id)
                    ->setMembershipType(MembershipTypeEnum::Private())
                    ->hasAttached($user)
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
                $customer = Customer::factory()->forUser($user, $address->id)->hasAttached($user)->create();
                Employee::factory()->forUser($user, $address->id)->create();
                $team = Team::factory(1)->create();
                $team[0]->users()->attach($user);

                $this->createSubscription($subscriptionService, $user, $property, $customer, $team[0]);
            }
        }
    }

    private function getUserlist()
    {
        return [
            [
                'first_name' => 'Patrick',
                'last_name' => 'Bågendahl',
                'email' => 'patrick@downstairs.se',
                'cellphone' => '46703181848',
                'dial_code' => '46',
                'identity_number' => '197312186237',
                'password' => Hash::make('Pat123'),
            ],
            [
                'first_name' => 'Magnus',
                'last_name' => 'Bernåker',
                'email' => 'magnus.bernaker@downstairs.se',
                'cellphone' => '46706939492',
                'dial_code' => '46',
                'identity_number' => '197905055658',
                'password' => Hash::make('Mag123'),
            ],
            [
                'first_name' => 'Sebastian',
                'last_name' => 'Strandberg',
                'email' => 'sebastian@duadigital.com',
                'cellphone' => '46726413250',
                'dial_code' => '46',
                'identity_number' => '198610154950',
                'password' => Hash::make('Seb123'),
            ],
            [
                'first_name' => 'Peter',
                'last_name' => 'Bernåker',
                'email' => 'peter@bernaker.com',
                'cellphone' => '46707603891',
                'dial_code' => '46',
            ],
            [
                'first_name' => 'Miles',
                'last_name' => 'Morales',
                'email' => 'miles@example.com',
                'cellphone' => '46726413233',
                'dial_code' => '46',
            ],
            [
                'first_name' => 'Iyad',
                'last_name' => 'Alhabbash',
                'email' => 'iyad@downstairs.se',
                'cellphone' => '46733220332',
                'dial_code' => '46',
            ],
        ];
    }

    private function assignRole(User $user)
    {
        $superadmins = [
            'patrick@downstairs.se',
            'magnus.bernaker@downstairs.se',
            'sebastian@duadigital.com',
            'miles@example.com',
        ];

        if (in_array($user->email, $superadmins)) {
            $user->assignRole('Superadmin');
        } else {
            $user->assignRole('Employee', 'Customer');
        }

        if (in_array($user->email, ['miles@example.com'])) {
            $user->info->update([
                'language' => 'en_US',
            ]);
        }
    }

    /**
     * @return User[]
     */
    private function getUsers()
    {
        $results = [];

        foreach ($this->getUserlist() as $data) {
            $user = User::where('email', $data['email'])
                ->orWhere('cellphone', $data['cellphone'])->first();

            if (! $user) {
                $user = User::factory()->hasInfo(1)->addData($data)->create();
                $this->createDefaultSettings($user);

                $results[] = $user;
            } else {
                $user->update($data);
                $results[] = $user;
            }
        }

        return $results;
    }

    private function createSubscription(
        SubscriptionService $subscriptionService,
        User $user,
        Property $property,
        Customer $customer,
        Team $team,
    ) {
        $subscriptions = [];

        foreach ($this->getSubscriptionData() as $data) {
            $subscription = $user->subscriptions()->create([
                ...$data,
                'team_id' => $team->id,
                'customer_id' => $customer->id,
                'property_id' => $property->id,
                'property_type_id' => 1,
            ]);
            $this->createSubscriptionProduct($subscription);
            $this->createSubscriptionStaff($subscription, $user);
            $subscriptions[] = $subscription;
        }
    }

    private function createSubscriptionStaff(Subscription $subscription, User $user)
    {
        SubscriptionStaffDetails::create([
            'subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'quarters' => $subscription->quarters,
        ]);
    }

    private function createSubscriptionProduct(Subscription $subscription)
    {
        //get two rundom products where service id is equal to subscription service id
        $products = Product::inRandomOrder()
            ->where('service_id', $subscription->service_id)->take(2)->get();

        foreach ($products as $product) {
            SubscriptionProduct::create([
                'subscription_id' => $subscription->id,
                'product_id' => $product->id,
                'quantity' => 1,
            ]);
        }
    }

    private function getDate(int $dayOfWeek)
    {
        $firstDay = Carbon::now()->firstOfMonth()->startOfWeek($dayOfWeek);

        return $firstDay->format('Y-m-d');
    }

    private function getSubscriptionData()
    {
        return [
            [
                'service_id' => 1,
                'frequency' => 1,
                'start_at' => $this->getDate(Carbon::MONDAY),
                'start_time_at' => '08:00:00',
                'end_time_at' => '10:00:00',
                'quarters' => 8,
                'refill_sequence' => 52,
                'is_paused' => 0,
                'is_fixed' => false,
            ],
            [
                'service_id' => 1,
                'frequency' => 1,
                'start_at' => $this->getDate(Carbon::TUESDAY),
                'start_time_at' => '08:00:00',
                'end_time_at' => '10:00:00',
                'quarters' => 8,
                'refill_sequence' => 52,
                'is_paused' => 0,
                'is_fixed' => false,
            ],
            [
                'service_id' => 2,
                'frequency' => 1,
                'start_at' => $this->getDate(Carbon::TUESDAY),
                'start_time_at' => '10:00:00',
                'end_time_at' => '12:00:00',
                'quarters' => 8,
                'refill_sequence' => 52,
                'is_paused' => 0,
                'is_fixed' => false,
            ],
        ];
    }
}
