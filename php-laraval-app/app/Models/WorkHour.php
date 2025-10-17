<?php

namespace App\Models;

use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Enums\WorkHour\WorkHourTypeEnum;
use App\Services\WorkHourService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkHour extends Model
{
    use HasFactory;

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
        'user_id',
        'fortnox_attendance_id',
        'type',
        'date',
        'start_time',
        'end_time',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'work_hours',
        'time_adjustment_hours',
        'total_hours',
        'unapproved_hours',
        'has_deviation',
        'booking_hours',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'work_hours' => ['date', 'start_time', 'end_time', 'type'],
        'total_hours' => ['date', 'start_time', 'end_time', 'type'],
        'unapproved_hours' => ['date', 'start_time', 'end_time', 'user_id', 'type'],
        'has_deviation' => ['user_id', 'date'],
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'time_adjustment_hours' => [
            'schedules.timeAdjustment' => ['quarters'],
        ],
        'total_hours' => [
            'schedules.timeAdjustment' => ['quarters'],
            'attendances' => ['check_in_at', 'check_out_at'],
        ],
        'booking_hours' => [
            'schedules' => ['start_at', 'end_at'],
        ],
        'work_hours' => [
            'attendances' => ['check_in_at', 'check_out_at'],
        ],
    ];

    /**
     * Get the total hours attribute.
     */
    public function getWorkHoursAttribute(): float
    {
        if ($this->type === WorkHourTypeEnum::Store()) {
            return $this->attendances->sum(function (CashierAttendance $attendance) {
                return $attendance->total_hours;
            });
        }

        return WorkHourService::calculate($this->date, $this->start_time, $this->end_time);
    }

    /**
     * Get the time adjustment hours attribute.
     */
    public function getTimeAdjustmentHoursAttribute(): float
    {
        return $this->schedules->sum('time_adjustment_hours');
    }

    /**
     * Get the total hours attribute.
     */
    public function getTotalHoursAttribute(): float
    {
        return $this->work_hours + $this->time_adjustment_hours;
    }

    /**
     * Get the has deviation attribute.
     */
    public function getHasDeviationAttribute(): bool
    {
        $startAt = $this->date
            ->copy()
            ->startOfDay()
            ->shiftTimezone('Europe/Stockholm')
            ->utc();
        $endAt = $this->date
            ->copy()
            ->endOfDay()
            ->shiftTimezone('Europe/Stockholm')
            ->utc();

        $totalScheduleDeviations = ScheduleCleaningDeviation::unhandled()
            ->whereHas('scheduleCleaning', function (Builder $query) use ($startAt, $endAt) {
                $query->where('start_at', '>=', $startAt)
                    ->where('end_at', '<=', $endAt)
                    ->whereHas('scheduleEmployees', function (Builder $query) {
                        $query->where('user_id', $this->user_id);
                    });
            })
            ->count();

        if ($totalScheduleDeviations > 0) {
            return true;
        }

        $totalEmployeeDeviations = Deviation::unhandled()
            ->where('user_id', $this->user_id)
            ->whereHas('scheduleCleaning', function (Builder $query) use ($startAt, $endAt) {
                $query->where('start_at', '>=', $startAt)
                    ->where('end_at', '<=', $endAt);
            })
            ->count();

        return $totalEmployeeDeviations > 0;
    }

    /**
     * Get the booking hours attribute.
     */
    public function getBookingHoursAttribute(): float
    {
        return $this->schedules->sum(function (ScheduleEmployee $schedule) {
            return $schedule->work_quarters / 4;
        });
    }

    /**
     * .
     */
    public function getUnapprovedHoursAttribute()
    {
        $startOfDay = $this->date
            ->copy()
            ->startOfDay()
            ->shiftTimezone('Europe/Stockholm')
            ->utc();
        $endOfDay = $this->date
            ->copy()
            ->endOfDay()
            ->shiftTimezone('Europe/Stockholm')
            ->utc();
        $startWorkHour = $this->date
            ->copy()
            ->shiftTimezone('Europe/Stockholm')
            ->setTimeFromTimeString($this->start_time)
            ->utc();
        $endWorkHour = $this->date
            ->copy()
            ->shiftTimezone('Europe/Stockholm')
            ->setTimeFromTimeString($this->end_time)
            ->utc();

        $schedules = ScheduleCleaning::whereHas('scheduleEmployees', function (Builder $query) {
            $query->where('user_id', $this->user_id)
                ->whereNull('work_hour_id')
                ->whereIn(
                    'status',
                    [ScheduleCleaningStatusEnum::Done(), ScheduleCleaningStatusEnum::Progress()]
                );
        })
            ->where(function (Builder $query) use ($startOfDay, $startWorkHour, $endWorkHour, $endOfDay) {
                $query->whereBetween('start_at', [$startOfDay, $startWorkHour])
                    ->orWhereBetween('end_at', [$endWorkHour, $endOfDay]);
            })
            ->get();

        if ($schedules->isEmpty()) {
            return 0;
        }

        /** @var Carbon */
        $startAt = $schedules->min('start_at');
        /** @var Carbon */
        $endAt = $schedules->max('end_at');

        $diffStart = $startAt->diffInMinutes($startWorkHour);
        $diffEnd = $endWorkHour->diffInMinutes($endAt);

        return ceil(($diffStart + $diffEnd) / 15) / 4;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ScheduleEmployee::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(CashierAttendance::class);
    }
}
