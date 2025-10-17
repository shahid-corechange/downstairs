<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kolossal\Multiplex\HasMeta;

class OrderFixedPrice extends Model
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
        'fixed_price_id',
        'type',
        'is_per_order',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_per_order' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'total_fixed_price',
        'total_gross_amount',
        'total_net_amount',
        'total_vat_amount',
        'total_rut_amount',
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'total_fixed_price' => ['rows' => ['price', 'vat_group', 'quantity']],
        'total_gross_amount' => ['rows' => ['price', 'quantity']],
        'total_net_amount' => ['rows' => ['price', 'quantity']],
        'total_vat_amount' => ['rows' => ['price', 'vat_group', 'quantity']],
        'total_rut_amount' => ['rows' => ['price', 'vat_group', 'quantity', 'has_rut']],
    ];

    public function getTotalFixedPriceAttribute(): float
    {
        return $this->rows->sum('total_price');
    }

    public function getTotalGrossAmountAttribute(): float
    {
        return $this->rows->sum('total_gross_amount');
    }

    public function getTotalNetAmountAttribute(): float
    {
        return $this->rows->sum('total_net_amount');
    }

    public function getTotalVatAmountAttribute(): float
    {
        return $this->rows->sum('vat_amount');
    }

    public function getTotalRutAmountAttribute(): float
    {
        return $this->rows->sum('rut_amount');
    }

    public function fixedPrice(): BelongsTo
    {
        return $this->belongsTo(FixedPrice::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(OrderFixedPriceRow::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function laundryProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'order_fixed_price_laundry_products',
            'order_fixed_price_id',
            'product_id',
        );
    }
}
