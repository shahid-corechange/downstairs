<?php

namespace App\Console\Commands;

use App\Models\ScheduleCleaning;
use App\Services\MigrateScheduleService;
use DB;
use Illuminate\Console\Command;

class MigrateScheduleCleanings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule-cleanings:migrate {start_id} {end_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate schedule cleanings to schedules';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(MigrateScheduleService $service)
    {
        $startId = $this->argument('start_id');
        $endId = $this->argument('end_id');

        $cleanings = $this->getScheduleCleanings($startId, $endId);

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
    private function getScheduleCleanings(int $startId, int $endId)
    {
        return ScheduleCleaning::withTrashed()
            ->whereBetween('id', [$startId, $endId])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('schedules')
                    ->whereColumn('schedules.scheduleable_id', 'schedule_cleanings.id')
                    ->where('schedules.scheduleable_type', ScheduleCleaning::class);
            })
            ->get();
    }
}
