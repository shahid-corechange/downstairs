<?php

namespace Tests\Portal\Operation;

use App\DTOs\UnassignSubscription\UnassignSubscriptionResponseDTO;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\MembershipTypeEnum;
use App\Events\ScheduleCleaningCreated;
use App\Jobs\SendNotificationJob;
use App\Models\ScheduleCleaning;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\UnassignSubscription;
use Bus;
use Event;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UnassignSubscriptionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        UnassignSubscription::factory(1)->forUser($this->user)->create();
    }

    public function testAdminCanAccessUnassignSubscriptions(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = UnassignSubscription::count();
        $total = $count > $pageSize ? $pageSize : $count;
        $team = Team::whereHas('users')->get();

        $this->actingAs($this->admin)
            ->get('/unassign-subscriptions')
            ->assertInertia(fn (Assert $page) => $page
                ->component('UnassignSubscription/Overview/index')
                ->has('unassignSubscriptions', $total)
                ->has('unassignSubscriptions.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('frequency')
                    ->etc()
                    ->has('service', fn (Assert $page) => $page
                        ->has('name')
                        ->etc())
                    ->has('user', fn (Assert $page) => $page
                        ->has('fullname')
                        ->etc()))
                ->has('frequencies')
                ->has('teams', $team->count())
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessUnassignSubscriptions(): void
    {
        $this->actingAs($this->user)
            ->get('/unassign-subscriptions')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterUnassignSubscriptions(): void
    {
        $data = UnassignSubscription::first();
        $pageSize = config('downstairs.pageSize');
        $team = Team::whereHas('users')->get();

        $this->actingAs($this->admin)
            ->get("/unassign-subscriptions?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('UnassignSubscription/Overview/index')
                ->has('unassignSubscriptions', 1)
                ->has('unassignSubscriptions.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('frequency', $data->frequency)
                    ->etc()
                    ->has('service', fn (Assert $page) => $page
                        ->where('name', $data->service->name)
                        ->etc())
                    ->has('user', fn (Assert $page) => $page
                        ->where('fullname', $data->user->fullname)
                        ->etc()))
                ->has('frequencies')
                ->has('teams', $team->count())
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessUnassignSubscriptionsJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/unassign-subscriptions/json');
        $keys = array_keys(
            UnassignSubscriptionResponseDTO::from(UnassignSubscription::first())->toArray()
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

    public function testCanCreateUnassignSubscription(): void
    {
        $customer = $this->user->customers->first();
        UnassignSubscription::whereNotNull('id')->delete();

        $startAt = now()->addDay()->format('Y-m-d');
        $productIds = [1, 2];
        $data = [
            'type' => MembershipTypeEnum::Private(),
            'userId' => $this->user->id,
            'propertyId' => $this->user->properties->first()->id,
            'customerId' => $customer->id,
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
            'fixedPrice' => 1400.00,
        ];

        $this->actingAs($this->admin)
            ->post('/unassign-subscriptions', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('unassign subscription created successfully'));

        $refillSequence = get_setting(
            GlobalSettingEnum::SubscriptionRefillSequence(),
            config('downstairs.subscription.refillSequence')
        );

        $this->assertDatabaseHas('unassign_subscriptions', [
            'user_id' => $data['userId'],
            'customer_id' => $data['customerId'],
            'property_id' => $data['propertyId'],
            'service_id' => $data['serviceId'],
            'fixed_price' => (float) $data['fixedPrice'],
            'description' => $data['description'],
            'quarters' => $data['quarters'],
            'is_fixed' => $data['isFixed'] ? 1 : 0,
            'frequency' => $data['frequency'],
            'start_at' => $data['startAt'],
            'end_at' => $data['endAt'],
            'start_time_at' => $data['startTimeAt'],
            'refill_sequence' => $refillSequence,
        ]);

        $subscription = UnassignSubscription::first();
        $this->assertEquals($productIds, $subscription->product_ids);
    }

    public function testCanUpdateUnassignSubscription(): void
    {
        $subscription = UnassignSubscription::first();

        $startAt = now()->addDay()->format('Y-m-d');
        $data = [
            'type' => MembershipTypeEnum::Private(),
            'frequency' => 1,
            'isFixed' => true,
            'startAt' => $startAt,
            'endAt' => null,
            'startTimeAt' => '04:00:00',
            'quarters' => 4,
            'description' => 'updated description',
            'productIds' => [3, 4],
        ];

        $this->actingAs($this->admin)
            ->patch("/unassign-subscriptions/{$subscription->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('unassign subscription updated successfully'));

        $this->assertDatabaseHas('unassign_subscriptions', [
            'id' => $subscription->id,
            'frequency' => $data['frequency'],
            'is_fixed' => $data['isFixed'],
            'start_at' => $data['startAt'],
            'end_at' => $data['endAt'],
            'start_time_at' => $data['startTimeAt'],
            'quarters' => $data['quarters'],
            'description' => $data['description'],
        ]);

        $subscription->refresh();
        $this->assertEquals($data['productIds'], $subscription->product_ids);
    }

    public function testCanDeleteUnassignSubscription(): void
    {
        $subscription = UnassignSubscription::first();

        $this->actingAs($this->admin)
            ->delete("/unassign-subscriptions/{$subscription->id}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('unassign subscription deleted successfully'));

        $this->assertDatabaseMissing('unassign_subscriptions', [
            'id' => $subscription->id,
        ]);
    }

    public function testCanGenerateSubscription(): void
    {
        Event::fake(ScheduleCleaningCreated::class);
        $this->user->subscriptions()->forceDelete();
        $this->user->fixedPrices()->forceDelete();
        $unassignSubscription = UnassignSubscription::first();
        Subscription::whereNotNull('id')->forceDelete();
        ScheduleCleaning::whereNotNull('id')->forceDelete();
        UnassignSubscription::whereNot('id', $unassignSubscription->id)->delete();
        $subscriptionData = $unassignSubscription;

        $data = [
            'type' => MembershipTypeEnum::Private(),
            'teamId' => $this->team->id,
            'productIds' => [1, 2],
            'fixedPrice' => 1400.00,
        ];

        $this->actingAs($this->admin)
            ->post("/unassign-subscriptions/{$unassignSubscription->id}/generate", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('subscription created successfully'));

        Event::assertDispatched(ScheduleCleaningCreated::class);
        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);

        $this->assertDatabaseMissing('unassign_subscriptions', [
            'id' => $unassignSubscription->id,
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $subscriptionData->user_id,
            'customer_id' => $subscriptionData->customer_id,
            'property_id' => $subscriptionData->property_id,
            'team_id' => $data['teamId'],
            'service_id' => $subscriptionData->service_id,
            'description' => $subscriptionData->description,
            'quarters' => $subscriptionData->quarters,
            'is_fixed' => $subscriptionData->is_fixed,
            'frequency' => $subscriptionData->frequency,
            'start_at' => $subscriptionData->start_at->format('Y-m-d'),
            'end_at' => $subscriptionData->end_at?->format('Y-m-d'),
            'start_time_at' => $subscriptionData->start_time_at,
            'refill_sequence' => $subscriptionData->refill_sequence,
        ]);

        $subscription = Subscription::where('user_id', $subscriptionData->user_id)->first();
        foreach ($data['productIds'] as $productId) {
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
                'quarters' => $subscriptionData->quarters,
                'is_active' => true,
            ]);
        }

        $this->assertDatabaseHas('fixed_prices', [
            'user_id' => $subscriptionData->user_id,
            'is_per_order' => 1,
        ]);
    }
}
