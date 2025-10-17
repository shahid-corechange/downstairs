<?php

namespace Tests\Model;

use App\Models\LeaveRegistration;
use App\Models\LeaveRegistrationDetail;
use App\Services\LeaveRegistrationService;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LeaveRegistrationDetailTest extends TestCase
{
    /** @test */
    public function leaveRegistrationDetailssDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('leave_registration_details', [
                'id',
                'leave_registration_id',
                'fortnox_absence_transaction_id',
                'start_at',
                'end_at',
                'created_at',
                'updated_at',
            ]),
        );
    }

    /** @test */
    public function leaveRegistrationHasDetails(): void
    {
        $leaveRegistration = LeaveRegistration::factory(1, [
            'start_at' => now()->subMonth()->startOfDay(),
        ])
            ->create()
            ->first();

        $details = LeaveRegistrationService::generateDetails($leaveRegistration);
        $leaveRegistration->details()->createMany($details);

        $leaveRegistrationDetail = LeaveRegistrationDetail::first();

        $this->assertInstanceOf(
            LeaveRegistration::class,
            $leaveRegistrationDetail->leaveRegistration
        );
    }
}
