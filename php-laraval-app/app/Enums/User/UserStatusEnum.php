<?php

namespace App\Enums\User;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: active, inactive, suspended, deleted, pending, blocked
 */
enum UserStatusEnum: string
{
    use InvokableCases;
    use Values;

    // The user account is currently active and can be used to access the application.
    case Active = 'active';

    // The user account is currently inactive and cannot be used to access the application until it is reactivated.
    case Inactive = 'inactive';

    // The user account is temporarily suspended
    // and cannot be used to access the application until the suspension is lifted.
    case Suspended = 'suspended';

    // The user account has been deleted from the database and cannot be used to access the application.
    case Deleted = 'deleted';

    // The user account has been created but is pending approval
    // or verification before it can be used to access the application.
    case Pending = 'pending';

    // The user account has been blocked from accessing the application due to a violation of the terms and conditions.
    case Blocked = 'blocked';
}
