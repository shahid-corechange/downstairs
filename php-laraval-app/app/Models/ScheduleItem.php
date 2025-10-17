<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ScheduleItem extends Model
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
        'schedule_id',
        'itemable_id',
        'itemable_type',
        'price',
        'quantity',
        'discount_percentage',
        'payment_method',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'name',
        'description',
    ];

    /**
     * Define the alias of the columns or relations.
     *
     * @var array<string,string>
     */
    protected array $aliases = [
        'item' => 'itemable',
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'name' => ['itemable' => ['name']],
        'description' => ['itemable' => ['description']],
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
        'quantity' => 'float',
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

    public function getNameAttribute(): ?string
    {
        return $this->itemable->name;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->itemable->description;
    }

    /**
     * Schedule item relation to schedule model.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class)->withTrashed();
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
