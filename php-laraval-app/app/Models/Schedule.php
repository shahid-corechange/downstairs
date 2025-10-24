<?php

namespace App\Models;

use App\Enums\Deviation\DeviationTypeEnum;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\Schedule\ScheduleStatusEnum;
use App\Enums\Schedule\ScheduleWorkStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Http\Traits\HasManySyncTrait;
use App\Services\CreditService;
use Auth;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Kolossal\Multiplex\HasMeta;

class Schedule extends Model
{
    use CascadeSoftDeletes;
    use HasFactory;
    use HasManySyncTrait;
    use HasMeta;
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
        'service_id',
        'team_id',
        'customer_id',
        'property_id',
        'subscription_id',
        'scheduleable_id',
        'scheduleable_type',
        'status',
        'start_at',
        'end_at',
        'original_start_at',
        'quarters',
        'is_fixed',
        'key_information',
        'note',
        'note->note',
        'note->property_note',
        'note->subscription_note',
        'cancelable_type',
        'cancelable_id',
        'canceled_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'actual_start_at',
        'actual_end_at',
        'actual_quarters',
        'calendar_quarters',
        'can_refund',
        'has_deviation',
        'work_status',
        'refund',
        'full_note',
        'canceled_by',
        'canceled_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'original_start_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'canceled_at' => 'datetime',
        'is_fixed' => 'boolean',
        'note' => 'array',
    ];

    /**
     * Define the columns that always be returned.
     *
     * @var string[]
     */
    protected array $includes = ['id', 'scheduleable_type', 'scheduleable_id'];

    /**
     * Define the alias of the columns or relations.
     *
     * @var array<string,string>
     */
    protected array $aliases = [
        'notes' => 'note',
        'note' => 'full_note',
        'detail' => 'scheduleable',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'calendar_quarters' => ['start_at', 'end_at'],
        'can_refund' => ['status', 'start_at'],
        'work_status' => ['start_at'],
        'refund' => ['status', 'start_at', 'quarters'],
        'full_note' => ['note'],
        'canceled_type' => ['cancelable_type'],
        'canceled_by' => ['cancelable_type', 'cancelable_id'],
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'actual_start_at' => ['scheduleEmployees' => ['start_at']],
        'actual_end_at' => ['scheduleEmployees' => ['end_at']],
        'actual_quarters' => ['scheduleEmployees' => ['start_at', 'end_at', 'status']],
        'has_deviation' => ['deviation' => ['is_handled', 'schedule_id']],
        'work_status' => [
            'scheduleEmployees' => ['start_at'],
            'deviation' => ['types', 'is_handled'],
        ],
        'refund' => [
            'subscription.fixedPrice' => ['is_per_order'],
            'subscription.fixedPrice.rows' => ['price'],
            'service' => ['price'],
        ],
        'canceled_by' => ['cancelable' => ['name', 'first_name', 'last_name']],
    ];

    /**
     * Define the models for polymorphic relations.
     *
     * @var array<string,string[]>
     */
    protected array $relationsMorphMap = [
        'cancelable' => [User::class, Customer::class, Team::class],
        'scheduleable' => [ScheduleCleaning::class, ScheduleLaundry::class],
    ];

    public function getActualStartAtAttribute(): ?Carbon
    {
        $startAt = $this->scheduleEmployees->min('start_at');

        if (is_null($startAt)) {
            return null;
        }

        return Carbon::parse($startAt);
    }

    public function getActualEndAtAttribute(): ?Carbon
    {
        $endAt = $this->scheduleEmployees->max('end_at');

        if (is_null($endAt)) {
            return null;
        }

        return Carbon::parse($endAt);
    }

    public function getActualQuartersAttribute()
    {
        $startAt = Carbon::parse($this->actual_start_at);
        $endAt = Carbon::parse($this->actual_end_at);
        $quarters = ceil($startAt->diffInMinutes($endAt) / 15);

        if ($this->isCleaning()) {
            $attendedWorkers = $this->scheduleEmployees
                ->whereIn('status', [
                    ScheduleEmployeeStatusEnum::Progress(),
                    ScheduleEmployeeStatusEnum::Done(),
                ])
                ->count();

            return $quarters * $attendedWorkers;
        }

        return $quarters;
    }

    /**
     * Get calendar quarters of the schedule
     * based on the start at and end at.
     */
    public function getCalendarQuartersAttribute(): int
    {
        return (int) ceil($this->start_at->diffInMinutes($this->end_at) / 15);
    }

    /**
     * Get refund status of the schedule cleaning.
     * If true, customer will get a credit refund.
     */
    public function getCanRefundAttribute(): bool
    {
        if (in_array($this->status, [
            ScheduleStatusEnum::Cancel(),
            ScheduleStatusEnum::Done(),
        ])) {
            return false;
        }

        $refundTime = get_setting(GlobalSettingEnum::CreditRefundTimeWindow(), 72);

        return $this->start_at->subHours($refundTime)->isPast();
    }

    public function getHasDeviationAttribute(): bool
    {
        return $this->deviation && ! $this->deviation->is_handled ? true : false;
    }

    /**
     * Get work status of the schedule cleaning.
     */
    public function getWorkStatusAttribute(): ?string
    {
        if ($this->start_at->isFuture()) {
            return null;
        }

        if ($this->actual_start_at === null && $this->start_at->isPast()) {
            return ScheduleWorkStatusEnum::NotStarted();
        }

        if ($this->has_deviation &&
            in_array(DeviationTypeEnum::StartWrongTime(), $this->deviation->types)) {
            return ScheduleWorkStatusEnum::StartedLate();
        }

        if ($this->has_deviation &&
            in_array(DeviationTypeEnum::StopWrongTime(), $this->deviation->types)) {
            return ScheduleWorkStatusEnum::EndedLate();
        }

        return ScheduleWorkStatusEnum::OK();
    }

    /**
     * Get refund information of the schedule cleaning.
     * Return null if the schedule cleaning is not refundable.
     */
    public function getRefundAttribute(): ?array
    {
        if (! $this->can_refund) {
            return null;
        }

        $creditService = new CreditService();
        $refundAmount = $creditService->calculateRefund($this);
        $creditValidUntil = get_setting(GlobalSettingEnum::CreditExpirationDays(), 365);

        return [
            'amount' => $refundAmount,
            'valid_until' => now()->startOfDay()->addDays($creditValidUntil)
                ->format(config('data.date_format')),
        ];
    }

    public function getFullNoteAttribute()
    {
        $notes = array_filter([
            $this->note['property_note'] ?? null,
            $this->note['subscription_note'] ?? null,
            $this->note['note'] ?? null,
        ]);

        return implode('; ', $notes);
    }

    public function getCanceledByAttribute(): ?string
    {
        if (is_null($this->cancelable_type)) {
            return null;
        }

        return $this->cancelable_type === User::class ? $this->cancelable->full_name : $this->cancelable->name;
    }

    public function getCanceledTypeAttribute(): ?string
    {
        if (is_null($this->cancelable_type)) {
            return null;
        } elseif ($this->cancelable_type === Team::class) {
            return 'employee';
        } elseif ($this->cancelable_type === Customer::class) {
            return 'customer';
        } else {
            return 'admin';
        }
    }

    /**
     * SQL raw expression to get canceled type.
     */
    public function canceledType(): ?string
    {
        return "
            (CASE
                WHEN cancelable_type like '%Team' THEN 'employee'
                WHEN cancelable_type like '%Customer' THEN 'customer'
                WHEN cancelable_type like '%User' THEN 'admin'
                ELSE NULL
            END)
        ";
    }

    /**
     * Get addon summaries from cleaning tasks.
     * This is used to determine whether the addon is charged or not,
     * when there are deviations in the task.
     */
    public function addonSummaries(): array
    {
        /** @var array $tasks */
        $tasks = $this->scheduleTasks->toArray();
        $addons = [];
        $addonItems = $this->items->filter(function ($item) {
            return $item->itemable_type === Addon::class;
        })->toArray();

        /** @var ScheduleItem $item */
        foreach ($addonItems as $item) {
            $taskIds = $item->addon->tasks()->pluck('id')->toArray();

            // count task that is not completed
            $counter = count(array_filter($tasks, function ($item) use ($taskIds) {
                return in_array($item['custom_task_id'], $taskIds)
                    && ! $item['is_completed'];
            }));

            /**
             * if all task is not completed,
             * then decided addon is not charged
             * if some task is not completed,
             * then decided addon is charged
             */
            if ($counter === count($taskIds)) {
                $addons[] = [
                    ...$item->toArray(),
                    'name' => $item->addon->name,
                    'is_charge' => false,
                ];
            } elseif ($counter > 0 && $counter < count($taskIds)) {
                $addons[] = [
                    ...$item->toArray(),
                    'name' => $item->addon->name,
                    'is_charge' => true,
                ];
            }
        }

        return $addons;
    }

    public function isCleaning(): bool
    {
        return $this->scheduleable_type === ScheduleCleaning::class;
    }

    public function isLaundry(): bool
    {
        return $this->scheduleable_type === ScheduleLaundry::class;
    }

    /**
     * Schedule cleaning relation to user model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Schedule cleaning relation to service model.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    /**
     * Schedule cleaning relation to team model.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class)->withTrashed();
    }

    /**
     * Schedule cleaning relation to customer model.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class)->withTrashed();
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class)->withTrashed();
    }

    public function scheduleable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Schedule cleaning relation to schedule cleaning employee model.
     */
    public function scheduleEmployees(): HasMany
    {
        return $this->hasMany(ScheduleEmployee::class);
    }

    /**
     * Get all employees of the schedule cleaning including deleted employees.
     */
    public function allEmployees(): HasMany
    {
        return $this->scheduleEmployees()->withTrashed()
            ->where(function (Builder $query) {
                $query->whereHas('user', function (Builder $query) {
                    $query->whereNull('deleted_at');
                })
                    ->orWhere('status', ScheduleEmployeeStatusEnum::Done());
            });
    }

    /**
     * Get all active employees of the schedule cleaning.
     */
    public function activeEmployees(): HasMany
    {
        return $this->scheduleEmployees()->whereNot('status', ScheduleEmployeeStatusEnum::Cancel());
    }

    /**
     * Schedule cleaning relation to schedule change request model.
     */
    public function changeRequest(): HasOne
    {
        return $this->hasOne(ScheduleChangeRequest::class, 'schedule_id')->withTrashed();
    }

    public function itemSummaries()
    {
        return [];
    }

    /**
     * Schedule cleaning relation to schedule item model.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ScheduleItem::class, 'schedule_id');
    }

    /**
     * Schedule cleaning relation to custom task model.
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(CustomTask::class, 'taskable');
    }

    /**
     * Schedule cleaning relation to order model.
     */
    public function order(): MorphOne
    {
        return $this->morphOne(Order::class, 'orderable')->withTrashed();
    }

    /**
     * Schedule cleaning relation to schedule deviation model.
     */
    public function deviation(): HasOne
    {
        return $this->hasOne(ScheduleDeviation::class, 'schedule_id');
    }

    /**
     * Schedule cleaning relation to schedule task model.
     */
    public function scheduleTasks(): HasMany
    {
        return $this->hasMany(ScheduleTask::class);
    }

    public function addons(): MorphToMany
    {
        return $this->morphedByMany(Addon::class, 'itemable', 'schedule_items')
            ->withPivot([
                'price',
                'quantity',
                'discount_percentage',
                'payment_method',
            ]);
    }

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'itemable', 'schedule_items')
            ->withPivot([
                'price',
                'quantity',
                'discount_percentage',
                'payment_method',
            ]);
    }

    /**
     * Schedule cleaning relation to cancelable entity model.
     */
    public function cancelable(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    /**
     * Scope a query to only include future schedule cleanings that is not yet started.
     */
    public function scopeFuture(Builder $query)
    {
        return $query->where('start_at', '>=', now())
            ->where('status', ScheduleStatusEnum::Booked());
    }

    /**
     * Scope a query to only include schedule cleanings that is active.
     */
    public function scopeBooked(Builder $query)
    {
        return $query->where('status', ScheduleStatusEnum::Booked());
    }

    public function scopeCanceled(Builder $query)
    {
        return $query->where('status', ScheduleStatusEnum::Cancel());
    }

    public function scopeProgress(Builder $query)
    {
        return $query->where('status', ScheduleStatusEnum::Progress());
    }

    public function scopeDone(Builder $query)
    {
        return $query->where('status', ScheduleStatusEnum::Done());
    }

    /**
     * Scope a query to get schedule cleaning of the given user.
     */
    public function scopeOfUser(Builder $query, int $userId)
    {
        return $query->whereHas('subscription', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        });
    }

    /**
     * Scope a query to get schedule cleaning of the authenticated user.
     */
    public function scopeOfAuthUser(Builder $query)
    {
        return $query->ofUser(Auth::id());
    }

    public function syncItems(array $items)
    {
        return $this->syncHasMany('items', $items, 'itemable_id', function ($schedule, $itemData) {
            return $schedule->items()->create($itemData);
        });
    }
}
