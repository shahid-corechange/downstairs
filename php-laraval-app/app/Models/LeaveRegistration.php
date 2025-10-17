<?php

namespace App\Models;

use App\Enums\GlobalSetting\GlobalSettingEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRegistration extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'employee_id',
        'type',
        'start_at',
        'end_at',
        'is_stopped',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'reschedule_needed',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_stopped' => 'boolean',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'reschedule_needed' => ['start_at', 'end_at'],
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'reschedule_needed' => [
            'employee.user.activeScheduleEmployees' => ['status'],
            'employee.user.activeScheduleEmployees.schedule' => ['start_at', 'end_at'],
        ],
    ];

    public function getRescheduleNeededAttribute(): bool
    {
        $days = get_setting(GlobalSettingEnum::AbsenceRescheduling(), 7);
        // If the start date is in the past, use the current date as the start date.
        $start = $this->start_at->isPast() ? now() : $this->start_at;
        // If the end date is not set, use the start date plus the number of days.
        $end = $this->end_at ?? $start->copy()->addDays($days);

        return $this->employee->user->activeScheduleEmployees->filter(function ($scheduleEmployee) use ($start, $end) {
            if (! $scheduleEmployee->schedule) {
                return false;
            }

            return $scheduleEmployee->schedule->start_at >= $start
                && $scheduleEmployee->schedule->start_at <= $end;
        })->isNotEmpty();
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }

    public function details(): HasMany
    {
        return $this->hasMany(LeaveRegistrationDetail::class, 'leave_registration_id');
    }
}
