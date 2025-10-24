<?php

namespace App\Models;

use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleEmployee extends Model
{
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
        'user_id',
        'schedule_id',
        'work_hour_id',
        'start_latitude',
        'start_longitude',
        'start_ip',
        'start_at',
        'end_latitude',
        'end_longitude',
        'end_ip',
        'end_at',
        'description',
        'status',
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
        'start_latitude' => 'float',
        'start_longitude' => 'float',
        'end_latitude' => 'float',
        'end_longitude' => 'float',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'total_work_time',
        'time_adjustment_hours',
        'work_quarters',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'total_work_time' => ['start_at', 'end_at'],
        'work_quarters' => ['start_at', 'end_at'],
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'time_adjustment_hours' => [
            'timeAdjustment' => ['quarters'],
        ],
    ];

    /**
     * Total work time (in seconds) based on schedule quarters and employees
     */
    public function getTotalWorkTimeAttribute(): int
    {
        if (! $this->start_at || ! $this->end_at) {
            return 0;
        }

        return $this->start_at->diffInSeconds($this->end_at);
    }

    /**
     * Time adjustment hours.
     */
    public function getTimeAdjustmentHoursAttribute(): float
    {
        $quarters = $this->timeAdjustment ? $this->timeAdjustment->quarters : 0;

        return $quarters / 4;
    }

    /**
     * Total quarters that workers have worked on
     * Convert total work time in seconds to quarters (15 minutes)
     */
    public function getWorkQuartersAttribute(): int
    {
        return ceil($this->total_work_time / (60 * 15));
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id')->withTrashed();
    }

    /**
     * Users relation to tags model.
     */
    public function user(): belongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    /**
     * Schedule employee relation to time adjustment model.
     */
    public function timeAdjustment(): HasOne
    {
        return $this->hasOne(TimeAdjustment::class);
    }

    /**
     * Schedule employee relation to work hour model.
     */
    public function workHour(): belongsTo
    {
        return $this->belongsTo(WorkHour::class);
    }

    /**
     * Scope a query to get active cleaning.
     */
    public function scopeActive(Builder $query)
    {
        return $query->whereIn('status', [
            ScheduleEmployeeStatusEnum::Pending(),
            ScheduleEmployeeStatusEnum::Progress(),
        ]);
    }

    /**
     * Scope a query to get schedule employee of the given user.
     */
    public function scopeOfUser(Builder $query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to get schedule employee of the authenticated user.
     */
    public function scopeOfAuthUser(Builder $query)
    {
        return $query->ofUser(Auth::id());
    }
}
