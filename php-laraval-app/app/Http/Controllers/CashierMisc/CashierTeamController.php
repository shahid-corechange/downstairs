<?php

namespace App\Http\Controllers\CashierMisc;

use App\DTOs\Team\TeamResponseDTO;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Models\BlockDay;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class CashierTeamController extends BaseUserController
{
    use ResponseTrait;

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $startAt = request()->query('startAt');
        $excludeTeamIds = request()->query('excludeTeamIds');
        $start = $startAt ? Carbon::parse($startAt) : now();
        $end = $start->copy()->addMinutes(15);

        $blockDays = BlockDay::where('block_date', '=', $start->toDateString())->count();

        if ($blockDays === 0) {
            $teams = Team::whereDoesntHave('schedules', function (Builder $query) use ($start, $end, $excludeTeamIds) {
                $query->where(function (Builder $query) use ($start, $end) {
                    $query->where('start_at', '<', $end)
                        ->orWhere('end_at', '>', $start);
                })->whereNotIn('team_id', $excludeTeamIds ? explode(',', $excludeTeamIds) : []);
            })->orWhereHas('schedules', function (Builder $query) use ($start, $end) {
                $query->where(function (Builder $query) use ($start, $end) {
                    $query->where('start_at', '<', $start)
                        ->where('end_at', '>', $end);
                })->where('status', ScheduleStatusEnum::Cancel());
            })
                ->get();
        } else {
            $teams = collect();
        }

        return $this->successResponse(
            TeamResponseDTO::transformCollection($teams, onlys: ['id', 'name']),
        );
    }
}
