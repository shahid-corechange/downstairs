<?php

namespace App\Console\Commands;

use App\Models\ScheduleCleaning;
use App\Services\MigrateScheduleService;
use DB;
use Illuminate\Console\Command;

class MigrateScheduleCleaningsByDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule-cleanings:migrate-by-date {start_date} {end_date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate schedule cleanings to schedules by date';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(MigrateScheduleService $service)
    {
        $startDate = $this->argument('start_date');
        $endDate = $this->argument('end_date');

        $cleanings = $this->getScheduleCleanings($startDate, $endDate);

        foreach ($cleanings as $cleaning) {
            if (! $cleaning->schedule) {
                $service->migrate($cleaning);
            }
        }
    }

    /**
     * Get schedule cleanings that are not referenced in the schedules table.
     * These are orphaned schedule cleanings that don't have a corresponding schedule record.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getScheduleCleanings(string $startDate, string $endDate)
    {
        return ScheduleCleaning::withTrashed()
            ->whereBetween('start_at', [$startDate, $endDate])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('schedules')
                    ->whereColumn('schedules.scheduleable_id', 'schedule_cleanings.id')
                    ->where('schedules.scheduleable_type', ScheduleCleaning::class);
            })
            ->get();
    }
}
