<?php

namespace App\Http\Controllers\Blockday;

use App\DTOs\BlockDay\BlockDayResponseDTO;
use App\DTOs\BlockDay\CreateBlockDayRequestDTO;
use App\DTOs\BlockDay\UpdateBlockDayRequestDTO;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\BlockDay;
use App\Models\Schedule;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BlockDayController extends Controller
{
    use ResponseTrait;

    private array $onlys = [
        'id',
        'blockDate',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(size: -1);
        $paginatedData = BlockDay::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        try {
            $day = request()->query('day');
            $startOfDay = $day ? Carbon::createFromFormat('Y-m-d', $day)->startOfDay() :
                now()->startOfDay();
            $endOfDay = $day ? Carbon::createFromFormat('Y-m-d', $day)->endOfDay() :
                now()->endOfDay();
        } catch (InvalidFormatException) {
            $startOfDay = now()->startOfDay();
            $endOfDay = now()->endOfDay();
        }

        return Inertia::render('Blockday/Overview/index', [
            'blockdays' => BlockDayResponseDTO::transformCollection($paginatedData->data, onlys: $this->onlys),
            'schedules' => $this->getSchedules($startOfDay, $endOfDay),
        ]);
    }

    private function getSchedules(Carbon $startOfDay, Carbon $endOfDay)
    {
        $onlys = [
            'id',
            'startAt',
            'endAt',
            'teamId',
            'team.name',
        ];

        $schedules = Schedule::selectWithRelations($onlys)
            ->booked()
            ->where(function (Builder $query) use ($startOfDay, $endOfDay) {
                $query->whereBetween('start_at', [$startOfDay, $endOfDay])
                    ->orWhereBetween('end_at', [$startOfDay, $endOfDay]);
            })
            ->get();

        return ScheduleResponseDTO::collection($schedules)
            ->include('team')
            ->only(...$onlys);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries();
        $paginatedData = BlockDay::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            BlockDayResponseDTO::transformCollection($paginatedData->data)
        );
    }

    /**
     * Store resource in storage.
     */
    public function store(CreateBlockDayRequestDTO $request): RedirectResponse
    {
        $isExists = BlockDay::where('block_date', '=', $request->block_date)
            ->first();

        if (! $isExists) {
            $startOfDay = Carbon::create($request->block_date.$request->start_block_time);
            $endOfDay = Carbon::create($request->block_date.$request->end_block_time);

            $count = Schedule::booked()
                ->where(function (Builder $query) use ($startOfDay, $endOfDay) {
                    $query->whereBetween('start_at', [$startOfDay, $endOfDay])
                        ->orWhereBetween('end_at', [$startOfDay, $endOfDay]);
                })
                ->count();

            if ($count > 0) {
                return back()->with('error', __('can not block day', ['schedule' => $count]));
            } else {
                BlockDay::create($request->toArray());
            }
        }

        return back()->with('success', __('block day created successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBlockDayRequestDTO $request, BlockDay $blockday): RedirectResponse
    {
        $blockday->update($request->toArray());

        return back()->with('success', __('block day updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BlockDay $blockday): RedirectResponse
    {
        $blockday->delete();

        return back()->with('success', __('block day deleted successfully'));
    }
}
