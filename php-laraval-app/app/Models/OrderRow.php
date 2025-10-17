<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderRow extends Model
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
        'order_id',
        'fortnox_article_id',
        'description',
        'quantity',
        'unit',
        'price',
        'discount_percentage',
        'vat',
        'has_rut',
        'internal_note',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'price_with_vat',
        'is_service_row',
        'is_material_row',
        'total_amount',
        'total_gross_amount',
        'total_net_amount',
        'vat_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'float',
        'price' => 'float',
        'has_rut' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'price_with_vat' => ['price', 'vat'],
        'is_service_row' => ['fortnox_article_id'],
        'is_material_row' => ['fortnox_article_id'],
        'total_amount' => ['price', 'vat', 'quantity', 'discount_percentage'],
        'total_gross_amount' => ['price', 'quantity'],
        'total_net_amount' => ['price', 'quantity', 'discount_percentage'],
        'vat_amount' => ['price', 'vat', 'quantity', 'discount_percentage'],
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'is_service_row' => ['order.service' => ['fortnox_article_id']],
    ];

    public function getPriceWithVatAttribute(): float
    {
        return price_with_vat($this->price, $this->vat);
    }

    public function getIsServiceRowAttribute(): bool
    {
        return $this->fortnox_article_id && $this->fortnox_article_id === $this->order->service->fortnox_article_id;
    }

    public function getIsMaterialRowAttribute(): bool
    {
        $material = get_material();

        return $this->fortnox_article_id && $this->fortnox_article_id === $material->fortnox_article_id;
    }

    /**
     * Get the total amount for this row including VAT and discount
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->price_with_vat * $this->quantity * (1 - ($this->discount_percentage ?? 0) / 100);
    }

    /**
     * Get the total gross amount for this row.
     *
     * Gross amount is the total amount excluding VAT and discount.
     */
    public function getTotalGrossAmountAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get the total net amount for this row.
     *
     * Net amount is the total amount including discount but excluding VAT.
     */
    public function getTotalNetAmountAttribute(): float
    {
        return $this->price * $this->quantity * (1 - ($this->discount_percentage ?? 0) / 100);
    }

    /**
     * Get the VAT amount for this row.
     *
     * VAT amount is calculated from the net amount.
     */
    public function getVatAmountAttribute(): float
    {
        return $this->total_net_amount * ($this->vat / 100);
    }

    /**
     * Order detail relation to order model.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->withTrashed();
    }
}
