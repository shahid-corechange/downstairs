<?php

namespace App\Models;

use App\Enums\Order\OrderStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
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
        'customer_id',
        'service_id',
        'subscription_id',
        'invoice_id',
        'order_fixed_price_id',
        'status',
        'paid_by',
        'paid_at',
        'ordered_at',
        'orderable_type',
        'orderable_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'ordered_at' => 'datetime',
    ];

    /**
     * Define the alias of the columns or relations.
     *
     * @var array<string,string>
     */
    protected array $aliases = [
        'schedule' => 'orderable',
    ];

    /**
     * Define the models for polymorphic relations.
     *
     * @var array<string,string[]>
     */
    protected array $relationsMorphMap = [
        'orderable' => [Schedule::class, LaundryOrder::class, StoreSale::class],
    ];

    /**
     * Order relation to user model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Order relation to customer model.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    /**
     * Order relation to service model.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    /**
     * Order relation to order row model.
     */
    public function rows(): HasMany
    {
        return $this->hasMany(OrderRow::class);
    }

    /**
     * Get the parent.
     */
    public function orderable(): MorphTo
    {
        return $this->morphTo('orderable')->withTrashed();
    }

    /**
     * Get the invoice.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class)->withTrashed();
    }

    /**
     * Order relation to subscription model.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class)->withTrashed();
    }

    public function fixedPrice(): BelongsTo
    {
        return $this->belongsTo(OrderFixedPrice::class, 'order_fixed_price_id')->withTrashed();
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', OrderStatusEnum::Draft());
    }
}
