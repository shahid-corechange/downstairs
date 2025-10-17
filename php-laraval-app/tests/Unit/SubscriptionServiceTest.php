<?php

namespace Tests\Unit;

use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Models\BlockDay;
use App\Models\ScheduleCleaning;
use App\Models\Subscription;
use App\Models\Team;
use App\Services\Subscription\SubscriptionService;
use Carbon\Carbon;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    private Subscription $subscription;

    private SubscriptionService $subscriptionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriptionService = new SubscriptionService();

        Subscription::where('team_id', $this->team->id)->forceDelete();
        $subscriptions = Subscription::factory(1, [
            'team_id' => $this->team->id,
            'frequency' => SubscriptionFrequencyEnum::EveryWeek(),
            'refill_sequence' => 52,
            'quarters' => 8,
            'start_at' => now()->format('Y-m-d'),
            'end_at' => now()->addYear()->format('Y-m-d'),
            'start_time_at' => now()->setHour(10)->format('H:00:00'),
            'end_time_at' => now()->setHour(12)->format('H:00:00'),
        ])->forUser($this->user)->create();

        /** @var Subscription */
        $subscription = $subscriptions[0];
        $this->subscription = $subscription;
        $startAt = $subscription->start_at->clone()->setTimeFromTimeString($subscription->start_time_at);
        $endAt = $subscription->end_at->clone()->setTimeFromTimeString($subscription->start_time_at);
        $initialOffset = $startAt->copy()->setTimezone('Europe/Stockholm')->format('O') / 100;

        while ($startAt->lte($endAt)) {
            $scheduleStartAt = $startAt->copy();
            $scheduleEndAt = $startAt->clone()->setTimeFromTimeString($subscription->end_time_at);
            $startAtOffset = $scheduleStartAt->copy()->setTimezone('Europe/Stockholm')->format('O') / 100;
            $endAtOffset = $scheduleEndAt->copy()->setTimezone('Europe/Stockholm')->format('O') / 100;

            if ($startAtOffset !== $initialOffset) {
                $scheduleStartAt->addHours($initialOffset - $startAtOffset);
            }

            if ($endAtOffset !== $initialOffset) {
                $scheduleEndAt->addHours($initialOffset - $endAtOffset);
            }

            ScheduleCleaning::factory(1, [
                'start_at' => $scheduleStartAt->format('Y-m-d H:i:s'),
                'end_at' => $scheduleEndAt->format('Y-m-d H:i:s'),
                'original_start_at' => $scheduleStartAt->format('Y-m-d H:i:s'),
                'status' => ScheduleCleaningStatusEnum::Booked(),
            ])->forSubscription($subscription)->create();

            if ($subscription->frequency === SubscriptionFrequencyEnum::Once()) {
                break;
            }

            $startAt->addWeeks($subscription->frequency);
        }
    }

    public function testSubscriptionWithSameTimeAndTeamWillCollide()
    {
        $totalSchedulesInAYear = 53; // 52 weeks + 1 week for the first schedule

        $this->assertCount($totalSchedulesInAYear, $this->subscriptionService->checkCollision(
            teamId: $this->subscription->team_id,
            frequency: SubscriptionFrequencyEnum::EveryWeek(),
            startAt: $this->subscription->start_at->format('Y-m-d'),
            startTimeAt: $this->subscription->start_time_at,
            endAt: $this->subscription->end_at->format('Y-m-d'),
            endTimeAt: $this->subscription->end_time_at
        ));
    }

    public function testSubscriptionWithSameTimeButDifferentTeamWillNotCollide()
    {
        $team = Team::factory(1)->create()[0];

        $this->assertTrue($this->subscriptionService->checkCollision(
            teamId: $team->id,
            frequency: SubscriptionFrequencyEnum::EveryWeek(),
            startAt: now()->format('Y-m-d'),
            startTimeAt: now()->setHour(10)->format('H:00:00'),
            endAt: now()->addYear()->format('Y-m-d'),
            endTimeAt: now()->setHour(12)->format('H:00:00')
        )->isEmpty());
    }

    public function testSubscriptionWithSameTeamButDifferentTimeWillNotCollide()
    {

        $this->assertTrue($this->subscriptionService->checkCollision(
            teamId: $this->team->id,
            frequency: SubscriptionFrequencyEnum::EveryWeek(),
            startAt: now()->format('Y-m-d'),
            startTimeAt: now()->setHour(13)->format('H:00:00'),
            endAt: now()->addYear()->format('Y-m-d'),
            endTimeAt: now()->setHour(14)->format('H:00:00')
        )->isEmpty());
    }

    public function testSubscriptionWithStartTimeSameAsSchedulesEndTimeWillCollide()
    {
        $totalSchedulesInAYear = 53; // 52 weeks + 1 week for the first schedule
        $subscriptionDate = $this->subscription->start_at->format('Y-m-d');
        $subscriptionTime = $this->subscription->start_time_at;
        $startTimeAt = Carbon::parse($subscriptionDate.' '.$subscriptionTime)
            ->subMinutes(1)
            ->format('H:i:00');
        $endTimeAt = Carbon::parse($this->subscription->end_at->format('Y-m-d').' '.$this->subscription->end_time_at)
            ->addMinutes(1)
            ->format('H:i:00');

        $this->assertCount($totalSchedulesInAYear, $this->subscriptionService->checkCollision(
            teamId: $this->subscription->team_id,
            frequency: SubscriptionFrequencyEnum::EveryWeek(),
            startAt: $this->subscription->start_at->format('Y-m-d'),
            startTimeAt: $startTimeAt,
            endAt: $this->subscription->end_at->format('Y-m-d'),
            endTimeAt: $endTimeAt
        ));
    }

    public function testSubscriptionWithEndTimeSameAsSchedulesStartTimeWillCollide()
    {
        $totalSchedulesInAYear = 53; // 52 weeks + 1 week for the first schedule
        $subscriptionDate = $this->subscription->start_at->format('Y-m-d');
        $subscriptionTime = $this->subscription->start_time_at;
        $startTimeAt = Carbon::parse($subscriptionDate.' '.$subscriptionTime)
            ->addMinutes(1)
            ->format('H:i:00');
        $endTimeAt = Carbon::parse($this->subscription->end_at->format('Y-m-d').' '.$this->subscription->end_time_at)
            ->addMinutes(1)
            ->format('H:i:00');

        $this->assertCount($totalSchedulesInAYear, $this->subscriptionService->checkCollision(
            teamId: $this->subscription->team_id,
            frequency: SubscriptionFrequencyEnum::EveryWeek(),
            startAt: $this->subscription->start_at->format('Y-m-d'),
            startTimeAt: $startTimeAt,
            endAt: $this->subscription->end_at->format('Y-m-d'),
            endTimeAt: $endTimeAt
        ));
    }

    public function testSubscriptionWithTimeOnSchedulesTimeRangeWillCollide()
    {
        $totalSchedulesInAYear = 53; // 52 weeks + 1 week for the first schedule

        $this->assertCount($totalSchedulesInAYear, $this->subscriptionService->checkCollision(
            teamId: $this->team->id,
            frequency: SubscriptionFrequencyEnum::EveryWeek(),
            startAt: now()->format('Y-m-d'),
            startTimeAt: now()->setHour(11)->format('H:00:00'),
            endAt: now()->addYear()->format('Y-m-d'),
            endTimeAt: now()->setHour(12)->format('H:00:00')
        ));
    }

    public function testSubscriptionWillNotCollideWithRescheduledBooking()
    {
        $totalSchedulesInAYear = 52; // 52 weeks + 1 week for the first schedule
        $scheduleCleaning = ScheduleCleaning::where('team_id', $this->subscription->team_id)->first();
        $scheduleCleaning->update([
            'start_at' => now()->setHour(13)->format('Y-m-d H:i:s'),
            'end_at' => now()->setHour(15)->format('Y-m-d H:i:s'),
        ]);
        $subscriptionDate = $this->subscription->start_at->format('Y-m-d');
        $subscriptionTime = $this->subscription->start_time_at;
        $startTimeAt = Carbon::parse($subscriptionDate.' '.$subscriptionTime)
            ->addMinutes(1)
            ->format('H:i:00');
        $endTimeAt = Carbon::parse($this->subscription->end_at->format('Y-m-d').' '.$this->subscription->end_time_at)
            ->addMinutes(1)
            ->format('H:i:00');

        $this->assertCount($totalSchedulesInAYear, $this->subscriptionService->checkCollision(
            teamId: $this->subscription->team_id,
            frequency: SubscriptionFrequencyEnum::EveryWeek(),
            startAt: $this->subscription->start_at->format('Y-m-d'),
            startTimeAt: $startTimeAt,
            endAt: $this->subscription->end_at->format('Y-m-d'),
            endTimeAt: $endTimeAt
        ));
    }

    public function testOnceSubscriptionWillNotCollide()
    {
        $this->assertTrue($this->subscriptionService->checkCollision(
            teamId: $this->team->id,
            frequency: SubscriptionFrequencyEnum::Once(),
            startAt: now()->format('Y-m-d'),
            startTimeAt: now()->setHour(8)->format('H:00:00'),
            endAt: null,
            endTimeAt: now()->setHour(9)->format('H:00:00')
        )->isEmpty());
    }

    public function testEveryWeekSubscriptionWillNotCollide()
    {
        $this->assertTrue($this->subscriptionService->checkCollision(
            teamId: $this->team->id,
            frequency: SubscriptionFrequencyEnum::EveryWeek(),
            startAt: now()->format('Y-m-d'),
            startTimeAt: now()->setHour(8)->format('H:00:00'),
            endAt: null,
            endTimeAt: now()->setHour(9)->format('H:00:00')
        )->isEmpty());
    }

    public function testEveryTwoWeeksSubscriptionWillNotCollide()
    {
        $this->assertTrue($this->subscriptionService->checkCollision(
            teamId: $this->team->id,
            frequency: SubscriptionFrequencyEnum::EveryTwoWeeks(),
            startAt: now()->format('Y-m-d'),
            startTimeAt: now()->setHour(8)->format('H:00:00'),
            endAt: null,
            endTimeAt: now()->setHour(9)->format('H:00:00')
        )->isEmpty());
    }

    public function testEveryThreeWeeksSubscriptionWillNotCollide()
    {
        $this->assertTrue($this->subscriptionService->checkCollision(
            teamId: $this->team->id,
            frequency: SubscriptionFrequencyEnum::EveryThreeWeeks(),
            startAt: now()->format('Y-m-d'),
            startTimeAt: now()->setHour(8)->format('H:00:00'),
            endAt: null,
            endTimeAt: now()->setHour(9)->format('H:00:00')
        )->isEmpty());
    }

    public function testEveryFourWeeksSubscriptionWillNotCollide()
    {
        $this->assertTrue($this->subscriptionService->checkCollision(
            teamId: $this->team->id,
            frequency: SubscriptionFrequencyEnum::EveryFourWeeks(),
            startAt: now()->format('Y-m-d'),
            startTimeAt: now()->setHour(8)->format('H:00:00'),
            endAt: null,
            endTimeAt: now()->setHour(9)->format('H:00:00')
        )->isEmpty());
    }

    public function testEveryEightWeeksSubscriptionWillNotCollide()
    {
        $this->assertTrue($this->subscriptionService->checkCollision(
            teamId: $this->team->id,
            frequency: SubscriptionFrequencyEnum::EveryEightWeeks(),
            startAt: now()->format('Y-m-d'),
            startTimeAt: now()->setHour(8)->format('H:00:00'),
            endAt: null,
            endTimeAt: now()->setHour(9)->format('H:00:00')
        )->isEmpty());
    }

    public function testEveryThirteenWeeksSubscriptionWillNotCollide()
    {
        $this->assertTrue($this->subscriptionService->checkCollision(
            teamId: $this->team->id,
            frequency: SubscriptionFrequencyEnum::EveryThirteenWeeks(),
            startAt: now()->format('Y-m-d'),
            startTimeAt: now()->setHour(8)->format('H:00:00'),
            endAt: null,
            endTimeAt: now()->setHour(9)->format('H:00:00')
        )->isEmpty());
    }

    public function testSemiannualSubscriptionWillNotCollide()
    {
        $this->assertTrue($this->subscriptionService->checkCollision(
            teamId: $this->team->id,
            frequency: SubscriptionFrequencyEnum::Semiannual(),
            startAt: now()->format('Y-m-d'),
            startTimeAt: now()->setHour(8)->format('H:00:00'),
            endAt: null,
            endTimeAt: now()->setHour(9)->format('H:00:00')
        )->isEmpty());
    }

    public function testAnnuallySubscriptionWillNotCollide()
    {
        $this->assertTrue($this->subscriptionService->checkCollision(
            teamId: $this->team->id,
            frequency: SubscriptionFrequencyEnum::Annually(),
            startAt: now()->format('Y-m-d'),
            startTimeAt: now()->setHour(8)->format('H:00:00'),
            endAt: null,
            endTimeAt: now()->setHour(9)->format('H:00:00')
        )->isEmpty());
    }

    public function testOnceSubscriptionWillCollide()
    {
        $this->assertCount(1, $this->subscriptionService->checkCollision(
            teamId: $this->team->id,
            frequency: SubscriptionFrequencyEnum::Once(),
            startAt: now()->format('Y-m-d'),
            startTimeAt: now()->setHour(10)->format('H:00:00'),
            endAt: null,
            endTimeAt: now()->setHour(11)->format('H:00:00')
        ));
    }

    public function testEveryWeekSubscriptionWillCollide()
    {
        $totalSchedulesInAYear = 53; // 52 weeks + 1 week for the first schedule

        $this->assertCount(
            ceil($totalSchedulesInAYear / SubscriptionFrequencyEnum::EveryWeek()),
            $this->subscriptionService->checkCollision(
                teamId: $this->team->id,
                frequency: SubscriptionFrequencyEnum::EveryWeek(),
                startAt: now()->format('Y-m-d'),
                startTimeAt: now()->setHour(10)->format('H:00:00'),
                endAt: null,
                endTimeAt: now()->setHour(11)->format('H:00:00')
            )
        );
    }

    public function testEveryTwoWeeksSubscriptionWillCollide()
    {
        $totalSchedulesInAYear = 53; // 52 weeks + 1 week for the first schedule

        $this->assertCount(
            ceil($totalSchedulesInAYear / SubscriptionFrequencyEnum::EveryTwoWeeks()),
            $this->subscriptionService->checkCollision(
                teamId: $this->team->id,
                frequency: SubscriptionFrequencyEnum::EveryTwoWeeks(),
                startAt: now()->format('Y-m-d'),
                startTimeAt: now()->setHour(10)->format('H:00:00'),
                endAt: null,
                endTimeAt: now()->setHour(11)->format('H:00:00')
            )
        );
    }

    public function testEveryThreeWeeksSubscriptionWillCollide()
    {
        $totalSchedulesInAYear = 53; // 52 weeks + 1 week for the first schedule

        $this->assertCount(
            ceil($totalSchedulesInAYear / SubscriptionFrequencyEnum::EveryThreeWeeks()),
            $this->subscriptionService->checkCollision(
                teamId: $this->team->id,
                frequency: SubscriptionFrequencyEnum::EveryThreeWeeks(),
                startAt: now()->format('Y-m-d'),
                startTimeAt: now()->setHour(10)->format('H:00:00'),
                endAt: null,
                endTimeAt: now()->setHour(11)->format('H:00:00')
            )
        );
    }

    public function testEveryFourWeeksSubscriptionWillCollide()
    {
        $totalSchedulesInAYear = 53; // 52 weeks + 1 week for the first schedule

        $this->assertCount(
            ceil($totalSchedulesInAYear / SubscriptionFrequencyEnum::EveryFourWeeks()),
            $this->subscriptionService->checkCollision(
                teamId: $this->team->id,
                frequency: SubscriptionFrequencyEnum::EveryFourWeeks(),
                startAt: now()->format('Y-m-d'),
                startTimeAt: now()->setHour(10)->format('H:00:00'),
                endAt: null,
                endTimeAt: now()->setHour(11)->format('H:00:00')
            )
        );
    }

    public function testEveryEightWeeksSubscriptionWillCollide()
    {
        $totalSchedulesInAYear = 53; // 52 weeks + 1 week for the first schedule

        $this->assertCount(
            ceil($totalSchedulesInAYear / SubscriptionFrequencyEnum::EveryEightWeeks()),
            $this->subscriptionService->checkCollision(
                teamId: $this->team->id,
                frequency: SubscriptionFrequencyEnum::EveryEightWeeks(),
                startAt: now()->format('Y-m-d'),
                startTimeAt: now()->setHour(10)->format('H:00:00'),
                endAt: null,
                endTimeAt: now()->setHour(11)->format('H:00:00')
            )
        );
    }

    public function testEveryThirteenWeeksSubscriptionWillCollide()
    {
        $totalSchedulesInAYear = 53; // 52 weeks + 1 week for the first schedule

        $this->assertCount(
            ceil($totalSchedulesInAYear / SubscriptionFrequencyEnum::EveryThirteenWeeks()),
            $this->subscriptionService->checkCollision(
                teamId: $this->team->id,
                frequency: SubscriptionFrequencyEnum::EveryThirteenWeeks(),
                startAt: now()->format('Y-m-d'),
                startTimeAt: now()->setHour(10)->format('H:00:00'),
                endAt: null,
                endTimeAt: now()->setHour(11)->format('H:00:00')
            )
        );
    }

    public function testSemiannualSubscriptionWillCollide()
    {
        $totalSchedulesInAYear = 53; // 52 weeks + 1 week for the first schedule

        $this->assertCount(
            ceil($totalSchedulesInAYear / SubscriptionFrequencyEnum::Semiannual()),
            $this->subscriptionService->checkCollision(
                teamId: $this->team->id,
                frequency: SubscriptionFrequencyEnum::Semiannual(),
                startAt: now()->format('Y-m-d'),
                startTimeAt: now()->setHour(10)->format('H:00:00'),
                endAt: null,
                endTimeAt: now()->setHour(11)->format('H:00:00')
            )
        );
    }

    public function testAnnuallySubscriptionWillCollide()
    {
        $totalSchedulesInAYear = 53; // 52 weeks + 1 week for the first schedule

        $this->assertCount(
            ceil($totalSchedulesInAYear / SubscriptionFrequencyEnum::Annually()),
            $this->subscriptionService->checkCollision(
                teamId: $this->team->id,
                frequency: SubscriptionFrequencyEnum::Annually(),
                startAt: now()->format('Y-m-d'),
                startTimeAt: now()->setHour(10)->format('H:00:00'),
                endAt: null,
                endTimeAt: now()->setHour(11)->format('H:00:00')
            )
        );
    }

    public function testBuildSchedules()
    {
        $startAt = Carbon::parse('2025-09-30 06:30:00');
        $endAt = Carbon::parse('2025-09-30 08:30:00');
        $refillSchedules = 104; // iterate 2 years ahead
        $blockDays = BlockDay::where('block_date', '>=', now())->pluck('block_date');
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
        $subscription = $subscriptions[0];

        for ($x = 1; $x <= $refillSchedules; $x++) {
            // Don't create schedule if end date is before today
            if (isset($subscription->end_at)
                && $subscription->end_at->format('Y-m-d') < $startAt->format('Y-m-d')) {
                break;
            }

            // Jump to next week if the day is blocked
            while (isset($blockDays) && $blockDays->search($startAt->format('Y-m-d'))) {
                $startAt->addWeeks($subscription->frequency);
                $endAt->addWeeks($subscription->frequency);
            }

            // assert that start and end are on the same day
            $this->assertTrue($startAt->isSameDay($endAt));
            // assert that time difference is 1 hour
            $this->assertEquals(2, $startAt->diffInHours($endAt));

            // Jump to next week
            $startAt->addWeeks($subscription->frequency);
            $endAt->addWeeks($subscription->frequency);
        }
    }

    public function testGetRefillSequenceSubscriptionNotOnce()
    {
        ScheduleCleaning::where('subscription_id', $this->subscription->id)->forceDelete();
        /** @var \Illuminate\Database\Query\Builder */
        $currentSchedules = $this->subscription->scheduleCleanings()
            ->future()
            ->orderBy('original_start_at', 'desc')
            ->count();
        $refillSchedules = $this->subscriptionService->getRefillSequence($this->subscription, $currentSchedules);
        $this->assertEquals(52, $refillSchedules);
    }

    public function testGetRefillSequenceSubscriptionOnce()
    {
        ScheduleCleaning::where('subscription_id', $this->subscription->id)->forceDelete();
        $this->subscription->update([
            'frequency' => SubscriptionFrequencyEnum::Once(),
            'end_at' => $this->subscription->start_at,
        ]);
        /** @var \Illuminate\Database\Query\Builder */
        $currentSchedules = $this->subscription->scheduleCleanings()
            ->future()
            ->orderBy('original_start_at', 'desc')
            ->count();
        $refillSchedules = $this->subscriptionService->getRefillSequence($this->subscription, $currentSchedules);
        $this->assertEquals(1, $refillSchedules);
    }

    public function testGetDateTimesIfThereIsLatestSchedule()
    {
        /** @var ScheduleCleaning */
        $latestSchedules = $this->subscription->scheduleCleanings()
            ->future()
            ->orderBy('original_start_at', 'desc')
            ->first();
        [$start_at, $end_at] = $this->subscriptionService->getDateTimes($this->subscription, $latestSchedules);
        $this->assertEquals(
            $latestSchedules->original_start_at->addWeeks($this->subscription->frequency)->format('Y-m-d H:i:s'),
            $start_at->format('Y-m-d H:i:s')
        );
    }
}
