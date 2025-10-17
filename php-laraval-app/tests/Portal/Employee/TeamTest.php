<?php

namespace Tests\Portal\Employee;

use App\Contracts\StorageService;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Models\ScheduleCleaning;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;
use Session;
use Tests\TestCase;

class TeamTest extends TestCase
{
    public function testAdminCanAccessTeams(): void
    {
        $team = Team::all();
        $workerCount = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Worker', 'Superadmin']);
        })->count();

        $this->actingAs($this->admin)
            ->get('/teams')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Team/Overview/index')
                ->has('teams', $team->count())
                ->has('teams.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('name')
                    ->has('avatar')
                    ->has('color')
                    ->has('description')
                    ->etc()
                    ->has('users')
                    ->has('users.0', fn (Assert $page) => $page
                        ->has('id')
                        ->has('fullname')))
                ->has('workers', $workerCount));
    }

    public function testCustomerCanNotAccessTeams(): void
    {
        $this->actingAs($this->user)
            ->get('/teams')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanCreateTeam(): void
    {
        $this->mock(StorageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->never();
        });

        $data = [
            'name' => 'Team 1',
            'color' => '#000000',
            'description' => 'Team 1 description',
            'userIds' => [$this->worker->id],
        ];

        $response = $this->actingAs($this->admin)->post('/teams', $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('team created successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('teams', [
            'name' => $data['name'],
            'color' => $data['color'],
            'description' => $data['description'],
        ]);

        $this->assertDatabaseHas('team_user', [
            'team_id' => Team::where('name', $data['name'])->first()->id,
            'user_id' => $data['userIds'][0],
        ]);
    }

    public function testCanUpdateTeam(): void
    {
        $this->mock(StorageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->never();
        });
        $team = $this->worker->teams()->first();

        $data = [
            'name' => 'Team 1',
            'color' => '#000000',
            'description' => 'Team 1 description',
            'userIds' => [$this->worker->id],
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/teams/{$team->id}", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('team updated successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => $data['name'],
            'color' => $data['color'],
            'description' => $data['description'],
        ]);

        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $data['userIds'][0],
        ]);
    }

    public function testCanDeleteTeam(): void
    {
        $team = Team::create([
            'name' => 'Team 1',
            'color' => '#000000',
            'description' => 'Team 1 description',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/teams/{$team->id}");

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('team deleted successfully'),
            Session::get('success')
        );

        $this->assertSoftDeleted('teams', [
            'id' => $team->id,
        ]);
    }

    public function testCanNotDeleteTeamWithActiveSubscriptions(): void
    {
        $team = Team::create([
            'name' => 'Team 1',
            'color' => '#000000',
            'description' => 'Team 1 description',
        ]);
        Subscription::factory()
            ->count(1)
            ->hasDetails(1)
            ->forUser($this->user)
            ->create()
            ->each(function (Subscription $subscription) use ($team) {
                $subscription->update(['team_id' => $team->id]);
            });

        $response = $this->actingAs($this->admin)
            ->delete("/teams/{$team->id}");

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('team has active subscriptions', ['count' => 1]),
            Session::get('error')
        );

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'is_active' => true,
        ]);
    }

    public function testCanRestoreTeam(): void
    {
        $team = Team::create([
            'name' => 'Team 1',
            'color' => '#000000',
            'description' => 'Team 1 description',
            'is_active' => false,
        ]);
        $team->delete();

        $response = $this->actingAs($this->admin)
            ->post("/teams/{$team->id}/restore");

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('team restored successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'is_active' => true,
            'deleted_at' => null,
        ]);
    }

    public function testCanUpdateTeamWithoutChangeWorkers(): void
    {
        $this->mock(StorageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->never();
        });

        $result = $this->createFullTeam([$this->worker->id]);
        /** @var \App\Models\Team */
        $team = $result['team'];
        /** @var \App\Models\Subscription */
        $subscription = $result['subscription'];
        /** @var \App\Models\ScheduleCleaning */
        $schedule = $result['schedule'];

        $data = [
            'name' => 'Team 1',
            'color' => '#FFFFFF',
            'description' => 'Team 1 description',
            'userIds' => [$this->worker->id],
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/teams/{$team->id}", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('team updated successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => $data['name'],
            'color' => $data['color'],
            'description' => $data['description'],
        ]);

        $this->assertEquals(1, $team->users->count());
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $this->worker->id,
        ]);

        $this->assertEquals(1, $subscription->staffs->count());
        $this->assertDatabaseHas('subscription_staff_details', [
            'subscription_id' => $subscription->id,
            'user_id' => $this->worker->id,
        ]);

        $this->assertEquals(1, $schedule->scheduleEmployees->count());
        $this->assertDatabaseHas('schedule_employees', [
            'scheduleable_type' => ScheduleCleaning::class,
            'scheduleable_id' => $schedule->id,
            'user_id' => $this->worker->id,
        ]);
    }

    public function testCanUpdateTeamWithMoreWorkers(): void
    {
        $this->mock(StorageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->never();
        });

        $result = $this->createFullTeam([$this->worker->id]);
        /** @var \App\Models\Team */
        $team = $result['team'];
        /** @var \App\Models\Subscription */
        $subscription = $result['subscription'];
        /** @var \App\Models\ScheduleCleaning */
        $schedule = $result['schedule'];

        $newWorker = $this->createUser(role: 'Worker');

        $data = [
            'name' => 'Team 1',
            'color' => '#FFFFFF',
            'description' => 'Team 1 description',
            'userIds' => [$this->worker->id, $newWorker->id],
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/teams/{$team->id}", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('team updated successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => $data['name'],
            'color' => $data['color'],
            'description' => $data['description'],
        ]);

        $this->assertEquals(2, $team->users->count());
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $newWorker->id,
        ]);

        $this->assertEquals(2, $subscription->staffs->count());
        $this->assertDatabaseHas('subscription_staff_details', [
            'subscription_id' => $subscription->id,
            'user_id' => $newWorker->id,
        ]);

        $this->assertEquals(2, $schedule->scheduleEmployees->count());
        $this->assertDatabaseHas('schedule_employees', [
            'scheduleable_type' => ScheduleCleaning::class,
            'scheduleable_id' => $schedule->id,
            'user_id' => $newWorker->id,
        ]);
    }

    public function testCanUpdateTeamWithLessWorkersIfNoCollidedSchedules(): void
    {
        $this->mock(StorageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->never();
        });

        $newWorker = $this->createUser(role: 'Worker');

        $result = $this->createFullTeam([$this->worker->id, $newWorker->id]);
        /** @var \App\Models\Team */
        $team = $result['team'];
        /** @var \App\Models\Subscription */
        $subscription = $result['subscription'];
        /** @var \App\Models\ScheduleCleaning */
        $schedule = $result['schedule'];

        $data = [
            'name' => 'Team 1',
            'color' => '#000000',
            'description' => 'Team 1 description',
            'userIds' => [$this->worker->id],
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/teams/{$team->id}", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => $team->name,
            'color' => $team->color,
            'description' => $team->description,
        ]);

        $this->assertEquals(1, $team->users->count());
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $this->worker->id,
        ]);

        $this->assertEquals(1, $subscription->staffs->count());
        $this->assertDatabaseHas('subscription_staff_details', [
            'subscription_id' => $subscription->id,
            'user_id' => $this->worker->id,
        ]);

        $this->assertEquals(1, $schedule->scheduleEmployees->count());
        $this->assertDatabaseHas('schedule_employees', [
            'scheduleable_type' => ScheduleCleaning::class,
            'scheduleable_id' => $schedule->id,
            'user_id' => $this->worker->id,
        ]);
    }

    public function testCanUpdateTeamByRemovingOriginalWorkersAndAddingNewWorkers(): void
    {
        $this->mock(StorageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->never();
        });

        $result = $this->createFullTeam([$this->worker->id]);
        /** @var \App\Models\Team */
        $team = $result['team'];
        /** @var \App\Models\Subscription */
        $subscription = $result['subscription'];
        /** @var \App\Models\ScheduleCleaning */
        $schedule = $result['schedule'];

        $newWorker = $this->createUser(role: 'Worker');

        $data = [
            'name' => 'Team 1',
            'color' => '#FFFFFF',
            'description' => 'Team 1 description',
            'userIds' => [$newWorker->id],
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/teams/{$team->id}", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('team updated successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => $data['name'],
            'color' => $data['color'],
            'description' => $data['description'],
        ]);

        $this->assertEquals(1, $team->users->count());
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $newWorker->id,
        ]);

        $this->assertEquals(1, $subscription->staffs->count());
        $this->assertDatabaseHas('subscription_staff_details', [
            'subscription_id' => $subscription->id,
            'user_id' => $newWorker->id,
        ]);

        $this->assertEquals(1, $schedule->scheduleEmployees->count());
        $this->assertDatabaseHas('schedule_employees', [
            'scheduleable_type' => ScheduleCleaning::class,
            'scheduleable_id' => $schedule->id,
            'user_id' => $newWorker->id,
        ]);
    }

    public function testCanNotUpdateTeamWithLessWorkersIfThereAreCollidedSchedules(): void
    {
        $this->mock(StorageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->never();
        });

        $newWorker = $this->createUser(role: 'Worker');
        $workerIds = [$this->worker->id, $newWorker->id];

        $result = $this->createFullTeam($workerIds, false);
        /** @var \App\Models\Team */
        $team = $result['team'];
        /** @var \App\Models\Subscription */
        $subscription = $result['subscription'];
        /** @var \App\Models\ScheduleCleaning */
        $schedule = $result['schedule'];

        $otherSchedule = ScheduleCleaning::factory(1, [
            'start_at' => $schedule->end_at,
            'end_at' => $schedule->end_at->addHours(1),
            'status' => ScheduleCleaningStatusEnum::Booked(),
        ])->forSubscription($subscription)->create()->first();

        foreach ($workerIds as $workerId) {
            $otherSchedule->scheduleEmployees()->create([
                'user_id' => $workerId,
                'status' => ScheduleEmployeeStatusEnum::Pending(),
            ]);
        }

        $data = [
            'name' => 'Team 1',
            'color' => '#000000',
            'description' => 'Team 1 description',
            'userIds' => [$this->worker->id],
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/teams/{$team->id}", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('team schedules collisions'),
            Session::get('error')
        );

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => $team->name,
            'color' => $team->color,
            'description' => $team->description,
        ]);

        $this->assertEquals(2, $team->users->count());
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $newWorker->id,
        ]);

        $this->assertEquals(2, $subscription->staffs->count());
        $this->assertDatabaseHas('subscription_staff_details', [
            'subscription_id' => $subscription->id,
            'user_id' => $newWorker->id,
        ]);

        $this->assertEquals(2, $schedule->scheduleEmployees->count());
        $this->assertDatabaseHas('schedule_employees', [
            'scheduleable_type' => ScheduleCleaning::class,
            'scheduleable_id' => $schedule->id,
            'user_id' => $newWorker->id,
        ]);
    }

    public function testCanNotUpdateTeamWithTemporaryWorkerAsBaseAndCollidedWithOtherSchedules(): void
    {
        $this->mock(StorageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->never();
        });

        $newWorker = $this->createUser(role: 'Worker');
        $workerIds = [$this->worker->id];

        $result = $this->createFullTeam($workerIds, false);
        /** @var \App\Models\Team */
        $team = $result['team'];
        /** @var \App\Models\Subscription */
        $subscription = $result['subscription'];
        /** @var \App\Models\ScheduleCleaning */
        $schedule = $result['schedule'];

        $this->addTemporaryWorkerToSchedule($newWorker, $schedule);

        $otherSchedule = ScheduleCleaning::factory(1, [
            'start_at' => $schedule->end_at,
            'end_at' => $schedule->end_at->addHours(1),
            'status' => ScheduleCleaningStatusEnum::Booked(),
        ])->forSubscription($subscription)->create()->first();

        foreach ($workerIds as $workerId) {
            $otherSchedule->scheduleEmployees()->create([
                'user_id' => $workerId,
                'status' => ScheduleEmployeeStatusEnum::Pending(),
            ]);
        }

        $data = [
            'name' => 'Team 1',
            'color' => '#000000',
            'description' => 'Team 1 description',
            'userIds' => [$newWorker->id],
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/teams/{$team->id}", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('team schedules collisions'),
            Session::get('error')
        );

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => $team->name,
            'color' => $team->color,
            'description' => $team->description,
        ]);

        $this->assertEquals(1, $team->users->count());
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $this->worker->id,
        ]);

        $this->assertEquals(1, $subscription->staffs->count());
        $this->assertDatabaseHas('subscription_staff_details', [
            'subscription_id' => $subscription->id,
            'user_id' => $this->worker->id,
        ]);

        $this->assertEquals(2, $schedule->scheduleEmployees->count());
        $this->assertDatabaseHas('schedule_employees', [
            'scheduleable_type' => ScheduleCleaning::class,
            'scheduleable_id' => $schedule->id,
            'user_id' => $this->worker->id,
        ]);
    }

    public function testCanUpdateTeamWithTemporaryWorkerAsBaseAndNoCollidedSchedules(): void
    {
        $this->mock(StorageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->never();
        });

        $newWorker = $this->createUser(role: 'Worker');
        $workerIds = [$this->worker->id];

        $result = $this->createFullTeam($workerIds);
        /** @var \App\Models\Team */
        $team = $result['team'];
        /** @var \App\Models\Subscription */
        $subscription = $result['subscription'];
        /** @var \App\Models\ScheduleCleaning */
        $schedule = $result['schedule'];

        $this->addTemporaryWorkerToSchedule($newWorker, $schedule);

        $data = [
            'name' => 'Team 1',
            'color' => '#000000',
            'description' => 'Team 1 description',
            'userIds' => [$newWorker->id],
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/teams/{$team->id}", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => $team->name,
            'color' => $team->color,
            'description' => $team->description,
        ]);

        $this->assertEquals(1, $team->users->count());
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $newWorker->id,
        ]);

        $this->assertEquals(1, $subscription->staffs->count());
        $this->assertDatabaseHas('subscription_staff_details', [
            'subscription_id' => $subscription->id,
            'user_id' => $newWorker->id,
        ]);

        $this->assertEquals(1, $schedule->scheduleEmployees->count());
        $this->assertDatabaseHas('schedule_employees', [
            'scheduleable_type' => ScheduleCleaning::class,
            'scheduleable_id' => $schedule->id,
            'user_id' => $newWorker->id,
        ]);
    }

    protected function createFullTeam(array $workerIds, bool $withOtherSchedule = true): array
    {
        $team = Team::create([
            'name' => 'Team 1',
            'color' => '#000000',
            'description' => 'Team 1 description',
        ]);
        $team->users()->sync($workerIds);

        $subscription = Subscription::factory(1, ['team_id' => $team->id])
            ->forUser($this->user)
            ->create()->first();

        /** @var \App\Models\ScheduleCleaning */
        $schedule = ScheduleCleaning::factory(1, [
            'status' => ScheduleCleaningStatusEnum::Booked(),
        ])->forSubscription($subscription)->create()->first();

        if ($withOtherSchedule) {
            $otherSchedule = ScheduleCleaning::factory(1, [
                'start_at' => $schedule->end_at->addHours(1),
                'end_at' => $schedule->end_at->addHours(2),
                'status' => ScheduleCleaningStatusEnum::Booked(),
            ])->forSubscription($subscription)->create()->first();
        }

        foreach ($workerIds as $workerId) {
            $subscription->staffs()->create([
                'user_id' => $workerId,
                'quarters' => $subscription->quarters,
            ]);
            $schedule->scheduleEmployees()->create([
                'user_id' => $workerId,
                'status' => ScheduleEmployeeStatusEnum::Pending(),
            ]);

            if ($withOtherSchedule) {
                $otherSchedule->scheduleEmployees()->create([
                    'user_id' => $workerId,
                    'status' => ScheduleEmployeeStatusEnum::Pending(),
                ]);
            }
        }

        return [
            'team' => $team,
            'subscription' => $subscription,
            'schedule' => $schedule,
        ];
    }

    protected function addTemporaryWorkerToSchedule(User $user, ScheduleCleaning $scheduleCleaning): void
    {
        $scheduleCleaning->scheduleEmployees()->create([
            'user_id' => $user->id,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);
        $newEndAt = calculate_end_time(
            $scheduleCleaning->start_at,
            calculate_calendar_quarters(
                $scheduleCleaning->quarters,
                $scheduleCleaning->scheduleEmployees()->count(),
            ),
            format: 'Y-m-d H:i:s',
        );
        $scheduleCleaning->update(['end_at' => $newEndAt]);
    }
}
