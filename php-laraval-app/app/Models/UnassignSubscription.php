<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class UnassignSubscription extends Model
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
        'customer_id',
        'service_id',
        'frequency',
        'start_at',
        'end_at',
        'is_fixed',
        'description',
        'fixed_price',
        'addon_ids',
        'product_carts',
        'cleaning_detail',
        'laundry_detail',
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
        'team_name',
        'total_price',
        'total_raw_price',
        'products',
        'addons',
        'quarters',
        'property_address',
    ];

    protected $casts = [
        'start_at' => 'date',
        'end_at' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_fixed' => 'boolean',
        'addon_ids' => 'array',
        'product_carts' => 'array',
        'cleaning_detail' => 'array',
        'laundry_detail' => 'array',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'start_time' => ['cleaning_detail', 'laundry_detail'],
        'end_time' => ['cleaning_detail', 'laundry_detail'],
        'total_price' => ['cleaning_detail', 'fixed_price', 'product_carts', 'addon_ids'],
        'total_raw_price' => ['cleaning_detail'],
        'products' => ['product_carts'],
        'addons' => ['addon_ids'],
        'quarters' => ['cleaning_detail'],
        'property_address' => ['cleaning_detail', 'laundry_detail'],
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'total_price' => [
            'service' => ['price_with_vat'],
        ],
        'total_raw_price' => [
            'service' => ['price_with_vat'],
        ],
    ];

    public function getStartTimeAttribute()
    {
        return $this->isCleaning() ? $this->cleaning_detail['start_time'] :
            $this->laundry_detail['pickup_time'];
    }

    public function getEndTimeAttribute()
    {
        if ($this->isCleaning()) {
            return $this->cleaning_detail['end_time'];
        }

        return now()
            ->setTimeFromTimeString($this->laundry_detail['pickup_time'])
            ->addMinutes(15)
            ->format('H:i:s');
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

    public function weekday(): string
    {
        $timezone = Auth::user()->info->timezone ?? 'Europe/Stockholm';
        $offset = now()->setTimezone($timezone)->offsetHours;

        // Use a CASE expression to handle different subscription types
        return "(WEEKDAY(DATE_ADD(
            CONCAT_WS(' ', start_at, 
                CASE 
                    WHEN JSON_EXTRACT(cleaning_detail, '$.start_time') IS NOT NULL 
                    THEN JSON_EXTRACT(cleaning_detail, '$.start_time')
                    ELSE JSON_EXTRACT(laundry_detail, '$.pickup_time') 
                END
            ), 
            INTERVAL $offset HOUR)) + 1)";
    }

    public function getTotalPriceAttribute(): float
    {
        if ($this->fixed_price) {
            return $this->fixed_price;
        }

        $itemPrices = 0;

        if (! empty($this->products)) {
            $itemPrices = $this->products->sum(
                fn (Product $product) => $product->price_with_vat * $product->quantity
            );
        }

        if (! empty($this->addons)) {
            $itemPrices = $this->addons->sum(
                fn (Addon $addon) => $addon->price_with_vat
            );
        }

        return $this->total_raw_price + $itemPrices;
    }

    public function getTotalRawPriceAttribute(): float
    {
        if ($this->fixed_price) {
            return $this->fixed_price;
        }

        $transport = get_transport();
        $material = get_material();

        $total = (($this->service->price_with_vat + $material->price_with_vat) * $this->quarters) +
            $transport->price_with_vat;

        return $total;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Product>
     */
    public function getProductsAttribute()
    {
        return array_reduce(
            $this->product_carts,
            function ($carry, $item) {
                $product = get_products()->find($item['id']);
                // combine product with quantity
                $carry->add([...$item, ...$product->toArray()]);
            },
            new Collection()
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Addon>
     */
    public function getAddonsAttribute()
    {
        return get_addons()->filter(
            fn (Addon $addon) => in_array($addon->id, $this->addon_ids ?? [])
        );
    }

    public function getQuartersAttribute()
    {
        return $this->isCleaning() ? $this->cleaning_detail['quarters'] :
            config('downstairs.schedule.laundry.quarters');
    }

    public function getPropertyAddressAttribute()
    {
        if ($this->isCleaning()) {
            $property = Property::find($this->cleaning_detail['property_id']);

            return $property->address->full_address;
        }

        $property = Property::find($this->laundry_detail['pickup_property_id']);

        return $property->address->full_address;
    }

    public function isCleaning()
    {
        return isset($this->cleaning_detail);
    }

    public function user(): belongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function customer(): belongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function property(): belongsTo
    {
        return $this->belongsTo(Property::class)->withTrashed();
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }
}
