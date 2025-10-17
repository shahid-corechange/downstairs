<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kolossal\Multiplex\HasMeta;

class LaundryPreference extends Model
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
        'price',
        'percentage',
        'vat_group',
        'hours',
        'include_holidays',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'name',
        'description',
        'price_with_vat',
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
        'price' => 'float',
        'percentage' => 'float',
        'include_holidays' => 'boolean',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'price_with_vat' => ['price', 'vat_group'],
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'name' => ['translations' => ['id']],
        'description' => ['translations' => ['id']],
    ];

    public function getNameAttribute(): ?string
    {
        return $this->getTranslation('name');
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->getTranslation('description');
    }

    public function getPriceWithVatAttribute(): float
    {
        return price_with_vat($this->price ?? 0, $this->vat_group);
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(CustomTask::class, 'taskable');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }
}
