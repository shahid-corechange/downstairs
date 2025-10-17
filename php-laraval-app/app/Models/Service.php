<?php

namespace App\Models;

use App\Enums\MembershipTypeEnum;
use App\Http\Traits\TranslationsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory;
    use SoftDeletes;
    use TranslationsTrait;

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
        'fortnox_article_id',
        'type',
        'membership_type',
        'price',
        'vat_group',
        'has_rut',
        'thumbnail_image',
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
        'price' => 'float',
        'has_rut' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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
        return price_with_vat($this->price, $this->vat_group);
    }

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, 'service_addons');
    }

    public function products(): MorphToMany
    {
        return $this->morphToMany(Product::class, 'productable');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(CustomTask::class, 'taskable');
    }

    public function quarters(): HasMany
    {
        return $this->hasMany(ServiceQuarter::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categoryable');
    }

    public function scopePrivate(Builder $query)
    {
        return $query->where('membership_type', MembershipTypeEnum::Private());
    }

    public function scopeCompany(Builder $query)
    {
        return $query->where('membership_type', MembershipTypeEnum::Company());
    }
}
