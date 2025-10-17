<?php

namespace App\Services\LaundryOrder;

use App\DTOs\Schedule\BuildScheduleDTO;
use App\Enums\MembershipTypeEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\ScheduleLaundry\ScheduleLaundryTypeEnum;
use App\Jobs\LaundryOrder\CreateLaundryOrderScheduleJob;
use App\Jobs\LaundryOrder\RemoveLaundryOrderScheduleJob;
use App\Jobs\LaundryOrder\UpdateLaundryOrderScheduleJob;
use App\Models\LaundryOrder;
use App\Models\Property;
use App\Models\Schedule;
use Carbon\Carbon;

class LaundryOrderService
{
    /**
     * Compose the laundry order
     *
     * @param  LaundryOrder  $laundryOrder
     */
    public function composeSchedules(
        $laundryOrder,
    ) {

        $type = $laundryOrder->customer->membership_type;
        $serviceId = $type === MembershipTypeEnum::Private() ? config('downstairs.services.laundry.private.id') :
            config('downstairs.services.laundry.company.id');

        $pickupSchedule = $laundryOrder->pickup_property_id ?
            $this->composePickupSchedule($laundryOrder, $serviceId) : null;
        $deliverySchedule = $laundryOrder->delivery_property_id ?
            $this->composeDeliverySchedule($laundryOrder, $serviceId) : null;

        return array_filter(
            [$pickupSchedule, $deliverySchedule],
            fn ($schedule) => $schedule !== null,
        );
    }

    /**
     * Update the teams of the laundry order
     *
     * @param  LaundryOrder  $laundryOrder
     * @param  array  $oldData
     */
    public function updateTeams($laundryOrder, $oldData)
    {
        $type = $laundryOrder->customer->membership_type;
        $serviceId = $type === MembershipTypeEnum::Private() ? config('downstairs.services.laundry.private.id') :
            config('downstairs.services.laundry.company.id');

        // pickup section
        if ($laundryOrder->pickup_team_id !== $oldData['pickup_team_id']) {
            if ($oldData['pickup_team_id']) {
                // delete the pickup schedule and notify the old team
                /** @var Schedule $schedule */
                $schedule = $laundryOrder->pickupSchedules()
                    ->where('team_id', $oldData['pickup_team_id'])
                    ->first();
                if ($schedule) {
                    RemoveLaundryOrderScheduleJob::dispatchAfterResponse($schedule, $laundryOrder);
                }
            }

            if ($laundryOrder->pickup_team_id) {
                $schedule = $this->composePickupSchedule($laundryOrder, $serviceId);
                CreateLaundryOrderScheduleJob::dispatchAfterResponse($schedule, $laundryOrder);
            }
        } elseif ($laundryOrder->pickup_team_id) {
            // update the pickup schedule
            /** @var Schedule $schedule */
            $schedule = $laundryOrder->pickupSchedules()
                ->where('team_id', $oldData['pickup_team_id'])
                ->first();
            if ($schedule) {
                [$startAt, $endAt] = $this->getStartEndPickupTime($laundryOrder);
                UpdateLaundryOrderScheduleJob::dispatchAfterResponse(
                    $laundryOrder,
                    $schedule,
                    $startAt,
                    $endAt,
                    $laundryOrder->pickup_property_id,
                );
            }
        }

        // delivery section
        if ($laundryOrder->delivery_team_id !== $oldData['delivery_team_id']) {
            if ($oldData['delivery_team_id']) {
                // delete the delivery schedule and notify the old team
                /** @var Schedule $schedule */
                $schedule = $laundryOrder->deliverySchedules()
                    ->where('team_id', $oldData['delivery_team_id'])
                    ->first();
                if ($schedule) {
                    RemoveLaundryOrderScheduleJob::dispatchAfterResponse($schedule, $laundryOrder);
                }
            }

            if ($laundryOrder->delivery_team_id) {
                $schedule = $this->composeDeliverySchedule($laundryOrder, $serviceId);
                CreateLaundryOrderScheduleJob::dispatchAfterResponse($schedule, $laundryOrder);
            }
        } elseif ($laundryOrder->delivery_team_id) {
            // update the delivery schedule
            /** @var Schedule $schedule */
            $schedule = $laundryOrder->deliverySchedules()
                ->where('team_id', $oldData['delivery_team_id'])
                ->first();
            if ($schedule) {
                [$startAt, $endAt] = $this->getStartEndDeliveryTime($laundryOrder);
                UpdateLaundryOrderScheduleJob::dispatchAfterResponse(
                    $laundryOrder,
                    $schedule,
                    $startAt,
                    $endAt,
                    $laundryOrder->delivery_property_id,
                );
            }
        }
    }

    /**
     * Compose the pickup schedule
     *
     * @param  LaundryOrder  $laundryOrder
     * @param  int  $serviceId
     * @return BuildScheduleDTO
     */
    private function composePickupSchedule($laundryOrder, $serviceId)
    {
        [$startAt, $endAt] = $this->getStartEndPickupTime($laundryOrder);
        $property = Property::find($laundryOrder->pickup_property_id);

        return BuildScheduleDTO::from([
            'user_id' => $laundryOrder->user_id,
            'service_id' => $serviceId,
            'team_id' => $laundryOrder->pickup_team_id,
            'customer_id' => $laundryOrder->customer_id,
            'property_id' => $laundryOrder->pickup_property_id,
            'status' => ScheduleStatusEnum::Booked(),
            'start_at' => $startAt->toDateTimeString(),
            'end_at' => $endAt->toDateTimeString(),
            'quarters' => 1,
            'key_information' => $property->key_description,
            'note' => ['note' => ''],
            'type' => ScheduleLaundryTypeEnum::Pickup(),
        ]);
    }

    /**
     * Compose the delivery schedule
     *
     * @param  LaundryOrder  $laundryOrder
     *  @param  int  $serviceId
     * @return BuildScheduleDTO
     */
    private function composeDeliverySchedule($laundryOrder, $serviceId)
    {
        [$startAt, $endAt] = $this->getStartEndDeliveryTime($laundryOrder);
        $property = Property::find($laundryOrder->delivery_property_id);

        return BuildScheduleDTO::from([
            'user_id' => $laundryOrder->user_id,
            'service_id' => $serviceId,
            'team_id' => $laundryOrder->delivery_team_id,
            'customer_id' => $laundryOrder->customer_id,
            'property_id' => $laundryOrder->delivery_property_id,
            'status' => ScheduleStatusEnum::Booked(),
            'start_at' => $startAt->toDateTimeString(),
            'end_at' => $endAt->toDateTimeString(),
            'quarters' => 1,
            'key_information' => $property->key_description,
            'note' => ['note' => ''],
            'type' => ScheduleLaundryTypeEnum::Delivery(),
        ]);
    }

    private function getStartEndPickupTime($laundryOrder)
    {
        $startAt = Carbon::parse($laundryOrder->ordered_at)
            ->setTimeFromTimeString($laundryOrder->pickup_time);
        $endAt = $startAt->copy()->addMinutes(15);

        return [$startAt, $endAt];
    }

    private function getStartEndDeliveryTime($laundryOrder)
    {
        $startAt = $laundryOrder->due_at;
        $endAt = $startAt->copy()->addMinutes(15);

        return [$startAt, $endAt];
    }
}
