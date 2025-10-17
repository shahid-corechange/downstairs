<?php

namespace App\Models;

use App\Services\DiscountService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerDiscount extends Model
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
        'user_id',
        'type',
        'value',
        'start_date',
        'end_date',
        'usage_limit',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'is_active' => ['start_date', 'end_date', 'usage_limit', 'deleted_at'],
    ];

    /**
     * Get the is active attribute.
     */
    public function getIsActiveAttribute(): bool
    {
        return DiscountService::isActive(
            $this->start_date,
            $this->end_date,
            $this->usage_limit,
            $this->deleted_at
        );
    }

    /**
     * SQL raw expression for the is active attribute.
     */
    public function isActive(): string
    {
        return "
            IF(
                deleted_at IS NULL 
                AND (usage_limit is NULL OR usage_limit > 0)
                AND (start_date IS NULL OR start_date <= NOW()) 
                AND (end_date IS NULL OR end_date >= NOW()), 
            'true', 'false')
        ";
    }

    /**
     * Get the current discount by the user.
     */
    public static function getCurrentDiscountByUser(int $userId, string $type): ?self
    {
        return self::where('user_id', $userId)
            ->where('type', $type)
            ->where(function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->where('start_date', '<=', now())->where('end_date', '>=', now());
                })
                    ->orWhere(function (Builder $query) {
                        $query->whereNull('start_date')->orWhereNull('end_date');
                    });
            })
            ->where(function (Builder $query) {
                $query->whereNull('usage_limit')
                    ->orWhere('usage_limit', '>', 0);
            })
            ->orderBy('value', 'desc')
            ->first();
    }

    public static function useDiscount(int $userId, string $type): void
    {
        $discount = self::getCurrentDiscountByUser($userId, $type);

        if ($discount && $discount->usage_limit !== null) {
            $discount->update([
                'usage_limit' => $discount->usage_limit - 1,
            ]);
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Scope a query to only include discounts within the given period.
     */
    public function scopePeriod(Builder $query, ?string $startDate, ?string $endDate): Builder
    {
        return $query->where(function (Builder $query) use ($startDate, $endDate) {
            if ($startDate !== null && $endDate !== null) {
                $query->whereNot(function (Builder $query) use ($startDate, $endDate) {
                    $query->where('start_date', '>', $endDate)
                        ->orWhere('end_date', '<', $startDate);
                })
                    ->orWhere(function (Builder $query) {
                        $query->whereNull('start_date')->orWhereNull('end_date');
                    });
            }
        });
    }

    /**
     * Scope a query to only include discounts with available usage.
     *
     * @param  Builder  $query
     * @param  int  $limit minimum usage limit, default 0
     */
    public function scopeHasAvailableUsage($query, $limit = 0): Builder
    {
        return $query->where('usage_limit', '>', $limit)
            ->orWhereNull('usage_limit');
    }
}
