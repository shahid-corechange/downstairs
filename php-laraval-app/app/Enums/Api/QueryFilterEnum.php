<?php

namespace App\Enums\Api;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: equal, eq, notEqual, neq, greaterThanOrEqual, gte,
 * lessThanOrEqual, lte, lessThan, lt, includes, like, in,
 * notIn, between
 */
enum QueryFilterEnum: string
{
    use InvokableCases;
    use Values;

    case Equal = 'equal';
    case Eq = 'eq';
    case NotEqual = 'notEqual';
    case Neq = 'neq';
    case GreaterThanOrEqual = 'greaterThanOrEqual';
    case Gte = 'gte';
    case LessThanOrEqual = 'lessThanOrEqual';
    case Lte = 'lte';
    case GreaterThan = 'greaterThan';
    case Gt = 'gt';
    case LessThan = 'lessThan';
    case Lt = 'lt';
    case Includes = 'includes';
    case Like = 'like';
    case In = 'in';
    case NotIn = 'notIn';
    case Between = 'between';
    case Nullable = 'nullable';
}
