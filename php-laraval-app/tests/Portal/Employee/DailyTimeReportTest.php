<?php

namespace Tests\Portal\Employee;

use App\DTOs\WorkHour\WorkHourResponseDTO;
use App\Models\WorkHour;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DailyTimeReportTest extends TestCase
{
    public function testAdminCanAccessDailyTimeReports(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = WorkHour::all()->count();

        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/time-reports/daily')
            ->assertInertia(fn (Assert $page) => $page
                ->component('TimeReports/Daily/index')
                ->has('timeReports', $total)
                ->has('timeReports.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('date')
                    ->has('startTime')
                    ->has('endTime')
                    ->has('totalHours')
                    ->has('hasDeviation')
                    ->has('bookingHours')
                    ->has('workHours')
                    ->has('timeAdjustmentHours')
                    ->etc()
                    ->has('user')
                    ->has('user', fn (Assert $page) => $page
                        ->has('id')
                        ->has('fullname')
                        ->etc()))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessDailyTimeReports(): void
    {
        $this->actingAs($this->user)
            ->get('/time-reports/daily')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterDailyTimeReports(): void
    {
        $pageSize = config('downstairs.pageSize');

        $this->actingAs($this->admin)
            ->get('/time-reports/daily?id=1')
            ->assertInertia(fn (Assert $page) => $page
                ->component('TimeReports/Daily/index')
                ->has('timeReports', 1)
                ->has('timeReports.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('date')
                    ->has('startTime')
                    ->has('endTime')
                    ->has('totalHours')
                    ->has('hasDeviation')
                    ->has('bookingHours')
                    ->has('workHours')
                    ->has('timeAdjustmentHours')
                    ->etc()
                    ->has('user')
                    ->has('user', fn (Assert $page) => $page
                        ->has('id')
                        ->has('fullname')
                        ->etc()))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessDailyTimeReportsJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/time-reports/daily/json');
        $workHour = WorkHour::first();
        $keys = array_keys(
            WorkHourResponseDTO::from($workHour)->toArray()
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
}
