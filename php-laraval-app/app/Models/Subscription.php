<?php

namespace App\Models;

use App\Enums\Schedule\ScheduleStatusEnum;
use Auth;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kolossal\Multiplex\HasMeta;

class Subscription extends Model
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
        'customer_id',
        'service_id',
        'user_id',
        'fixed_price_id',
        'frequency',
        'start_at',
        'end_at',
        'is_paused',
        'is_fixed',
        'description',
        'subscribable_type',
        'subscribable_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'start_time',
        'end_time',
        'weekday',
        'total_price',
        'total_raw_price',
        'is_cleaning_has_laundry',
    ];

    protected $casts = [
        'start_at' => 'date',
        'end_at' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_paused' => 'boolean',
        'is_fixed' => 'boolean',
    ];

    /**
     * Define the columns that always be returned.
     *
     * @var string[]
     */
    protected array $includes = ['id', 'subscribable_type', 'subscribable_id'];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'weekday' => ['start_at', 'subscribable_type', 'subscribable_id'],
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'start_time' => ['subscribable' => ['start_time', 'pickup_time']],
        'end_time' => ['subscribable' => ['end_time', 'pickup_time']],
        'weekday' => ['subscribable' => ['start_time', 'pickup_time']],
        'total_price' => [
            'fixedPrice.rows' => ['price', 'vat_group'],
            'service' => ['price', 'vat_group'],
            'products' => ['price', 'vat_group'],
            'addons' => ['price', 'vat_group'],
            'subscribable' => ['quarters'],
        ],
        'total_raw_price' => [
            'fixedPrice.rows' => ['price', 'vat_group'],
            'service' => ['price', 'vat_group'],
            'subscribable' => ['quarters'],
        ],
        'is_cleaning_has_laundry' => ['schedules.scheduleable' => ['laundry_order_id']],
    ];

    /**
     * Define the alias of the columns or relations.
     *
     * @var array<string,string>
     */
    protected array $aliases = [
        'detail' => 'subscribable',
    ];

    /**
     * Define the models for polymorphic relations.
     *
     * @var array<string,string[]>
     */
    protected array $relationsMorphMap = [
        'subscribable' => [SubscriptionCleaningDetail::class, SubscriptionLaundryDetail::class],
    ];

    public function getStartTimeAttribute(): string
    {
        return $this->subscribable->start_time;
    }

    public function getEndTimeAttribute(): string
    {
        return $this->subscribable->end_time;
    }

    public function getWeekdayAttribute(): int
    {
        $timezone = Auth::user()->info->timezone ?? 'Europe/Stockholm';
        $weekday = $this->start_at
            ->setTimeFromTimeString($this->start_time)
            ->setTimezone($timezone)
            ->format('N');

        return (int) $weekday;
    }

    // public function weekday(): string
    // {
    //     $timezone = Auth::user()->info->timezone ?? 'Europe/Stockholm';
    //     $offset = now()->setTimezone($timezone)->offsetHours;

    //     return "(WEEKDAY(DATE_ADD(CONCAT_WS(' ', start_at, start_time_at), INTERVAL $offset HOUR)) + 1)";
    // }

    public function getTotalPriceAttribute(): float
    {
        if ($this->fixedPrice) {
            return $this->fixedPrice->total_price_with_vat;
        }

        $productPrices = $this->products->sum(
            fn ($product) => $product->price_with_vat
        );

        $addonPrices = $this->addons->sum(
            fn ($addon) => $addon->price_with_vat
        );

        return $this->total_raw_price + $productPrices + $addonPrices;
    }

    public function getTotalRawPriceAttribute(): float
    {
        if ($this->fixedPrice) {
            return $this->fixedPrice->total_price_with_vat;
        }

        $transport = get_transport();
        $material = get_material();
        $service = $this->service;

        $total = (($service->price_with_vat + $material->price_with_vat) * $this->subscribable->quarters) +
            $transport->price_with_vat;

        return $total;
    }

    public function getIsCleaningHasLaundryAttribute(): bool
    {
        if ($this->isCleaning()) {
            $isIncludeLaundry = $this->schedules->contains(function ($schedule) {
                return $schedule->scheduleable && $schedule->scheduleable->laundry_order_id;
            });

            return $isIncludeLaundry;
        }

        return false;
    }

    public function isCleaning()
    {
        return $this->subscribable_type === SubscriptionCleaningDetail::class;
    }

    public function isLaundry()
    {
        return $this->subscribable_type === SubscriptionLaundryDetail::class;
    }

    public function details(): HasOne
    {
        return $this->hasOne(SubscriptionDetails::class)->withTrashed();
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class)->withTrashed();
    }

    public function staffs(): HasMany
    {
        return $this->HasMany(SubscriptionStaffDetails::class);
    }

    public function schedules(): HasMany
    {
        return $this->HasMany(Schedule::class);
    }

    public function laundryOrders(): HasMany
    {
        return $this->HasMany(LaundryOrder::class);
    }

    public function futureLaundryOrders(): HasMany
    {
        return $this->laundryOrders()
            ->whereHas('schedules', function ($query) {
                $query->future();
            });
    }

    /**
     * Users relation to tags model.
     */
    public function user(): belongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function customer(): belongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function property(): belongsTo
    {
        return $this->belongsTo(Property::class)->withTrashed();
    }

    public function subscriptionProducts(): HasMany
    {
        return $this->hasMany(SubscriptionProduct::class, 'subscription_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SubscriptionItem::class, 'subscription_id', 'id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(CustomTask::class, 'taskable');
    }

    public function fixedPrice(): BelongsTo
    {
        return $this->belongsTo(FixedPrice::class)->withTrashed();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function draftOrders(): HasMany
    {
        return $this->orders()->draft();
    }

    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }

    public function addons(): MorphToMany
    {
        return $this->morphedByMany(Addon::class, 'itemable', 'subscription_items')
            ->withPivot([
                'quantity',
            ]);
    }

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'itemable', 'subscription_items')
            ->withPivot([
                'quantity',
            ]);
    }

    /**
     * Get the schedules that not have time base on the subscription.
     */
    public function updatedSchedules(): HasMany
    {
        return $this->schedules()
            ->whereIn('status', [
                ScheduleStatusEnum::Booked(),
                ScheduleStatusEnum::Progress(),
            ])
            ->whereMeta('manually_updated', true);
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_paused', false)
            ->where(function (Builder $query) {
                $query->where('end_at', '>=', now())
                    ->orWhereNull('end_at');
            });
    }

    public function scopeInactive(Builder $query)
    {
        return $query->whereNotNull('end_at')
            ->where('end_at', '<', now());
    }

    public function scopeCleaning(Builder $query)
    {
        return $query->where('subscribable_type', SubscriptionCleaningDetail::class);
    }

    public function scopeLaundry(Builder $query)
    {
        return $query->where('subscribable_type', SubscriptionLaundryDetail::class);
    }
}
