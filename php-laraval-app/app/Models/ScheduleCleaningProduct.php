<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleCleaningProduct extends Model
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
        'schedule_cleaning_id',
        'product_id',
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
        'schedule_id' => 'schedule_cleaning_id',
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'name' => ['product' => ['name']],
        'description' => ['product' => ['description']],
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

    public function getNameAttribute(): ?string
    {
        return $this->product->name;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->product->description;
    }

    /**
     * Schedule cleaning product relation to schedule cleaning model.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ScheduleCleaning::class, 'schedule_cleaning_id')->withTrashed();
    }

    /**
     * Schedule cleaning product relation to product model.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id')->withTrashed();
    }
}
