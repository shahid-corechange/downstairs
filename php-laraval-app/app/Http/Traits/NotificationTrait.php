<?php

namespace App\Http\Traits;

use App\Contracts\NotificationService;
use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\PermissionsEnum;
use App\Models\User;

trait NotificationTrait
{
    /**
     * Get the notification hub name based on user data.
     */
    private function setHub(User $user, NotificationService $notification): NotificationService
    {
        $app = request()->header('X-Requested-By');

        if ($app === 'Customer' && $user->can(PermissionsEnum::AccessCustomerApp())) {
            $notification->hub(NotificationHubEnum::Customer());
        } else {
            $notification->hub(NotificationHubEnum::Employee());
        }

        return $notification;
    }

    /**
     * Create a new user tag with user id.
     */
    private function userTag(int $userId): string
    {
        return 'user_'.$userId;
    }

    /**
     * Create a new team tag with team id.
     */
    private function teamTag(int $teamId): string
    {
        return 'team_'.$teamId;
    }

    /**
     * Create a new team tag with team ids.
     *
     * @param  \App\Models\Team[]|\Illuminate\Database\Eloquent\Collection<int, \App\Models\Team>  $teams
     * @return string[]
     */
    private function teamsTag($teams): array
    {
        $newTags = [];

        foreach ($teams as $team) {
            array_push($newTags, $this->teamTag($team->id));
        }

        return $newTags;
    }

    /**
     * Set tag by user role.
     *
     * @return string[]
     */
    private function setUserTag(User $user): array
    {
        $tags = [];

        if ($user->can(PermissionsEnum::AccessCustomerApp())) {
            array_push($tags, $this->userTag($user->id));
        } elseif ($user->can(PermissionsEnum::AccessEmployeeApp())) {
            array_push($tags, $this->userTag($user->id));
            $tags = [...$tags, ...$this->teamsTag($user->teams)];
        }

        return $tags;
    }

    /**
     * Register a new device if not exists in notification hub.
     */
    private function register(
        User $user,
        NotificationService $notification,
        string $devicePlatform,
        string $deviceToken
    ): void {
        if ($user->canAny([PermissionsEnum::AccessCustomerApp(), PermissionsEnum::AccessEmployeeApp()])) {
            $tags = $this->setUserTag($user);
            $notification = $this->setHub($user, $notification);

            if (! $notification->isRegistered($devicePlatform, $deviceToken)) {
                $notification->register($devicePlatform, $deviceToken, $tags);
            }
        }
    }

    /**
     * Unregister a device from notification hub.
     */
    private function unregister(
        User $user,
        NotificationService $notification,
        string $devicePlatform,
        string $deviceToken
    ): void {
        if ($user->canAny([PermissionsEnum::AccessCustomerApp(), PermissionsEnum::AccessEmployeeApp()])) {
            $this->setHub($user, $notification)->unregister($devicePlatform, $deviceToken);
        }
    }
}
