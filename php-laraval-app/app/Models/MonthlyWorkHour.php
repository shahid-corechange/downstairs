<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model for worked_hours view table.
 */
class MonthlyWorkHour extends ViewModel
{
    protected $table = 'monthly_work_hours';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'has_deviation',
        'total_hours',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'has_deviation' => ['schedule_deviation', 'schedule_employee_deviation'],
        'total_hours' => ['total_work_hours', 'adjustment_hours'],
    ];

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', 'employee_id');
    }

    /**
     * Get the has deviation attribute.
     */
    public function getHasDeviationAttribute(): bool
    {
        return $this->schedule_deviation > 0 || $this->schedule_employee_deviation > 0;
    }

    /**
     * Get the total hours attribute.
     */
    public function getTotalHoursAttribute(): float
    {
        return $this->total_work_hours + $this->adjustment_hours;
    }
}
