<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderFixedPriceRow extends Model
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
        'order_fixed_price_id',
        'type',
        'description',
        'quantity',
        'price',
        'vat_group',
        'has_rut',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'price_with_vat',
        'total_price',
        'total_gross_amount',
        'total_net_amount',
        'vat_amount',
        'rut_amount',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
        'total_price' => ['price', 'vat_group', 'quantity'],
        'total_gross_amount' => ['price', 'quantity'],
        'total_net_amount' => ['price', 'quantity'],
        'vat_amount' => ['price', 'vat_group', 'quantity'],
        'rut_amount' => ['price', 'vat_group', 'quantity', 'has_rut'],
    ];

    public function getPriceWithVatAttribute(): float
    {
        return price_with_vat($this->price, $this->vat_group);
    }

    public function getTotalPriceAttribute(): float
    {
        return $this->price_with_vat * $this->quantity;
    }

    /**
     * Get the total gross amount for this row.
     *
     * Gross amount is the total amount excluding VAT.
     */
    public function getTotalGrossAmountAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get the total net amount for this row.
     *
     * Net amount is the same as gross amount because there is no discount.
     */
    public function getTotalNetAmountAttribute(): float
    {
        return $this->total_gross_amount;
    }

    /**
     * Get the VAT amount for this row.
     *
     * VAT amount is calculated from the net amount.
     */
    public function getVatAmountAttribute(): float
    {
        return $this->total_net_amount * ($this->vat_group / 100);
    }

    public function getRutAmountAttribute(): float
    {
        return $this->has_rut ? $this->total_price * 0.5 : 0;
    }

    public function fixedPrice(): BelongsTo
    {
        return $this->belongsTo(OrderFixedPrice::class, 'order_fixed_price_id')->withTrashed();
    }
}
