<?php

namespace Tests\Portal\Management;

use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Models\BlockDay;
use App\Models\ScheduleCleaning;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BlockDayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        BlockDay::factory(10)->create();
    }

    public function testAdminCanAccessBlockDays(): void
    {
        $startOfDay = now()->startOfDay();
        $endOfDay = now()->endOfDay();
        $blockDays = BlockDay::all();
        $schedules = ScheduleCleaning::active()
            ->where(function (Builder $query) use ($startOfDay, $endOfDay) {
                $query->whereBetween('start_at', [$startOfDay, $endOfDay])
                    ->orWhereBetween('end_at', [$startOfDay, $endOfDay]);
            })
            ->get();

        $this->actingAs($this->admin)
            ->get('/blockdays')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Blockday/Overview/index')
                ->has('blockdays', $blockDays->count())
                ->has('blockdays.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('blockDate'))
                ->has('schedules', $schedules->count()));
    }

    public function testCustomerCanNotAccessBlockDays(): void
    {
        $this->actingAs($this->user)
            ->get('/blockdays')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanCreateBlockDay(): void
    {
        $data = [
            'blockDate' => now()->addYears(2)->format('Y-m-d'),
        ];

        $this->actingAs($this->admin)
            ->post('/blockdays', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('block day created successfully'));

        $this->assertDatabaseHas('block_days', [
            'block_date' => $data['blockDate'],
            'start_block_time' => '00:00:00',
            'end_block_time' => '23:59:59',
        ]);
    }

    public function testCanNotCreateBlockDayIfThereAreSchedules(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->first();
        $date = Carbon::parse($schedule->start_at)->utc()->format('Y-m-d');
        $data = [
            'blockDate' => $date,
        ];

        $startOfDay = Carbon::create($date.'00:00:00')->utc();
        $endOfDay = Carbon::create($date.'23:59:59')->utc();

        $count = ScheduleCleaning::active()
            ->where(function (Builder $query) use ($startOfDay, $endOfDay) {
                $query->whereBetween('start_at', [$startOfDay, $endOfDay])
                    ->orWhereBetween('end_at', [$startOfDay, $endOfDay]);
            })
            ->count();

        $this->actingAs($this->admin)
            ->post('/blockdays', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('can not block day', ['schedule' => $count]));
    }

    public function testCanUpdateBlockDay(): void
    {
        $blockDay = BlockDay::first();
        $data = [
            'blockDate' => now()->addYears(2)->format('Y-m-d'),
        ];

        $this->actingAs($this->admin)
            ->patch("/blockdays/{$blockDay->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('block day updated successfully'));

        $this->assertDatabaseHas('block_days', [
            'id' => $blockDay->id,
            'block_date' => $data['blockDate'],
        ]);
    }

    public function testCanDeleteBlockDay(): void
    {
        $blockDay = BlockDay::first();

        $this->actingAs($this->admin)
            ->delete("/blockdays/{$blockDay->id}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('block day deleted successfully'));

        $this->assertDatabaseMissing('block_days', [
            'id' => $blockDay->id,
        ]);
    }
}
