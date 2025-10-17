<?php

namespace Tests\Unit;

use App\DTOs\Subscription\SubscriptionScheduleDTO;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Models\ScheduleCleaning;
use App\Models\Subscription;
use App\Services\CreditService;
use App\Services\OrderService;
use App\Services\Schedule\ScheduleCleaningService;
use App\Services\Schedule\ScheduleService;
use Tests\TestCase;

class ScheduleCleaningServiceTest extends TestCase
{
    private Subscription $subscription;

    private ScheduleService $scheduleService;

    private ScheduleCleaningService $scheduleCleaningService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scheduleService = new ScheduleService(
            new OrderService(),
            new CreditService(),
        );

        $this->scheduleCleaningService = new ScheduleCleaningService();

        Subscription::where('team_id', $this->team->id)->forceDelete();
        $subscriptions = Subscription::factory(1, [
            'team_id' => $this->team->id,
            'frequency' => SubscriptionFrequencyEnum::EveryWeek(),
            'quarters' => 16,
            'start_at' => '2024-08-06',
            'end_at' => null,
            'start_time_at' => '06:30:00',
            'end_time_at' => '08:30:00',
        ])->forUser($this->user)->create();
        $this->subscription = $subscriptions[0];
    }

    public function testScheduleGenerationHasCorrectNormalizeTime()
    {
        $startAt = $this->subscription->start_at->copy()->setTimeFromTimeString($this->subscription->start_time_at);
        $endAt = $this->subscription->start_at->copy()->setTimeFromTimeString($this->subscription->end_time_at);
        $amountOfWeeks = 104; // iterate 2 years ahead

        for ($x = 0; $x < $amountOfWeeks; $x++) {
            [$scheduleStartAt, $scheduleEndAt] = $this->scheduleCleaningService->normalizeTime(
                $this->subscription,
                $startAt,
                $endAt
            );

            // assert that start and end are on the same day
            $this->assertTrue($scheduleStartAt->isSameDay($scheduleEndAt));
            // assert that time difference is 1 hour
            $this->assertEquals(2, $scheduleStartAt->diffInHours($scheduleEndAt));

            $startAt->addWeeks($this->subscription->frequency);
            $endAt->addWeeks($this->subscription->frequency);
        }
    }

    public function testCanStoreData()
    {
        ScheduleCleaning::where('subscription_id', $this->subscription->id)->forceDelete();
        $data = SubscriptionScheduleDTO::from([
            'subscription_id' => $this->subscription->id,
            'team_id' => $this->subscription->team_id,
            'customer_id' => $this->subscription->customer_id,
            'property_id' => $this->subscription->property_id,
            'status' => ScheduleCleaningStatusEnum::Booked(),
            'start_at' => '2026-11-10 06:30:00',
            'end_at' => '2026-11-10 08:30:00',
            'quarters' => $this->subscription->quarters,
            'key_information' => $this->subscription->property->key_description,
            'note' => ['note' => 'test'],
            'is_fixed' => $this->subscription->is_fixed,
        ]);

        $this->scheduleCleaningService->store($data);
        $scheduleCleaning = ScheduleCleaning::where('subscription_id', $this->subscription->id)->first();

        $this->assertDatabaseHas('schedule_cleanings', [
            'subscription_id' => $this->subscription->id,
            'start_at' => '2026-11-10 07:30:00',
            'end_at' => '2026-11-10 09:30:00',
        ]);

        // assert that start and end are on the same day
        $this->assertTrue($scheduleCleaning->start_at->isSameDay($scheduleCleaning->end_at));
        // assert that time difference is 1 hour
        $this->assertEquals(2, $scheduleCleaning->start_at->diffInHours($scheduleCleaning->end_at));
    }
}
