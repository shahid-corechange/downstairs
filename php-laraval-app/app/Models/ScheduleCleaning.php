<?php

namespace App\Models;

use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
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

class ScheduleCleaning extends Model
{
    use CascadeSoftDeletes;
    use HasFactory;
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
        'subscription_id',
        'team_id',
        'customer_id',
        'property_id',
        'laundry_order_id', // for laundry add on
        'laundry_type', // for laundry add on
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
     * Define the alias of the columns or relations.
     *
     * @var array<string,string>
     */
    protected array $aliases = [
        'notes' => 'note',
        'note' => 'full_note',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'calendar_quarters' => ['start_at', 'end_at'],
        'can_refund' => ['status', 'start_at'],
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
        'has_deviation' => ['deviation' => ['is_handled', 'schedule_cleaning_id']],
        'refund' => [
            'subscription.fixedPrice' => ['is_per_order', 'total_price'],
            'subscription.service' => ['price'],
        ],
        'canceled_by' => ['cancelable' => ['name', 'full_name']],
    ];

    /**
     * Define the models for polymorphic relations.
     *
     * @var array<string,string[]>
     */
    protected array $relationsMorphMap = [
        'cancelable' => [User::class, Customer::class, Team::class],
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
        $attendedWorkers = $this->scheduleEmployees
            ->whereIn('status', [
                ScheduleEmployeeStatusEnum::Progress(),
                ScheduleEmployeeStatusEnum::Done(),
            ])
            ->count();

        return ceil($startAt->diffInMinutes($endAt) / 15) * $attendedWorkers;
    }

    /**
     * Get calendar quarters of the schedule cleaning
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
            ScheduleCleaningStatusEnum::Cancel(),
            ScheduleCleaningStatusEnum::Done(),
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
     * Get refund information of the schedule cleaning.
     * Return null if the schedule cleaning is not refundable.
     */
    public function getRefundAttribute(): ?array
    {
        if (! $this->can_refund) {
            return null;
        }

        $creditService = new CreditService();
        $refundAmount = 0;
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
     * Get product summaries from cleaning tasks.
     * This is used to determine whether the product is charged or not,
     * when there are deviations in the task.
     */
    public function productSummaries(): array
    {
        /** @var array $tasks */
        $tasks = $this->scheduleCleaningTasks->toArray();
        $products = [];

        /** @var ScheduleCleaningProduct $product */
        foreach ($this->products()->get() as $product) {
            $taskIds = $product->product->tasks()->pluck('id')->toArray();

            // count task that is not completed
            $counter = count(array_filter($tasks, function ($item) use ($taskIds) {
                return in_array($item['custom_task_id'], $taskIds)
                    && ! $item['is_completed'];
            }));

            /**
             * if all task is not completed,
             * then decided product is not charged
             * if some task is not completed,
             * then decided product is charged
             */
            if ($counter === count($taskIds)) {
                $products[] = [
                    ...$product->toArray(),
                    'name' => $product->product->name,
                    'is_charge' => false,
                ];
            } elseif ($counter > 0 && $counter < count($taskIds)) {
                $products[] = [
                    ...$product->toArray(),
                    'name' => $product->product->name,
                    'is_charge' => true,
                ];
            }
        }

        return $products;
    }

    /**
     * Schedule cleaning relation to subscription model.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class)->withTrashed();
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

    /**
     * Schedule cleaning relation to property model.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class)->withTrashed();
    }

    /**
     * Schedule cleaning relation to schedule cleaning employee model.
     */
    public function scheduleEmployees(): MorphMany
    {
        return $this->morphMany(ScheduleEmployee::class, 'scheduleable');
    }

    /**
     * Get all employees of the schedule cleaning including deleted employees.
     */
    public function allEmployees(): MorphMany
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
    public function activeEmployees(): MorphMany
    {
        return $this->scheduleEmployees()->whereNot('status', ScheduleEmployeeStatusEnum::Cancel());
    }

    /**
     * Schedule cleaning relation to schedule cleaning change request model.
     */
    public function changeRequest(): HasOne
    {
        return $this->hasOne(ScheduleCleaningChangeRequest::class, 'schedule_cleaning_id')->withTrashed();
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

    public function laundryOrder(): BelongsTo
    {
        return $this->belongsTo(LaundryOrder::class)->withTrashed();
    }

    /**
     * Schedule cleaning relation to schedule cleaning deviation model.
     */
    public function deviation(): HasOne
    {
        return $this->hasOne(ScheduleCleaningDeviation::class, 'schedule_cleaning_id');
    }

    /**
     * Schedule cleaning relation to schedule cleaning task model.
     */
    public function scheduleCleaningTasks(): HasMany
    {
        return $this->hasMany(ScheduleCleaningTask::class);
    }

    public function addons(): MorphToMany
    {
        return $this->morphedByMany(Addon::class, 'itemable', 'schedule_cleaning_items')
            ->withPivot([
                'price',
                'quantity',
                'discount_percentage',
                'payment_method',
            ]);
    }

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'itemable', 'schedule_cleaning_items')
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

    public function schedule(): MorphOne
    {
        return $this->morphOne(Schedule::class, 'scheduleable');
    }

    /**
     * Scope a query to only include future schedule cleanings that is not yet started.
     */
    public function scopeFuture(Builder $query)
    {
        return $query->where('start_at', '>=', now())
            ->whereIn('status', [
                ScheduleCleaningStatusEnum::Draft(),
                ScheduleCleaningStatusEnum::Booked(),
                ScheduleCleaningStatusEnum::Pending(),
            ]);
    }

    /**
     * Scope a query to only include schedule cleanings that is active.
     */
    public function scopeActive(Builder $query)
    {
        return $query->whereIn('status', [
            ScheduleCleaningStatusEnum::Booked(),
            ScheduleCleaningStatusEnum::Progress(),
        ]);
    }

    public function scopeCanceled(Builder $query)
    {
        return $query->where('status', ScheduleCleaningStatusEnum::Cancel());
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
}
