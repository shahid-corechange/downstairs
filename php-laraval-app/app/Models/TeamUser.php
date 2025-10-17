<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TeamUser extends Pivot
{
    use LogsActivity;

    public $incrementing = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
