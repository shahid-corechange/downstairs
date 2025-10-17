<?php

namespace App\Models;

use App\Enums\ScheduleLaundry\ScheduleLaundryTypeEnum;
use App\Http\Traits\HasManySyncTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaundryOrder extends Model
{
    use HasFactory;
    use HasManySyncTrait;
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
        'store_id',
        'laundry_preference_id',
        'subscription_id',
        'user_id',
        'causer_id',
        'customer_id',
        'pickup_property_id',
        'pickup_team_id',
        'pickup_time',
        'delivery_property_id',
        'delivery_team_id',
        'delivery_time',
        'status',
        'payment_method',
        'ordered_at',
        'paid_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'due_at',
        'total_rut',
        'total_price_with_vat',
        'total_price_with_discount',
        'total_discount',
        'total_vat',
        'total_to_pay',
        'round_amount',
        'preference_amount',
        'pickup_in_cleaning_id',
        'delivery_in_cleaning_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'pickup_time' => 'datetime',
        'delivery_time' => 'datetime',
        'ordered_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'due_at' => ['ordered_at', 'delivery_time'],
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'due_at' => ['preference' => ['hours', 'include_holidays']],
        'total_rut' => ['products' => ['has_rut', 'price', 'vat_group', 'quantity']],
        'total_price_with_vat' => ['products' => ['price', 'vat_group', 'quantity']],
        'total_price_with_discount' => ['products' => ['price', 'vat_group', 'discount', 'quantity']],
        'total_discount' => ['products' => ['price', 'vat_group', 'discount', 'quantity']],
        'total_vat' => ['products' => ['price', 'vat_group', 'quantity']],
        'total_to_pay' => ['products' => ['price', 'vat_group', 'discount', 'quantity']],
        'round_amount' => [
            'products' => ['has_rut', 'price', 'vat_group', 'discount', 'quantity'],
            'preference' => ['percentage', 'price', 'vat_group'],
        ],
        'preference_amount' => [
            'products' => ['has_rut', 'price', 'vat_group', 'discount', 'quantity'],
            'preference' => ['percentage', 'price', 'vat_group'],
        ],
        'pickup_in_cleaning_id' => [
            'scheduleCleanings' => [
                'id',
                'scheduleable_id',
                'scheduleable_type',
            ],
            'scheduleCleanings.scheduleable' => ['laundry_type'],
        ],
        'delivery_in_cleaning_id' => [
            'scheduleCleanings' => [
                'id',
                'scheduleable_id',
                'scheduleable_type',
            ],
            'scheduleCleanings.scheduleable' => ['laundry_type'],
        ],
    ];

    public function getDueAtAttribute(): Carbon
    {
        $dueAt = $this->ordered_at->copy();
        $blockDays = BlockDay::where('block_date', '>=', $dueAt->copy()->shiftTimezone('Europe/Stockholm')
            ->format('Y-m-d'))
            ->get();
        $hours = $this->preference?->hours ?? 0;

        while ($hours > 0) {
            // Skip weekends or block days
            if ($dueAt->copy()->shiftTimezone('Europe/Stockholm')->isSaturday() ||
                $dueAt->copy()->shiftTimezone('Europe/Stockholm')->isSunday() ||
                $blockDays->first(function ($blockDay) use ($dueAt) {
                    return $blockDay->block_date === $dueAt->copy()->shiftTimezone('Europe/Stockholm')->format('Y-m-d');
                })
            ) {
                $dueAt->addDay();

                continue;
            }

            // If the hours are more than 24, add a day and subtract 24
            // If the hours are less than 24, add the remaining hours
            if ($hours > 24) {
                $dueAt->addDay();
                $hours -= 24;
            } else {
                $dueAt->addHours($hours);
                $hours = 0;
            }
        }

        return $this->delivery_time ? $dueAt->setTimeFromTimeString($this->delivery_time) : $dueAt;
    }

    public function getTotalRutAttribute(): float
    {
        return $this->products->sum('total_rut');
    }

    public function getTotalPriceWithVatAttribute(): float
    {
        return $this->products->sum('total_price_with_vat');
    }

    public function getTotalPriceWithDiscountAttribute(): float
    {
        return $this->products->sum('total_price_with_discount');
    }

    public function getTotalToPayAttribute(): float
    {
        return $this->total_price_with_vat - $this->total_rut - $this->total_discount;
    }

    public function getRoundAmountAttribute(): float
    {
        $total = $this->total_to_pay + $this->preference_amount;

        return round(round($total) - $total, 2);
    }

    public function getTotalDiscountAttribute(): float
    {
        return $this->products->sum('total_discount_amount');
    }

    /**
     * Get grouped vat amount.
     *
     * @return \Illuminate\Support\Collection<string|int, mixed>
     */
    public function getTotalVatAttribute()
    {
        // group by vat_group and sum the vat_amount
        return $this->products
            ->groupBy('vat_group')
            ->map(function ($group) {
                return $group->sum('total_vat_amount');
            });
    }

    public function getPreferenceAmountAttribute(): float
    {
        if ($this->preference) {
            return ($this->total_price_with_discount - $this->total_rut) *
                (($this->preference->percentage ?? 0) / 100) +
                ($this->preference->price_with_vat);
        }

        return 0;
    }

    public function getPickupInCleaningIdAttribute(): ?int
    {
        return $this->pickupInCleanings()->first()?->id;
    }

    public function getDeliveryInCleaningIdAttribute(): ?int
    {
        return $this->deliveryInCleanings()->first()?->id;
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id')->withTrashed();
    }

    public function preference(): BelongsTo
    {
        return $this->belongsTo(LaundryPreference::class, 'laundry_preference_id')->withTrashed();
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class)->withTrashed();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function pickupProperty(): BelongsTo
    {
        return $this->belongsTo(Property::class)->withTrashed();
    }

    public function pickupTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class)->withTrashed();
    }

    public function deliveryProperty(): BelongsTo
    {
        return $this->belongsTo(Property::class)->withTrashed();
    }

    public function deliveryTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class)->withTrashed();
    }

    public function products(): HasMany
    {
        return $this->hasMany(LaundryOrderProduct::class);
    }

    public function schedules(): HasManyThrough
    {
        return $this->hasManyThrough(
            Schedule::class,
            ScheduleLaundry::class,
            'laundry_order_id',
            'scheduleable_id',
            'id',
            'id'
        )->where('scheduleable_type', ScheduleLaundry::class);
    }

    public function pickupSchedules(): HasManyThrough
    {
        return $this->schedules()->whereHas('scheduleable', function ($query) {
            $query->where('type', ScheduleLaundryTypeEnum::Pickup());
        });
    }

    public function deliverySchedules(): HasManyThrough
    {
        return $this->schedules()->whereHas('scheduleable', function ($query) {
            $query->where('type', ScheduleLaundryTypeEnum::Delivery());
        });
    }

    /**
     * Get schedules when the laundry order is a schedule cleaning.
     */
    public function scheduleCleanings(): HasManyThrough
    {
        return $this->hasManyThrough(
            Schedule::class,
            ScheduleCleaning::class,
            'laundry_order_id',
            'scheduleable_id',
            'id',
            'id'
        )->where('scheduleable_type', ScheduleCleaning::class);
    }

    /**
     * Get pickup schedules in cleaning.
     */
    public function pickupInCleanings(): HasManyThrough
    {
        return $this->scheduleCleanings()->whereHas('scheduleable', function ($query) {
            $query->where('laundry_type', ScheduleLaundryTypeEnum::Pickup());
        });
    }

    /**
     * Get delivery schedules in cleaning.
     */
    public function deliveryInCleanings(): HasManyThrough
    {
        return $this->scheduleCleanings()->whereHas('scheduleable', function ($query) {
            $query->where('laundry_type', ScheduleLaundryTypeEnum::Delivery());
        });
    }

    public function histories(): HasMany
    {
        return $this->hasMany(LaundryOrderHistory::class);
    }

    public function order(): MorphOne
    {
        return $this->morphOne(Order::class, 'orderable')->withTrashed();
    }

    public function syncProducts(array $products)
    {
        return $this->syncHasMany(
            'products',
            $products,
            'product_id',
            null,
            function ($existingProduct, $productData) {
                // Remove is_modified field as it doesn't exist in the database
                unset($productData['is_modified']);

                // update product other than product sales misc
                if ($productData['product_id'] !== config('downstairs.products.productSalesMisc.id')) {
                    $this->products()
                        ->where('product_id', $productData['product_id'])
                        ->update($productData);
                }

                // Handling update or create product sales misc
                // Unique identifier is name and product id because all product sales misc have the same product id
                $product = $this->products()
                    ->where('product_id', $productData['product_id'])
                    ->where('name', $productData['name'])
                    ->first();

                if ($product) {
                    $product->update($productData);
                } else {
                    $this->products()->create($productData);
                }
            },
            function ($itemIds, $products) {
                // Get the product sales misc ID
                $productSalesMiscId = config('downstairs.products.productSalesMisc.id');

                // Get all product names from the provided products array
                $providedProductNames = collect($products)->pluck('name')->toArray();

                // Delete product sales misc items that are not in the provided products array
                $this->products()
                    ->where('product_id', $productSalesMiscId)
                    ->whereNotIn('name', $providedProductNames)
                    ->delete();

                // Delete other products not in the provided array
                $this->products()
                    ->whereNotIn('product_id', $itemIds)
                    ->delete();
            }
        );
    }
}
