<?php

namespace App\Http\Controllers\Api\V2;

use App\DTOs\Schedule\ScheduleChangeRequestDTO;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\Enums\Schedule\ScheduleChangeStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Models\Schedule;

class ScheduleChangeRequestController extends Controller
{
    use QueryStringTrait;
    use ResponseTrait;

    /**
     * List of additional fields to be included in the response.
     *
     * @var string[]
     */
    protected array $includes = [
        'subscription.service',
        'team',
        'customer.address.city.country',
        'property.address.city.country',
        'property.type',
        'products',
        'changeRequest',
    ];

    /**
     * Display a listing of the resource.
     */
    public function store(
        int $schedule,
        ScheduleChangeRequestDTO $request
    ) {
        $data = Schedule::ofAuthUser()->findOrFail($schedule);

        $this->authorize('change', $data);

        $request->assignIfOptional('start_at_changed', $data->start_at);
        $calendarQuarters = calculate_calendar_quarters(
            $data->quarters,
            $data->scheduleEmployees->count()
        );
        $endAt = $request->start_at_changed->clone()->addMinutes($calendarQuarters * 15);

        $attributes = [
            ...$request->toArray(),
            'end_at_changed' => $endAt,
            'status' => ScheduleChangeStatusEnum::Pending(),
        ];

        if ($data->changeRequest) {
            $data->changeRequest->update($attributes);
        } else {
            $data->changeRequest()->create($attributes);
        }

        return $this->successResponse(
            ScheduleResponseDTO::transformData($data, $this->includes)
        );
    }
}
