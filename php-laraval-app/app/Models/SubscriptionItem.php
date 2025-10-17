<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SubscriptionItem extends Model
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
        'subscription_id',
        'itemable_id',
        'itemable_type',
        'quantity',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Define the models for polymorphic relations.
     *
     * @var array<string,string[]>
     */
    protected array $relationsMorphMap = [
        'itemable' => [Addon::class, Product::class],
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id')->withTrashed();
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter by itemable object.
     *
     * @param  Addon|Product  $itemable
     */
    public function scopeWhereItemable(Builder $query, $itemable)
    {
        return $query->where('itemable_id', $itemable->id)
            ->where('itemable_type', get_class($itemable));
    }
}
