<?php

namespace App\Policies;

use App\Enums\Azure\NotificationHub\NotificationHubEnum;
use App\Enums\PermissionsEnum;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class NotificationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Notification $notification): Response
    {
        $app = request()->header('X-Requested-By');
        $hub = $app === 'Customer' && $user->can(PermissionsEnum::AccessCustomerApp()) ?
            NotificationHubEnum::Customer() : NotificationHubEnum::Employee();

        return $notification->hub === $hub && $notification->user_id === $user->id ?
            $this->allow() :
            $this->denyAsNotFound(__('notification not found'));
    }
}
