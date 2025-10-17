<?php

namespace App\Http\Controllers\Api\V2;

use App\DTOs\Schedule\ScheduleResponseDTO;
use App\DTOs\Service\ServiceResponseDTO;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Http\Controllers\Controller;
use App\Http\Traits\QueryStringTrait;
use App\Http\Traits\ResponseTrait;
use App\Http\Traits\UserSettingTrait;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\Subscription;
use Auth;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use QueryStringTrait;
    use ResponseTrait;
    use UserSettingTrait;

    /**
     * List of additional fields to be included in the response.
     *
     * @var string[]
     */
    protected array $includes = [
        'properties.address.city.country',
        'properties.type',
        'info',
    ];

    /**
     * Display the user schedule cleanings information.
     */
    public function schedules(): JsonResponse
    {
        $queries = $this->getQueries(
            filter: [
                'subscription_userId_eq' => Auth::id(),
            ],
            sort: ['start_at' => 'asc']
        );
        $paginatedData = Schedule::applyFilterSortAndPaginate($queries);

        $groupedData = array_reduce($paginatedData->data, function (array $carry, Schedule $item) {
            $includedFields = [
                'service',
                'subscription',
                'team',
                'customer.address.country',
                'property.address.country',
                'property.type',
                'addons',
                'products',
                'changeRequest',
            ];
            $dto = ScheduleResponseDTO::transformData($item, $includedFields);
            $monthIndex = 'm'.$item->start_at->timezone('Europe/Stockholm')->month - 1;
            $date = $item->start_at->timezone('Europe/Stockholm')->format('Y-m-d');

            if (! isset($carry[$monthIndex])) {
                $carry[$monthIndex] = [];
            }

            if (! isset($carry[$monthIndex][$date])) {
                $carry[$monthIndex][$date] = [];
            }

            $carry[$monthIndex][$date][] = $dto;

            return $carry;
        }, []);

        return $this->successResponse(
            $groupedData,
            pagination: $paginatedData->pagination
        );
    }

    /**
     * Display frequencies of subscription for filter.
     */
    public function scheduleFrequencies()
    {
        $frequencies = Subscription::where('user_id', Auth::id())
            ->where('is_paused', '=', false)
            ->where(function ($query) {
                return $query->where('end_at', '>=', now())->orWhere('end_at', '=', null);
            })->distinct()
            ->orderBy('frequency')->pluck('frequency')->toArray();

        $result = array_reduce($frequencies, function ($carry, $item) {
            // Skip the once frequency because it's always shown in the frontend
            if ($item === SubscriptionFrequencyEnum::Once()) {
                return $carry;
            }

            try {
                $val = SubscriptionFrequencyEnum::from($item);
                $carry[] = [
                    'value' => $val->value,
                    'name' => __($val->name),
                ];

                return $carry;
            } catch (\ValueError $e) {
                return $carry;
            }
        }, []);

        return $this->successResponse($result);
    }

    /**
     * Display services of subscription for filter.
     */
    public function scheduleServices()
    {
        $services = Service::whereIn('id', function ($query) {
            $query->select('service_id')
                ->from('subscriptions')
                ->where('user_id', Auth::id())
                ->where('is_paused', '=', false)
                ->where('deleted_at', '=', null)
                ->where(function ($subQuery) {
                    $subQuery->where('end_at', '>=', now())
                        ->orWhereNull('end_at');
                });
        })->orderBy('id')->get();

        return $this->successResponse(
            ServiceResponseDTO::transformCollection($services)
        );
    }
}
