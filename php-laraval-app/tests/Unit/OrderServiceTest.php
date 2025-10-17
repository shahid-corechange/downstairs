<?php

namespace Tests\Unit;

use App\Enums\Order\OrderStatusEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Enums\VatNumbersEnum;
use App\Models\ScheduleCleaning;
use App\Models\Subscription;
use App\Services\OrderService;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    protected OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user->subscriptions()->forceDelete();
        $this->service = new OrderService();

        Subscription::factory(1, [
            'team_id' => 1,
            'frequency' => SubscriptionFrequencyEnum::EveryWeek(),
            'start_at' => now()->format('Y-m-d'),
            'start_time_at' => now()->format('H:00:00'),
            'end_time_at' => now()->addHour()->format('H:00:00'),
            'quarters' => 4,
        ])->forUser($this->user)->create();
    }

    public function testCanCreateOrder(): void
    {
        $subscription = $this->user->subscriptions()->first();
        $schedule = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Booked())
            ->create();

        [$order] = $this->service->createOrder($schedule);

        $this->assertNotNull($order);
        $this->assertNotNull($order->invoice);
        $this->assertEquals($subscription->customer_id, $order->customer_id);
        $this->assertEquals($this->user->id, $order->user_id);
        $this->assertEquals($subscription->service_id, $order->service_id);
        $this->assertEquals(OrderStatusEnum::Draft(), $order->status);
    }

    public function testOrderHasRows(): void
    {
        $subscription = $this->user->subscriptions()->first();
        $schedule = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Booked())
            ->create();

        [$order] = $this->service->createOrder($schedule);
        $this->service->createOrderRows($order, $schedule);

        $this->assertCount(3, $order->rows);
    }

    public function testOrderCancelByCustomer(): void
    {
        $subscription = $this->user->subscriptions()->first();

        /** @var \App\Models\ScheduleCleaning */
        $schedule = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Booked())
            ->create();

        [$order] = $this->service->createOrder($schedule, $schedule->can_refund);
        $this->service->cancelByCustomer($order, $schedule);
        $row = $order->rows->first();

        if ($schedule->can_refund) {
            $this->assertCount(1, $order->rows);
            $this->assertEquals(0, $row->discount_percentage);
            $this->assertEquals(VatNumbersEnum::TwentyFive(), $row->vat);
            $this->assertEquals($subscription->service->fortnox_article_id, $row->fortnox_article_id);
        } else {
            $this->assertCount(1, $order->rows);
        }
    }

    public function testOrderCancelByAdmin(): void
    {
        $subscription = $this->user->subscriptions()->first();

        /** @var \App\Models\ScheduleCleaning */
        $schedule = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Booked())
            ->create();

        [$order] = $this->service->createOrder($schedule, $schedule->can_refund);
        $this->service->cancelByAdmin($order, $schedule);
        $row = $order->rows->first();

        if ($schedule->can_refund) {
            $this->assertCount(1, $order->rows);
            $this->assertEquals(0, $row->discount_percentage);
            $this->assertEquals(VatNumbersEnum::TwentyFive(), $row->vat);
        } else {
            $this->assertCount(1, $order->rows);
        }
    }
}
