<?php

namespace App\Models;

use App\Enums\PriceAdjustment\PriceAdjustmentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceAdjustment extends Model
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
        'causer_id',
        'type',
        'description',
        'price_type',
        'price',
        'execution_date',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
        'execution_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Price adjustment relation to price adjustment row model.
     */
    public function rows(): HasMany
    {
        return $this->hasMany(PriceAdjustmentRow::class, 'price_adjustment_id');
    }

    /**
     * Time adjustment relation to user model.
     */
    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    public function scopeNotDone($query)
    {
        return $query->whereIn(
            'status',
            [PriceAdjustmentStatusEnum::Pending(), PriceAdjustmentStatusEnum::Partial()]
        );
    }
}
