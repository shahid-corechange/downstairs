<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreSale extends Model
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
        'store_id',
        'causer_id',
        'status',
        'payment_method',
    ];

    protected $appends = [
        'total_price_with_vat',
        'total_price_with_discount',
        'total_discount',
        'total_vat',
        'total_to_pay',
        'round_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'total_price_with_vat' => ['products' => ['price', 'vat_group', 'quantity']],
        'total_price_with_discount' => ['products' => ['price', 'vat_group', 'discount', 'quantity']],
        'total_discount' => ['products' => ['price', 'vat_group', 'discount', 'quantity']],
        'total_vat' => ['products' => ['price', 'vat_group', 'quantity']],
        'total_to_pay' => ['products' => ['price', 'vat_group', 'discount', 'quantity']],
        'rounded_total_to_pay' => ['products' => ['price', 'vat_group', 'discount', 'quantity']],
        'round_amount' => ['products' => ['price', 'vat_group', 'discount', 'quantity']],
    ];

    public function getTotalPriceWithVatAttribute(): float
    {
        return $this->products->sum('price_with_vat');
    }

    public function getTotalPriceWithDiscountAttribute(): float
    {
        return $this->products->sum('price_with_discount');
    }

    public function getTotalDiscountAttribute(): float
    {
        return $this->products->sum('discount_amount');
    }

    public function getTotalVatAttribute()
    {
        // group by vat_group and sum the vat_amount
        return $this->products
            ->groupBy('vat_group')
            ->map(function ($group) {
                return $group->sum('vat_amount');
            });
    }

    public function getTotalToPayAttribute(): float
    {
        return $this->products->sum('price_with_discount');
    }

    public function getRoundedTotalToPayAttribute(): float
    {
        return round($this->total_to_pay);
    }

    public function getRoundAmountAttribute(): float
    {
        return $this->rounded_total_to_pay - $this->total_to_pay;
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class)->withTrashed();
    }

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id')->withTrashed();
    }

    public function products(): HasMany
    {
        return $this->hasMany(StoreSaleProduct::class);
    }

    public function order(): MorphOne
    {
        return $this->morphOne(Order::class, 'orderable')->withTrashed();
    }
}
