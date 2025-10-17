<?php

namespace App\Models;

use App\Http\Traits\ModelQueryStringTrait;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use ModelQueryStringTrait;
}
