<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedPriceRow extends Model
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
        'fixed_price_id',
        'type',
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
    ];

    public function getPriceWithVatAttribute(): float
    {
        return price_with_vat($this->price, $this->vat_group);
    }

    public function fixedPrice(): BelongsTo
    {
        return $this->belongsTo(FixedPrice::class);
    }
}
