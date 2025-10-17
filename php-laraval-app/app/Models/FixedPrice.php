<?php

namespace App\Models;

use App\Enums\FixedPrice\FixedPriceTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kolossal\Multiplex\HasMeta;

class FixedPrice extends Model
{
    use HasFactory;
    use HasMeta;
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
        'user_id',
        'type',
        'start_date',
        'end_date',
        'is_per_order',
        'laundry_product_ids',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_per_order' => 'boolean',
        'laundry_product_ids' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'total_price',
        'total_price_with_vat',
        'has_active_subscriptions',
        'is_active',
        'is_include_laundry',
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'total_price' => ['rows' => ['price']],
        'total_price_with_vat' => ['rows' => ['price', 'vat_group']],
        'has_active_subscriptions' => ['subscriptions' => ['is_paused']],
        'is_active' => ['subscriptions' => ['is_paused']],
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'is_active' => ['start_date', 'end_date', 'deleted_at'],
    ];

    /**
     * Get the total price of the fixed price.
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->rows->sum('price');
    }

    /**
     * Get the total price of the fixed price.
     */
    public function getTotalPriceWithVatAttribute(): float
    {
        return $this->rows->sum(fn (FixedPriceRow $row) => $row->price_with_vat);
    }

    /**
     * Check if the fixed price has active subscriptions.
     */
    public function getHasActiveSubscriptionsAttribute(): bool
    {
        return $this->subscriptions
            ->where('is_paused', false)
            ->count() > 0;
    }

    /**
     * SQL raw expressions for hasActiveSubscriptions.
     */
    public function hasActiveSubscriptions()
    {
        return "
            (
                SELECT CASE WHEN COUNT(*) > 0 THEN 'true' ELSE 'false' END
                FROM subscriptions
                WHERE fixed_prices.id = subscriptions.fixed_price_id
                AND subscriptions.is_paused = false
                AND subscriptions.deleted_at IS NULL
            )
        ";
    }

    /**
     * Check if the fixed price is active.
     */
    public function getIsActiveAttribute(): bool
    {
        if (! is_null($this->deleted_at) ||
            (! $this->has_active_subscriptions && $this->type !== FixedPriceTypeEnum::Laundry())
        ) {
            return false;
        }

        if (! is_null($this->start_date) && $this->start_date->startOfDay()->isFuture()) {
            return false;
        }

        if (! is_null($this->end_date) && $this->end_date->endOfDay()->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * SQL raw expression for the is active attribute.
     */
    public function isActive(): string
    {
        return "
            IF(
                deleted_at IS NULL 
                AND (start_date IS NULL OR start_date <= DATE(NOW())) 
                AND (end_date IS NULL OR end_date >= DATE(NOW()))
                AND ({$this->hasActiveSubscriptions()} = 'true' OR type = 'laundry'),
            'true', 'false')
        ";
    }

    /**
     * Check if the fixed price is include laundry.
     */
    public function getIsIncludeLaundryAttribute(): bool
    {
        return $this->getMeta('include_laundry', false);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(FixedPriceRow::class);
    }

    public function orderFixedPrices(): HasMany
    {
        return $this->hasMany(OrderFixedPrice::class);
    }

    public function laundryProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'fixed_price_laundry_products',
            'fixed_price_id',
            'product_id',
        );
    }

    /**
     * Get the cleaning and laundry fixed price.
     *
     * @param  int  $userId
     * @param  Carbon|null  $startDate
     * @param  Carbon|null  $endDate
     */
    public static function getCleaningAndLaundry($userId, $startDate = null, $endDate = null): ?FixedPrice
    {
        return self::getActive(
            $userId,
            [FixedPriceTypeEnum::CleaningAndLaundry()],
            $startDate,
            $endDate,
        );
    }

    /**
     * Get the active fixed price.
     *
     * @param  int  $userId
     * @param  array  $type
     * @param  Carbon|null  $startDate
     * @param  Carbon|null  $endDate
     */
    public static function getActive($userId, $type, $startDate = null, $endDate = null): ?FixedPrice
    {
        return FixedPrice::where('user_id', $userId)
            ->whereIn('type', $type)
            ->where(function (Builder $query) use ($startDate) {
                if (is_null($startDate)) {
                    return;
                }

                $query->whereNull('start_date')
                    ->orWhere(function (Builder $query) use ($startDate) {
                        $query->whereMonth('start_date', '>=', $startDate->month)
                            ->whereYear('start_date', '>=', $startDate->year);
                    });
            })
            ->where(function (Builder $query) use ($endDate) {
                if (is_null($endDate)) {
                    return;
                }

                $query->whereNull('end_date')
                    ->orWhere(function (Builder $query) use ($endDate) {
                        $query->whereMonth('end_date', '<=', $endDate->month)
                            ->whereYear('end_date', '<=', $endDate->year);
                    });
            })
            ->first();
    }
}
