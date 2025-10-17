<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreSaleProduct extends Model
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
        'store_sale_id',
        'product_id',
        'name',
        'note',
        'quantity',
        'price',
        'vat_group',
        'discount',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'price_with_vat',
        'discount_amount',
        'vat_amount',
        'price_with_discount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'discount' => 'float',
        'price' => 'float',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'price_with_vat' => ['price', 'vat_group', 'quantity'],
        'discount_amount' => ['price', 'vat_group', 'discount', 'quantity'],
        'vat_amount' => ['price', 'vat_group', 'quantity'],
        'price_with_discount' => ['price', 'vat_group', 'discount', 'quantity'],
    ];

    public function getPriceWithVatAttribute(): float
    {
        return price_with_vat($this->price, $this->vat_group) * $this->quantity;
    }

    public function getDiscountAmountAttribute(): float
    {
        return $this->price_with_vat * ($this->discount / 100);
    }

    public function getVatAmountAttribute(): float
    {
        return $this->price_with_vat * ($this->vat_group / 100);
    }

    public function getPriceWithDiscountAttribute(): float
    {
        return $this->price_with_vat - $this->discount_amount;
    }

    public function storeSale(): BelongsTo
    {
        return $this->belongsTo(StoreSale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
