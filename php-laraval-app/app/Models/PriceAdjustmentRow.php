<?php

namespace App\Models;

use App\Enums\PriceAdjustment\PriceAdjustmentRowStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PriceAdjustmentRow extends Model
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
        'price_adjustment_id',
        'adjustable_type',
        'adjustable_id',
        'previous_price',
        'price',
        'vat_group',
        'status',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'adjustable_name',
        'price_with_vat',
    ];

    /**
     * Define the columns that always be returned.
     *
     * @var string[]
     */
    protected array $includes = ['id', 'adjustable_type'];

    protected $casts = [
        'previous_price' => 'float',
        'price' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'previous_price_with_vat' => ['previous_price', 'vat_group'],
        'price_with_vat' => ['price', 'vat_group'],
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'adjustable_name' => [
            'adjustable' => ['name', 'is_per_order'],
            'adjustable.user' => ['first_name', 'last_name'],
        ],
    ];

    /**
     * Define the models for polymorphic relations.
     *
     * @var array<string,string[]>
     */
    protected array $relationsMorphMap = [
        'adjustable' => [Service::class, Addon::class, Product::class, FixedPrice::class],
    ];

    public function getAdjustableNameAttribute(): string
    {
        if ($this->adjustable instanceof FixedPrice) {
            $frequency = $this->adjustable->is_per_order ? __('per booking') : __('monthly');

            return $this->adjustable->user->fullname.' - '.$frequency;
        }

        return $this->adjustable->name;
    }

    public function getPriceWithVatAttribute(): float
    {
        if ($this->adjustable_type === FixedPrice::class) {
            return $this->price;
        }

        return price_with_vat($this->price, $this->vat_group);
    }

    public function getPreviousPriceWithVatAttribute(): float
    {
        // previous price is include vat
        return $this->previous_price;
    }

    /**
     * Get the parent adjustable mode.
     */
    public function adjustable(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    public function priceAdjustment(): BelongsTo
    {
        return $this->belongsTo(PriceAdjustment::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', PriceAdjustmentRowStatusEnum::Pending());
    }

    public function scopeDone($query)
    {
        return $query->where('status', PriceAdjustmentRowStatusEnum::Done());
    }

    public function scopeProduct($query, string $productId)
    {
        return $query->where('adjustable_id', $productId)
            ->where('adjustable_type', Product::class);
    }

    public function scopeAddon($query, string $addonId)
    {
        return $query->where('adjustable_id', $addonId)
            ->where('adjustable_type', Addon::class);
    }

    public function scopeService($query, string $serviceId)
    {
        return $query->where('adjustable_id', $serviceId)
            ->where('adjustable_type', Service::class);
    }

    public function scopeFixedPrice($query, string $fixedPriceId)
    {
        return $query->where('adjustable_id', $fixedPriceId)
            ->where('adjustable_type', FixedPrice::class);
    }
}
