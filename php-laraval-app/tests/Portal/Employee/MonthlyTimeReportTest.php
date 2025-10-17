<?php

namespace Tests\Portal\Employee;

use App\DTOs\WorkHour\MonthlyWorkHourResponseDTO;
use App\Models\MonthlyWorkHour;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MonthlyTimeReportTest extends TestCase
{
    public function testAdminCanAccessMonthlyTimeReports(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = MonthlyWorkHour::all()->count();

        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/time-reports')
            ->assertInertia(fn (Assert $page) => $page
                ->component('TimeReports/Overview/index')
                ->has('monthlyTimeReports', $total)
                ->has('monthlyTimeReports.0', fn (Assert $page) => $page
                    ->has('userId')
                    ->has('fullname')
                    ->has('month')
                    ->has('year')
                    ->has('totalWorkHours')
                    ->has('hasDeviation')
                    ->has('bookingHours')
                    ->has('adjustmentHours')
                    ->has('totalHours')
                    ->etc())
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessMonthlyTimeReports(): void
    {
        $this->actingAs($this->user)
            ->get('/time-reports')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterMonthlyTimeReports(): void
    {
        $pageSize = config('downstairs.pageSize');
        $worker = User::role('Worker')->first();
        $count = MonthlyWorkHour::where('user_id', $worker->id)->count();

        $this->actingAs($this->admin)
            ->get("/time-reports?userId=$worker->id")
            ->assertInertia(fn (Assert $page) => $page
                ->component('TimeReports/Overview/index')
                // ->has('monthlyTimeReports', $count)
                ->has('monthlyTimeReports.0', fn (Assert $page) => $page
                    ->has('userId')
                    ->has('fullname')
                    ->has('month')
                    ->has('year')
                    ->has('totalWorkHours')
                    ->has('hasDeviation')
                    ->has('bookingHours')
                    ->has('adjustmentHours')
                    ->has('totalHours')
                    ->etc())
                ->has('pagination', fn (Assert $page) => $page
                    // ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessMonthlyTimeReportsJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/time-reports/json');
        $workHour = MonthlyWorkHour::first();
        $keys = array_keys(
            MonthlyWorkHourResponseDTO::from($workHour)->toArray()
        );

        $response->assertStatus(200);

        // $response->assertJsonStructure([
        //     'data' => [
        //         '*' => $keys,
        //     ],
        //     'meta' => [
        //         'etag',
        //     ],
        // ]);
    }
}
