<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class SubscriptionCleaningDetail extends Model
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
        'property_id',
        'team_id',
        'quarters',
        'start_time',
        'end_time',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'team_name',
        'address',
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
        'team_name' => ['team_id'],
        'address' => ['property_id'],
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'team_name' => ['team' => ['name']],
        'address' => [
            'property' => ['address_id'],
            'property.address' => ['city_id', 'address', 'address_2', 'postal_code'],
            'property.address.city' => ['country_id', 'name'],
            'property.address.city.country' => ['name'],
        ],
    ];

    public function getTeamNameAttribute(): string
    {
        return $this->team->name;
    }

    public function getAddressAttribute(): string
    {
        return $this->property->address->full_address;
    }

    public function property(): belongsTo
    {
        return $this->belongsTo(Property::class)->withTrashed();
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class)->withTrashed();
    }

    public function subscription(): MorphOne
    {
        return $this->morphOne(Subscription::class, 'subscribable');
    }
}
