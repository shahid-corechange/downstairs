<?php

namespace Tests\Model;

use App\Models\ScheduleCleaning;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TeamTest extends TestCase
{
    /** @test */
    public function teamsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('teams', [
                'id',
                'name',
                'avatar',
                'color',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function teamHasUsers(): void
    {
        $team = Team::factory()
            ->hasUsers(3)
            ->create();

        $this->assertCount(3, $team->users);
        $this->assertInstanceOf(User::class, $team->users->first());
    }

    // /** @test */
    // public function teamHasSubscriptions(): void
    // {
    //     Subscription::factory()
    //         ->count(1)
    //         ->hasDetails(1)
    //         ->forUser($this->user)
    //         ->create()
    //         ->each(function (Subscription $subscription) {
    //             $subscription->update(['team_id' => $this->team->id]);
    //         });

    //     $this->assertIsObject($this->team->subscriptions);
    //     $this->assertInstanceOf(Subscription::class, $this->team->subscriptions->first());
    // }

    // /** @test */
    // public function teamHasSchedules(): void
    // {
    //     $this->user->scheduleCleanings()->create([
    //         'team_id' => $this->team->id,
    //         'customer_id' => $this->user->customers->first()->id,
    //         'property_id' => $this->user->properties->first()->id,
    //         'subscription_id' => $this->user->subscriptions->first()->id,
    //         'start_at' => now(),
    //         'end_at' => now()->addHour(),
    //         'quarters' => 4,
    //         'status' => 'Booked',
    //     ]);
    //     $this->assertIsObject($this->team->schedules);
    //     $this->assertInstanceOf(ScheduleCleaning::class, $this->team->schedules->first());
    // }
}
