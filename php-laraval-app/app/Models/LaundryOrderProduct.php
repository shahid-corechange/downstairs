<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaundryOrderProduct extends Model
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
        'laundry_order_id',
        'product_id',
        'name',
        'note',
        'quantity',
        'price',
        'vat_group',
        'discount',
        'has_rut',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'price_with_vat',
        'total_price_with_vat',
        'total_discount_amount',
        'total_vat_amount',
        'total_price_with_discount',
        'total_rut',
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
        'price_with_vat' => ['price', 'vat_group'],
        'total_price_with_vat' => ['price', 'vat_group', 'quantity'],
        'total_discount_amount' => ['price', 'vat_group', 'discount', 'quantity'],
        'total_vat_amount' => ['price', 'vat_group', 'quantity'],
        'total_price_with_discount' => ['price', 'vat_group', 'discount', 'quantity'],
        'total_rut' => ['has_rut', 'price', 'vat_group', 'quantity'],
    ];

    public function getPriceWithVatAttribute(): float
    {
        return price_with_vat($this->price, $this->vat_group);
    }

    public function getTotalPriceWithVatAttribute(): float
    {
        return $this->price_with_vat * $this->quantity;
    }

    public function getTotalDiscountAmountAttribute(): float
    {
        return $this->total_price_with_vat * ($this->discount / 100);
    }

    public function getTotalVatAmountAttribute(): float
    {
        return $this->quantity * ($this->price * ($this->vat_group / 100));
    }

    public function getTotalPriceWithDiscountAttribute(): float
    {
        return $this->total_price_with_vat - $this->total_discount_amount;
    }

    public function getTotalRutAttribute(): float
    {
        return $this->has_rut ? $this->total_price_with_discount * 0.25 : 0;
    }

    public function laundryOrder(): BelongsTo
    {
        return $this->belongsTo(LaundryOrder::class)->withTrashed();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
