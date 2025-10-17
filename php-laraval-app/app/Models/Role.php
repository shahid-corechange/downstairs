<?php

namespace App\Models;

use App\Http\Traits\ModelQueryStringTrait;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use ModelQueryStringTrait;
}
