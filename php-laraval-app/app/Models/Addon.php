<?php

namespace App\Models;

use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Http\Traits\TranslationsTrait;
use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Addon extends Model
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
        'unit',
        'price',
        'credit_price',
        'vat_group',
        'has_rut',
        'thumbnail_image',
        'color',
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
        'app_price',
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
        'has_rut' => 'boolean',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'price_with_vat' => ['price', 'vat_group'],
        'app_price' => ['price', 'vat_group', 'has_rut'],
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

    /**
     * Don't use this accessor outside response DTO or directly from the model
     * TODO: Need to refactor this when introduce laundry in app and company
     */
    public function getAppPriceAttribute(): float
    {
        $user = Auth::user();
        // Need to check if user is null to prevent error using toArray()
        $discount = $user ? $user->customerDiscounts->filter(function (CustomerDiscount $discount) {
            return $discount->type === CustomerDiscountTypeEnum::Cleaning();
        })->first() : null;
        $discountValue = $discount ? $discount->value / 100 : 0;
        $rut = $this->has_rut ? 0.5 : 1;
        $basePrice = $this->price_with_vat * $rut;

        // if ($this->service && $this->service->membership_type === MembershipTypeEnum::Private()) {
        //     $rut = $this->has_rut ? 0.5 : 1;
        //     $basePrice = $this->price_with_vat * $rut;
        // } elseif ($this->service && $this->service->membership_type === MembershipTypeEnum::Company()) {
        //     $basePrice = $this->price;
        // } else {
        //     $basePrice = $this->price_with_vat;
        // }

        return round($basePrice * (1 - $discountValue), 2);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_addons');
    }

    public function products(): MorphToMany
    {
        return $this->morphToMany(Product::class, 'productable');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(CustomTask::class, 'taskable');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categoryable');
    }

    public function subscriptions(): MorphToMany
    {
        return $this->morphToMany(Subscription::class, 'itemable', 'subscription_items');
    }

    public function schedules(): MorphToMany
    {
        return $this->morphToMany(Schedule::class, 'itemable', 'schedule_items');
    }
}
