<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
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
        'city_id',
        'address',
        'address_2',
        'postal_code',
        'area',
        'accuracy',
        'latitude',
        'longitude',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'full_address',
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
        'accuracy' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Define the alias of the columns or relations.
     *
     * @var array<string,string>
     */
    protected array $aliases = [
        'address2' => 'address_2',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'full_address' => ['address', 'address_2', 'postal_code'],
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'full_address' => [
            'city' => ['city_id', 'name'],
            'city.country' => ['country_id', 'name'],
        ],
    ];

    public function getFullAddressAttribute(): string
    {
        $address = $this->address_2 ? "{$this->address}, {$this->address_2}" : $this->address;

        $cityName = $this->city?->name ?? '';
        $countryName = $this->city?->country?->name ?? '';

        return "$address, $cityName, {$this->postal_code}, $countryName";
    }

    /**
     * SQL raw expressions for fullAddress.
     */
    public function fullAddress(): string|array
    {
        return [
            'column' => "CONCAT_WS(
                    ', ',
                    IF(address_2 IS NOT NULL, CONCAT(address, ', ', address_2), address),
                    cities.name,
                    postal_code,
                    countries.name
                )",
            'joins' => [
                ['cities', 'addresses.city_id', '=', 'cities.id'],
                ['countries', 'cities.country_id', '=', 'countries.id'],
            ],
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function property(): HasOne
    {
        return $this->hasOne(Property::class)->withTrashed();
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class)->withTrashed();
    }

    public function store(): HasOne
    {
        return $this->hasOne(Store::class)->withTrashed();
    }
}
