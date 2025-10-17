<?php

namespace Tests\Model;

use App\Models\Employee;
use App\Models\LeaveRegistration;
use App\Models\LeaveRegistrationDetail;
use App\Services\LeaveRegistrationService;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LeaveRegistrationTest extends TestCase
{
    protected LeaveRegistration $leaveRegistration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->leaveRegistration = LeaveRegistration::factory(1, [
            'start_at' => now()->subMonth()->startOfDay(),
        ])
            ->create()
            ->first();
    }

    /** @test */
    public function leaveRegistrationsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('leave_registrations', [
                'id',
                'employee_id',
                'type',
                'start_at',
                'end_at',
                'is_stopped',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function leaveRegistrationHasEmployee(): void
    {
        $this->assertInstanceOf(Employee::class, $this->leaveRegistration->employee);
    }

    /** @test */
    public function leaveRegistrationHasDetails(): void
    {
        $details = LeaveRegistrationService::generateDetails($this->leaveRegistration);
        $this->leaveRegistration->details()->createMany($details);

        $this->leaveRegistration->refresh();

        $this->assertIsObject($this->leaveRegistration->details);
        $this->assertInstanceOf(
            LeaveRegistrationDetail::class,
            $this->leaveRegistration->details->first()
        );
    }

    /** @test */
    public function leaveRegistrationHasRescheduleNeeded(): void
    {
        $this->assertIsBool($this->leaveRegistration->reschedule_needed);
    }
}
