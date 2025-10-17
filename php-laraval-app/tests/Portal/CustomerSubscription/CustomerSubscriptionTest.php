<?php

namespace Tests\Portal\CustomerSubscription;

use App\DTOs\Subscription\SubscriptionResponseDTO;
use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\MembershipTypeEnum;
use App\Enums\VatNumbersEnum;
use App\Events\ScheduleCleaningCreated;
use App\Jobs\SendNotificationJob;
use App\Jobs\UpdateScheduleItemJob;
use App\Models\FixedPrice;
use App\Models\ScheduleCleaning;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use Bus;
use Event;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomerSubscriptionTest extends TestCase
{
    public function testAdminCanAccessCustomerSubscriptions(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = Subscription::whereHas(
            'user',
            fn (Builder $query) => $query->whereHas(
                'roles',
                fn (Builder $query) => $query->where(
                    'name',
                    'Customer'
                )
            )
        )
            ->whereHas(
                'customer',
                fn (Builder $query) => $query->where(
                    'membership_type',
                    MembershipTypeEnum::Private()
                )
            )->count();
        $total = $count > $pageSize ? $pageSize : $count;
        $team = Team::whereHas('users')->get();

        $this->actingAs($this->admin)
            ->get('/customers/subscriptions')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Subscription/Overview/index')
                ->has('subscriptions', $total)
                ->has('subscriptions.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('frequency')
                    ->etc()
                    ->has('service', fn (Assert $page) => $page
                        ->has('name')
                        ->etc())
                    ->has('user', fn (Assert $page) => $page
                        ->has('fullname')
                        ->etc())
                    ->has('team', fn (Assert $page) => $page
                        ->has('name')
                        ->etc()))
                ->has('frequencies')
                ->has('teams', $team->count())
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessCustomerSubscriptions(): void
    {
        $this->actingAs($this->user)
            ->get('/customers/subscriptions')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterCustomerSubscriptions(): void
    {
        $data = Subscription::whereHas(
            'user',
            fn (Builder $query) => $query->whereHas(
                'roles',
                fn (Builder $query) => $query->where(
                    'name',
                    'Customer'
                )
            )
        )
            ->whereHas(
                'customer',
                fn (Builder $query) => $query->where(
                    'membership_type',
                    MembershipTypeEnum::Private()
                )
            )->first();
        $pageSize = config('downstairs.pageSize');
        $team = Team::whereHas('users')->get();

        $this->actingAs($this->admin)
            ->get("/customers/subscriptions?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Subscription/Overview/index')
                ->has('subscriptions', 1)
                ->has('subscriptions.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('frequency', $data->frequency)
                    ->etc()
                    ->has('service', fn (Assert $page) => $page
                        ->where('name', $data->service->name)
                        ->etc())
                    ->has('user', fn (Assert $page) => $page
                        ->where('fullname', $data->user->fullname)
                        ->etc())
                    ->has('team', fn (Assert $page) => $page
                        ->where('name', $data->team->name)
                        ->etc()))
                ->has('frequencies')
                ->has('teams', $team->count())
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessCustomerSubscriptionsJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/customers/subscriptions/json');
        $keys = array_keys(
            SubscriptionResponseDTO::from(Subscription::first())->toArray()
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => $keys,
            ],
            'meta' => [
                'etag',
            ],
        ]);
    }

    public function testCanAccessCustomerSubscriptionJson(): void
    {
        $subscription = $this->user->subscriptions()->first();
        $response = $this->actingAs($this->admin)
            ->get("/customers/subscriptions/{$subscription->id}/json");
        $keys = array_keys(
            SubscriptionResponseDTO::from(Subscription::first())->toArray()
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => $keys,
            'meta' => [
                'etag',
            ],
        ]);
    }

    public function testCanNotAccessCustomerSubscriptionJson(): void
    {
        $subscription = $this->userCompany->subscriptions()->first();

        $this->actingAs($this->admin)
            ->get("/customers/subscriptions/{$subscription->id}/json")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanAccessCustomerSubscriptionWizard(): void
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'Customer');
        })
            ->whereHas('customers', function ($query) {
                $query->where('membership_type', MembershipTypeEnum::Private());
            })
            ->get();

        $services = Service::whereNotIn('id', [2])->private()->get();

        $this->actingAs($this->admin)
            ->get('/customers/subscriptions/wizard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Subscription/Wizard/index')
                ->has('users', $users->count())
                ->has('users.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('fullname')
                    ->has('cellphone'))
                ->has('frequencies')
                ->has('teams', Team::whereHas('users')->count())
                ->has('teams.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('name')
                    ->has('users'))
                ->has('services', $services->count())
                ->has('services.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('name')
                    ->has('priceWithVat')
                    ->etc()));
    }

    public function testCanCreateCustomerSubscriptionFromWizard(): void
    {
        Event::fake(ScheduleCleaningCreated::class);
        $customer = $this->user->customers->first();
        $this->user->subscriptions()->forceDelete();
        $this->user->fixedPrices()->forceDelete();

        $startAt = now()->addDay()->format('Y-m-d');
        $productIds = [1, 2];
        $data = [
            'userId' => $this->user->id,
            'propertyId' => $this->user->properties->first()->id,
            'customerId' => $customer->id,
            'teamId' => $this->team->id,
            'serviceId' => 1,
            'productIds' => $productIds,
            'description' => 'test',
            'quarters' => 4,
            // time and frequency
            'isFixed' => true,
            'frequency' => 1,
            'startAt' => $startAt,
            'endAt' => null,
            'startTimeAt' => '04:00:00',
        ];

        $this->actingAs($this->admin)
            ->post('/customers/subscriptions/wizard', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('subscription created successfully'));

        Event::assertDispatched(ScheduleCleaningCreated::class);
        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);

        $refillSequence = get_setting(
            GlobalSettingEnum::SubscriptionRefillSequence(),
            config('downstairs.subscription.refillSequence')
        );

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $data['userId'],
            'customer_id' => $data['customerId'],
            'property_id' => $data['propertyId'],
            'team_id' => $data['teamId'],
            'service_id' => $data['serviceId'],
            'fixed_price_id' => null,
            'description' => $data['description'],
            'quarters' => $data['quarters'],
            'is_fixed' => $data['isFixed'],
            'frequency' => $data['frequency'],
            'start_at' => $data['startAt'],
            'end_at' => $data['endAt'],
            'start_time_at' => $data['startTimeAt'],
            'refill_sequence' => $refillSequence,
        ]);

        $subscription = Subscription::where('user_id', $this->user->id)->first();
        foreach ($productIds as $productId) {
            $this->assertDatabaseHas('subscription_product', [
                'subscription_id' => $subscription->id,
                'product_id' => $productId,
                'quantity' => 1,
            ]);
        }

        $teamMembers = Team::find($this->team->id)->users;
        foreach ($teamMembers as $teamMember) {
            $this->assertDatabaseHas('subscription_staff_details', [
                'subscription_id' => $subscription->id,
                'user_id' => $teamMember->id,
                'quarters' => 4,
                'is_active' => true,
            ]);
        }

        $this->assertDatabaseMissing('fixed_prices', [
            'user_id' => $data['userId'],
            'is_per_order' => true,
        ]);
    }

    public function testCanCreateCustomerSubscriptionWithCustomPriceFromWizard(): void
    {
        Event::fake(ScheduleCleaningCreated::class);
        $customer = $this->user->customers->first();
        $this->user->subscriptions()->forceDelete();
        $this->user->fixedPrices()->forceDelete();

        $startAt = now()->addDay()->format('Y-m-d');
        $productIds = [1, 2];
        $data = [
            'userId' => $this->user->id,
            'propertyId' => $this->user->properties->first()->id,
            'customerId' => $customer->id,
            'teamId' => $this->team->id,
            'serviceId' => 1,
            'productIds' => $productIds,
            'description' => 'test',
            'quarters' => 4,
            // time and frequency
            'isFixed' => true,
            'frequency' => 1,
            'startAt' => $startAt,
            'endAt' => null,
            'startTimeAt' => '04:00:00',
            // fixed price
            'totalPrice' => 9999,
        ];

        $this->actingAs($this->admin)
            ->post('/customers/subscriptions/wizard', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('subscription created successfully'));

        Event::assertDispatched(ScheduleCleaningCreated::class);
        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);

        $refillSequence = get_setting(
            GlobalSettingEnum::SubscriptionRefillSequence(),
            config('downstairs.subscription.refillSequence')
        );

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $data['userId'],
            'customer_id' => $data['customerId'],
            'property_id' => $data['propertyId'],
            'team_id' => $data['teamId'],
            'service_id' => $data['serviceId'],
            'description' => $data['description'],
            'quarters' => $data['quarters'],
            'is_fixed' => $data['isFixed'],
            'frequency' => $data['frequency'],
            'start_at' => $data['startAt'],
            'end_at' => $data['endAt'],
            'start_time_at' => $data['startTimeAt'],
            'refill_sequence' => $refillSequence,
        ]);

        $subscription = Subscription::where('user_id', $this->user->id)->first();
        foreach ($productIds as $productId) {
            $this->assertDatabaseHas('subscription_product', [
                'subscription_id' => $subscription->id,
                'product_id' => $productId,
                'quantity' => 1,
            ]);
        }

        $teamMembers = Team::find($this->team->id)->users;
        foreach ($teamMembers as $teamMember) {
            $this->assertDatabaseHas('subscription_staff_details', [
                'subscription_id' => $subscription->id,
                'user_id' => $teamMember->id,
                'quarters' => 4,
                'is_active' => true,
            ]);
        }

        $this->assertDatabaseHas('fixed_prices', [
            'user_id' => $data['userId'],
            'is_per_order' => true,
        ]);

        $vat = VatNumbersEnum::TwentyFive();
        $price = $data['totalPrice'] / (1 + $vat / 100);
        $this->assertDatabaseHas('fixed_price_rows', [
            'type' => FixedPriceRowTypeEnum::Service(),
            'quantity' => 1,
            'price' => $price,
            'vat_group' => $vat,
            'has_rut' => true,
        ]);
    }

    public function testCanCreateCustomerSubscriptionWithExistingFixedPrice(): void
    {
        Event::fake(ScheduleCleaningCreated::class);
        $customer = $this->user->customers->first();
        $this->user->fixedPrices()->forceDelete();
        $this->user->subscriptions()->forceDelete();

        $fixedPrice = FixedPrice::create([
            'user_id' => $this->user->id,
            'is_per_order' => true,
            'start_date' => null,
            'end_date' => null,
        ]);
        $fixedPriceRow = $fixedPrice->rows()->create([
            'type' => FixedPriceRowTypeEnum::Service(),
            'quantity' => 1,
            'price' => 9999,
            'vat_group' => VatNumbersEnum::TwentyFive(),
            'has_rut' => true,
        ]);
        $startAt = now()->addDay()->format('Y-m-d');
        $productIds = [1, 2];

        $data = [
            'userId' => $this->user->id,
            'propertyId' => $this->user->properties->first()->id,
            'customerId' => $customer->id,
            'teamId' => $this->team->id,
            'serviceId' => 1,
            'productIds' => $productIds,
            'description' => 'test',
            'quarters' => 4,
            // time and frequency
            'isFixed' => true,
            'frequency' => 1,
            'startAt' => $startAt,
            'endAt' => null,
            'startTimeAt' => '04:00:00',
            // fixed price
            'fixedPriceId' => $fixedPrice->id,
        ];

        $this->actingAs($this->admin)
            ->post('/customers/subscriptions/wizard', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('subscription created successfully'));

        Event::assertDispatched(ScheduleCleaningCreated::class);
        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);

        $refillSequence = get_setting(
            GlobalSettingEnum::SubscriptionRefillSequence(),
            config('downstairs.subscription.refillSequence')
        );

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $data['userId'],
            'customer_id' => $data['customerId'],
            'property_id' => $data['propertyId'],
            'team_id' => $data['teamId'],
            'service_id' => $data['serviceId'],
            'description' => $data['description'],
            'quarters' => $data['quarters'],
            'is_fixed' => $data['isFixed'],
            'frequency' => $data['frequency'],
            'start_at' => $data['startAt'],
            'end_at' => $data['endAt'],
            'start_time_at' => $data['startTimeAt'],
            'refill_sequence' => $refillSequence,
            'fixed_price_id' => $data['fixedPriceId'],
        ]);

        $subscription = Subscription::where('user_id', $this->user->id)->first();
        foreach ($productIds as $productId) {
            $this->assertDatabaseHas('subscription_product', [
                'subscription_id' => $subscription->id,
                'product_id' => $productId,
                'quantity' => 1,
            ]);
        }

        $teamMembers = Team::find($this->team->id)->users;
        foreach ($teamMembers as $teamMember) {
            $this->assertDatabaseHas('subscription_staff_details', [
                'subscription_id' => $subscription->id,
                'user_id' => $teamMember->id,
                'quarters' => 4,
                'is_active' => true,
            ]);
        }

        $this->assertDatabaseHas('fixed_prices', [
            'user_id' => $data['userId'],
            'is_per_order' => true,
        ]);

        $vat = VatNumbersEnum::TwentyFive();
        $this->assertDatabaseHas('fixed_price_rows', [
            'type' => $fixedPriceRow->type,
            'quantity' => $fixedPriceRow->quantity,
            'price' => $fixedPriceRow->price,
            'vat_group' => $vat,
            'has_rut' => $fixedPriceRow->has_rut,
        ]);
    }

    public function testCanUpdateCustomerSubscription(): void
    {
        Event::fake(ScheduleCleaningCreated::class);
        $subscription = Subscription::where('user_id', $this->user->id)->first();
        Subscription::whereNot('id', $subscription->id)->forceDelete();

        $startAt = now()->addDays(4)->format('Y-m-d');
        $data = [
            'teamId' => $this->team->id,
            'frequency' => 1,
            'isFixed' => true,
            'startAt' => $startAt,
            'endAt' => null,
            'startTimeAt' => '04:00:00',
            'quarters' => 4,
            'description' => 'test',
            'productIds' => [3, 4],
        ];

        $subscriptionProductIds = $subscription->products->pluck('product_id')->toArray();
        $newProductIds = array_diff($data['productIds'], $subscriptionProductIds);
        $removedProductIds = array_diff($subscriptionProductIds, $data['productIds']);

        $this->actingAs($this->admin)
            ->patch("/customers/subscriptions/{$subscription->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('subscription updated successfully'));

        Event::assertDispatched(ScheduleCleaningCreated::class);
        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);

        if (! empty($newProductIds) || ! empty($removedProductIds)) {
            Bus::assertDispatchedAfterResponse(UpdateScheduleItemJob::class);
        }

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'team_id' => $data['teamId'],
            'frequency' => $data['frequency'],
            'is_fixed' => $data['isFixed'],
            'start_at' => $data['startAt'],
            'end_at' => $data['endAt'],
            'start_time_at' => $data['startTimeAt'],
            'quarters' => $data['quarters'],
            'description' => $data['description'],
        ]);

        foreach ($newProductIds as $productId) {
            $this->assertDatabaseHas('subscription_product', [
                'subscription_id' => $subscription->id,
                'product_id' => $productId,
                'quantity' => 1,
            ]);
        }

        foreach ($removedProductIds as $productId) {
            $this->assertDatabaseMissing('subscription_product', [
                'subscription_id' => $subscription->id,
                'product_id' => $productId,
            ]);
        }
    }

    public function testCanPauseCustomerSubscription(): void
    {
        $subscription = Subscription::where('is_paused', false)->first();

        $this->actingAs($this->admin)
            ->post("/customers/subscriptions/{$subscription->id}/pause")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('subscription paused successfully'));

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testCanContinueCustomerSubscription(): void
    {
        Event::fake(ScheduleCleaningCreated::class);
        $subscription = Subscription::first();
        ScheduleCleaning::whereNotNull('id')->forceDelete();
        Subscription::whereNot('id', $subscription->id)->forceDelete();
        $subscription->update(['is_paused' => true]);

        $this->actingAs($this->admin)
            ->post("/customers/subscriptions/{$subscription->id}/continue")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('subscription continued successfully'));

        Event::assertDispatched(ScheduleCleaningCreated::class);
        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'is_paused' => false,
        ]);
    }

    public function testCanDeleteCustomerSubscription(): void
    {
        $subscription = Subscription::first();

        $this->actingAs($this->admin)
            ->delete("/customers/subscriptions/{$subscription->id}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('subscription deleted successfully'));

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testCanRestoreCustomerSubscription(): void
    {
        Event::fake(ScheduleCleaningCreated::class);
        $subscription = Subscription::first();
        ScheduleCleaning::whereNotNull('id')->forceDelete();
        Subscription::whereNot('id', $subscription->id)->forceDelete();
        $subscription->delete();

        $this->actingAs($this->admin)
            ->post("/customers/subscriptions/{$subscription->id}/restore")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('subscription restored successfully'));

        Event::assertDispatched(ScheduleCleaningCreated::class);
    }
}
