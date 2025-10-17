<?php

namespace Tests\Model;

use App\Models\MonthlyWorkHour;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MonthlyWorkHourTest extends TestCase
{
    /** @test */
    public function monthlyWorkHoursDatabaseHasExpectedColumns(): void
    {
        // First check if the view exists
        $viewExists = DB::select("
             SELECT COUNT(*) as count 
             FROM information_schema.views 
             WHERE table_schema = DATABASE()
             AND table_name = 'monthly_work_hours'
         ")[0]->count > 0;

        $this->assertTrue($viewExists, 'The monthly_work_hours_view does not exist');

        // Then check columns
        $columns = DB::getSchemaBuilder()->getColumnListing('monthly_work_hours');

        $expectedColumns = [
            'user_id',
            'fortnox_id',
            'fullname',
            'month',
            'year',
            'total_work_hours',
            'adjustment_hours',
            'booking_hours',
            'schedule_deviation',
            'schedule_employee_deviation',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $columns, "Column '$column' not found in view");
        }
    }

    /** @test */
    public function monthlyWorkHourHasTotalHours(): void
    {
        $workHour = MonthlyWorkHour::first();
        if ($workHour) {
            $this->assertIsFloat($workHour->total_hours);
        } else {
            $this->assertNull($workHour);
        }
    }

    /** @test */
    public function monthlyWorkHourHasHasDeviation(): void
    {
        $workHour = MonthlyWorkHour::first();
        if ($workHour) {
            $this->assertIsBool($workHour->has_deviation);
        } else {
            $this->assertNull($workHour);
        }
    }
}
