<?php

namespace App\Services\Subscription;

use App\DTOs\Service\ServiceResponseDTO;
use App\DTOs\Team\TeamResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\MembershipTypeEnum;
use App\Enums\Service\ServiceTypeEnum;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Enums\Subscription\SubscriptionRefillSequenceEnum;
use App\Models\Service;
use App\Models\Team;
use App\Models\User;

class SubscriptionViewService
{
    public function getFrequencies()
    {
        $results = [];
        foreach (SubscriptionFrequencyEnum::options() as $key => $value) {
            $results[$value] = __($key);
        }

        return $results;
    }

    public function getRefillSequences()
    {
        $globalRefillSequence = get_setting(
            GlobalSettingEnum::SubscriptionRefillSequence(),
            config('downstairs.subscription.refillSequence')
        );

        $refillSequences = [];

        foreach (SubscriptionRefillSequenceEnum::options() as $key => $value) {
            if ($value <= $globalRefillSequence) {
                $refillSequences[$value] = __($key);
            }
        }

        return $refillSequences;
    }

    /**
     * Calculate the quarters
     */
    public function getQuarters(string $startTimeAt, string $endTimeAt)
    {
        $startTime = \DateTime::createFromFormat('H:i:s', $startTimeAt);
        $endTime = \DateTime::createFromFormat('H:i:s', $endTimeAt);
        $intervalMinutes = ($endTime->getTimestamp() - $startTime->getTimestamp()) / 60;

        return $intervalMinutes / 15;
    }

    public function getTeams()
    {
        $onlys = ['id', 'name', 'users.id', 'users.fullname'];
        $teams = Team::selectWithRelations($onlys)
            ->whereHas('users')
            ->get();

        return TeamResponseDTO::collection($teams)->include('users')->only(...$onlys);
    }

    public function getServices(string $type)
    {
        $onlys = [
            'id',
            'name',
            'priceWithVat',
            'addons.id',
            'addons.name',
            'addons.priceWithVat',
            'addons.creditPrice',
            'quarters.minSquareMeters',
            'quarters.maxSquareMeters',
            'quarters.quarters',
        ];
        $query = Service::selectWithRelations($onlys)
            ->where('type', ServiceTypeEnum::Cleaning());

        if ($type === MembershipTypeEnum::Private()) {
            $services = $query->private()->get();
        } else {
            $services = $query->company()->get();
        }

        return ServiceResponseDTO::collection($services)
            ->include('addons', 'quarters')
            ->only(...$onlys);
    }

    public function getUsers(string $type)
    {
        $onlys = [
            'id',
            'fullname',
            'cellphone',
        ];
        $role = $type === MembershipTypeEnum::Private() ? 'Customer' : 'Company';

        $users = User::selectWithRelations($onlys)
            ->whereHas('roles', function ($query) use ($role) {
                $query->where('name', $role);
            })
            ->whereHas('customers', function ($query) use ($type) {
                $query->where('membership_type', $type);
            })
            ->get();

        return UserResponseDTO::collection($users)->only(...$onlys);
    }
}
