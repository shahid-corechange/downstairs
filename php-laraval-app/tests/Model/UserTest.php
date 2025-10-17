<?php

namespace Tests\Model;

use App\Models\Credit;
use App\Models\Customer;
use App\Models\CustomerDiscount;
use App\Models\Employee;
use App\Models\Feedback;
use App\Models\FixedPrice;
use App\Models\Notification;
use App\Models\Property;
use App\Models\RutCoApplicant;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleEmployee;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\UserSetting;
use App\Models\WorkHour;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserTest extends TestCase
{
    /** @test */
    public function usersDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('users', [
                'id',
                'first_name',
                'last_name',
                'email',
                'cellphone',
                'dial_code',
                'identity_number',
                'password',
                'status',
                'created_at',
                'updated_at',
                'last_seen',
                'email_verified_at',
                'cellphone_verified_at',
                'identity_number_verified_at',
                'remember_token',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function userHasFullName(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $user->fullname);
    }

    /** @test */
    public function userHasInitials(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('JD', $user->initials);
    }

    /** @test */
    public function userHasFormattedCellphone(): void
    {
        $user = User::factory()->create([
            'cellphone' => '461234567890',
            'dial_code' => '46',
        ]);

        $this->assertEquals('+46 1234567890', $user->formatted_cellphone);
    }

    /** @test */
    public function userHasTotalCredits(): void
    {
        $user = User::factory()->create();

        $this->assertIsInt($user->total_credits);
    }

    /** @test */
    public function userHasRoles(): void
    {
        $this->assertTrue($this->admin->hasRole('Superadmin'));
    }

    /** @test */
    public function userIsSuperadmin(): void
    {
        $this->assertTrue($this->admin->isSuperadmin());
    }

    /** @test */
    public function userIsEmployee(): void
    {
        $employee = User::factory()
            ->create()
            ->assignRole('Employee');
        $worker = User::factory()
            ->create()
            ->assignRole('Worker');

        $this->assertTrue($employee->isEmployee());
        $this->assertTrue($worker->isEmployee());
    }

    /** @test */
    public function userIsActive(): void
    {
        $this->assertTrue($this->user->isActive());
    }

    /** @test */
    public function userHasInfo(): void
    {
        $this->assertInstanceOf(UserInfo::class, $this->user->info);
    }

    // /** @test */
    // public function userHasSubscriptions(): void
    // {
    //     $this->assertIsObject($this->user->subscriptions);
    //     $this->assertInstanceOf(Subscription::class, $this->user->subscriptions->first());
    // }

    /** @test */
    public function userHasTeams(): void
    {
        $this->team->users()->attach($this->user);

        $this->assertIsObject($this->user->teams);
        $this->assertInstanceOf(Team::class, $this->user->teams->first());
    }

    /** @test */
    public function userHasProperties(): void
    {
        $this->assertIsObject($this->user->properties);
        $this->assertInstanceOf(Property::class, $this->user->properties->first());
    }

    /** @test */
    public function userHasFeedbacks(): void
    {
        $this->user->feedbacks()->create([
            'option' => 'Issues',
            'description' => 'Test feedback',
        ]);
        $this->assertIsObject($this->user->feedbacks);
        $this->assertInstanceOf(Feedback::class, $this->user->feedbacks->first());
    }

    /** @test */
    public function userHasCustomers(): void
    {
        $this->assertIsObject($this->user->customers);
        $this->assertInstanceOf(Customer::class, $this->user->customers->first());
    }

    /** @test */
    public function userHasEmployee(): void
    {
        $this->assertInstanceOf(Employee::class, $this->worker->employee);
    }

    /** @test */
    public function userHasCredits(): void
    {
        Credit::factory()->forUser($this->user->id)->create();

        $this->assertIsObject($this->user->credits);
        $this->assertInstanceOf(Credit::class, $this->user->credits->first());
    }

    // /** @test */
    // public function userHasScheduleCleanings(): void
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
    //     $this->assertIsObject($this->user->scheduleCleanings);
    //     $this->assertInstanceOf(
    //         ScheduleCleaning::class,
    //         $this->user->scheduleCleanings->first()
    //     );
    // }

    // /** @test */
    // public function userHasScheduleEmployees(): void
    // {
    //     $scheduleCleaning = ScheduleCleaning::first();
    //     $scheduleCleaning->scheduleEmployees()->create([
    //         'user_id' => $this->worker->id,
    //         'start_latitude' => fake()->latitude,
    //         'start_longitude' => fake()->longitude,
    //         'start_ip' => fake()->ipv4,
    //         'start_at' => $scheduleCleaning->start_at,
    //         'end_latitude' => fake()->latitude,
    //         'end_longitude' => fake()->longitude,
    //         'end_ip' => fake()->ipv4,
    //         'end_at' => $scheduleCleaning->end_at,
    //     ]);
    //     $this->assertIsObject($this->worker->ScheduleEmployees);
    //     $this->assertInstanceOf(
    //         ScheduleEmployee::class,
    //         $this->worker->ScheduleEmployees->first()
    //     );
    // }

    /** @test */
    public function userHasSettings(): void
    {
        $this->assertIsObject($this->user->settings);
        $this->assertInstanceOf(UserSetting::class, $this->user->settings->first());
    }

    /** @test */
    public function userHasNotifications(): void
    {
        Notification::factory()->forUser($this->user)->create();

        $this->assertIsObject($this->user->notifications);
        $this->assertInstanceOf(Notification::class, $this->user->notifications->first());
    }

    // /** @test */
    // public function userHasFixedPrices(): void
    // {
    //     $user = FixedPrice::first()->user;

    //     $this->assertIsObject($user->fixedPrices);
    //     $this->assertInstanceOf(FixedPrice::class, $user->fixedPrices->first());
    // }

    /** @test */
    public function userHasCustomerDiscounts(): void
    {
        $user = CustomerDiscount::first()->user;

        $this->assertIsObject($user->customerDiscounts);
        $this->assertInstanceOf(
            CustomerDiscount::class,
            $user->customerDiscounts->first()
        );
    }

    /** @test */
    public function userHasRutCoApplicant(): void
    {
        $user = User::first();
        $user->rutCoApplicants()->create([
            'name' => 'John Doe',
            'identity_number' => '198112289874',
            'phone' => '123412341234',
            'dial_code' => '46',
        ]);

        $this->assertInstanceOf(RutCoApplicant::class, $user->rutCoApplicants->first());
    }

    // /** @test */
    // public function userHasWorkHours(): void
    // {
    //     $user = User::role('Worker')->first();

    //     $this->assertIsObject($user->workHours);
    //     $this->assertInstanceOf(WorkHour::class, $user->workHours->first());
    // }
}
