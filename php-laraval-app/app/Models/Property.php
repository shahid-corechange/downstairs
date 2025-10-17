<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kolossal\Multiplex\HasMeta;

class Property extends Model
{
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
        'address_id',
        'property_type_id',
        'membership_type',
        'square_meter',
        'key_information',
        'status',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'key_description',
        'key_place',
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
        'square_meter' => 'float',
        'key_information' => 'array',
    ];

    /**
     * Define the alias of the columns or relations.
     *
     * @var array<string,string>
     */
    protected array $aliases = [
        'type_id' => 'property_type_id',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'key_description' => ['key_information'],
        'key_place' => ['key_information'],
    ];

    public function getKeyDescriptionAttribute(): string
    {
        $result = '';
        $keys = ['front_door_code', 'alarm_code_off', 'alarm_code_on', 'information'];

        foreach ($keys as $key) {
            if (isset($this->key_information[$key]) && $this->key_information[$key] != null) {
                if ($key !== 'information') {
                    $result .= __("key_information.{$key}")." {$this->key_information[$key]}, ";
                } else {
                    $result .= "{$this->key_information[$key]}, ";
                }
            }
        }

        return rtrim($result, ', ');
    }

    public function getKeyPlaceAttribute(): ?string
    {
        return isset($this->key_information['key_place']) ? $this->key_information['key_place'] : null;
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class)->withTrashed();
    }

    public function companyUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'company');
            })
            ->withTrashed();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTrashed();
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class, 'property_type_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
