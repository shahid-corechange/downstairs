<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class SubscriptionLaundryDetail extends Model
{
    use CascadeSoftDeletes;
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
        'store_id',
        'laundry_preference_id',
        'pickup_property_id',
        'pickup_team_id',
        'pickup_time',
        'delivery_property_id',
        'delivery_team_id',
        'delivery_time',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'start_time',
        'end_time',
        'team_name',
        'address',
        'quarters',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'start_time' => ['pickup_time'],
        'end_time' => ['pickup_time'],
        'team_name' => ['pickup_team_id', 'delivery_team_id'],
        'address' => ['pickup_property_id', 'delivery_property_id'],
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'team_name' => [
            'pickupTeam' => ['name'],
            'deliveryTeam' => ['name'],
        ],
        'address' => [
            'pickupProperty' => ['address_id'],
            'pickupProperty.address' => ['city_id', 'address', 'address_2', 'postal_code'],
            'pickupProperty.address.city' => ['country_id', 'name'],
            'pickupProperty.address.city.country' => ['name'],
            'deliveryProperty' => ['address_id'],
            'deliveryProperty.address' => ['city_id', 'address', 'address_2', 'postal_code'],
            'deliveryProperty.address.city' => ['country_id', 'name'],
            'deliveryProperty.address.city.country' => ['name'],
        ],
    ];

    public function getStartTimeAttribute(): string
    {
        // start time based on pick up time
        return $this->pickup_time;
    }

    public function getEndTimeAttribute(): string
    {
        // end time 1 quarter or 15 minutes after start or pick up time
        return now()
            ->setTimeFromTimeString($this->pickup_time)
            ->addMinutes(config('downstairs.schedule.laundry.quarters') * 15)
            ->format('H:i:s');
    }

    public function getTeamNameAttribute(): ?string
    {
        if ($this->pickup_team_id && $this->delivery_team_id) {
            return __('pickup').": {$this->pickupTeam->name}, ".
                __('delivery').": {$this->deliveryTeam->name}";
        }

        if ($this->pickup_team_id) {
            return $this->pickupTeam->name;
        }

        if ($this->delivery_team_id) {
            return $this->deliveryTeam->name;
        }

        return null;
    }

    public function getAddressAttribute(): ?string
    {
        if ($this->pickup_property_id && $this->delivery_property_id) {
            return __('pickup').": {$this->pickupProperty->address->full_address}, ".
                __('delivery').": {$this->deliveryProperty->address->full_address}";
        }

        if ($this->pickup_property_id) {
            return $this->pickupProperty->address->full_address;
        }

        if ($this->delivery_property_id) {
            return $this->deliveryProperty->address->full_address;
        }

        return null;
    }

    public function getQuartersAttribute(): int
    {
        return config('downstairs.schedule.laundry.quarters');
    }

    public function store(): belongsTo
    {
        return $this->belongsTo(Store::class)->withTrashed();
    }

    public function preference(): belongsTo
    {
        return $this->belongsTo(LaundryPreference::class)->withTrashed();
    }

    public function pickupProperty(): belongsTo
    {
        return $this->belongsTo(Property::class)->withTrashed();
    }

    public function pickupTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class)->withTrashed();
    }

    public function deliveryProperty(): belongsTo
    {
        return $this->belongsTo(Property::class)->withTrashed();
    }

    public function deliveryTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class)->withTrashed();
    }

    public function subscription(): MorphOne
    {
        return $this->morphOne(Subscription::class, 'subscribable');
    }
}
