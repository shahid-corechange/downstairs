<?php

namespace App\Models;

use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleCleaningChangeRequest extends Model
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
        'schedule_cleaning_id',
        'causer_id',
        'original_start_at',
        'start_at_changed',
        'original_end_at',
        'end_at_changed',
        'status',
    ];

    /**
     * Define the alias of the columns or relations.
     *
     * @var array<string,string>
     */
    protected array $aliases = [
        'schedule_id' => 'schedule_cleaning_id',
    ];

    protected $casts = [
        'original_start_at' => 'datetime',
        'start_at_changed' => 'datetime',
        'original_end_at' => 'datetime',
        'end_at_changed' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'can_reschedule',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'can_reschedule' => ['schedule_cleaning_id', 'start_at_changed', 'end_at_changed'],
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'can_reschedule' => ['schedule' => ['team_id', 'start_at', 'end_at']],
    ];

    public function getCanRescheduleAttribute(): bool
    {
        $refillSequence = get_setting(
            GlobalSettingEnum::SubscriptionRefillSequence(),
            config('downstairs.subscription.refillSequence')
        );
        $days = weeks_to_days($refillSequence);
        $limit = now()->addDays($days)->endOfDay();

        // Can't reschedule schedule a certain time ahead
        // Can't reschedule schedule to a certain time ahead
        if ($this->schedule->start_at->isAfter($limit) ||
            $this->start_at_changed->isAfter($limit)
        ) {
            return false;
        }

        $teamSchedules = ScheduleCleaning::where('team_id', $this->schedule->team_id)
            ->where('id', '!=', $this->schedule_cleaning_id)
            ->whereIn('status', [
                ScheduleCleaningStatusEnum::Booked(),
                ScheduleCleaningStatusEnum::Pending(),
                ScheduleCleaningStatusEnum::Progress(),
            ])
            ->whereNot(function (Builder $query) {
                $query->where('start_at', '>=', $this->end_at_changed ?? $this->schedule->end_at)
                    ->orWhere('end_at', '<=', $this->start_at_changed ?? $this->schedule->start_at);
            })
            ->get();

        return $teamSchedules->count() === 0;
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ScheduleCleaning::class, 'schedule_cleaning_id')->withTrashed();
    }

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id')->withTrashed();
    }

    public function isTimeMatch(Carbon|string $startAt, Carbon|string $endAt): bool
    {
        return $this->start_at_changed->equalTo(Carbon::parse($startAt)) &&
            $this->end_at_changed->equalTo(Carbon::parse($endAt));
    }
}
