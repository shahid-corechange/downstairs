<?php

namespace App\Enums\Auth;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum TokenAbilityEnum: string
{
    use InvokableCases;
    use Values;

    case APIAccess = 'api-access';
    case IssueAccessToken = 'issue-access-token';
}
