<?php

namespace App\Models;

use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\Invoice\InvoiceStatusEnum;
use App\Enums\Invoice\InvoiceTypeEnum;
use App\Enums\MembershipTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kolossal\Multiplex\HasMeta;

class Invoice extends Model
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
        'customer_id',
        'fortnox_invoice_id',
        'fortnox_tax_reduction_id',
        'type',
        'category',
        'month',
        'year',
        'remark',
        'total_gross',
        'total_net',
        'total_vat',
        'total_rut',
        'status',
        'sent_at',
        'due_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'total_include_vat',
        'total_invoiced',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'total_gross' => 'float',
        'total_net' => 'float',
        'total_vat' => 'float',
        'total_rut' => 'float',
        'sent_at' => 'datetime',
        'due_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'total_include_vat' => ['total_net', 'total_vat'],
        'total_invoiced' => ['total_net', 'total_vat', 'total_rut'],
    ];

    public static function findOrCreate(
        int $userId,
        int $customerId,
        int $month,
        int $year,
        string $type,
        string $category = 'invoice',
    ): self {
        $result = self::where('customer_id', $customerId)
            ->where('type', $type)
            ->where('category', $category)
            ->where('month', $month)
            ->where('year', $year)
            ->where('status', InvoiceStatusEnum::Open())
            ->first();

        if (! $result) {
            $sentDay = get_setting(GlobalSettingEnum::InvoiceSentDate(), 5);
            $customer = Customer::find($customerId);
            $sentAt = Carbon::createFromDate($year, $month + 1, $sentDay)->startOfDay();
            $dueAt = $sentAt->copy();

            if ($customer->due_days) {
                $dueAt->addDays($customer->due_days);
            } else {
                $dueDay = get_setting(GlobalSettingEnum::InvoiceDueDays(), 30);
                $dueAt->addDays($dueDay);
            }

            $result = self::create([
                'user_id' => $userId,
                'customer_id' => $customerId,
                'type' => $type,
                'category' => $category,
                'month' => $month,
                'year' => $year,
                'status' => InvoiceStatusEnum::Open(),
                'sent_at' => $sentAt,
                'due_at' => $dueAt,
            ]);
        }

        return $result;
    }

    /**
     * Get type of the invoice based on user type and membership type.
     */
    public static function getUserType(int $userId, string $membershipType, string $defaultType): string
    {
        if ($membershipType === MembershipTypeEnum::Private()) {
            $fixedPrice = FixedPrice::getCleaningAndLaundry($userId, now(), now());

            return $fixedPrice ? InvoiceTypeEnum::CleaningAndLaundry() : $defaultType;
        } else {
            return InvoiceTypeEnum::CleaningAndLaundry();
        }
    }

    /**
     * Get total include VAT attribute.
     */
    public function getTotalIncludeVatAttribute(): float
    {
        return round($this->total_net + $this->total_vat);
    }

    /**
     * Get total invoiced after VAT and RUT.
     */
    public function getTotalInvoicedAttribute(): float
    {
        return $this->total_include_vat - $this->total_rut;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function monthlyFixedPrices(): HasManyThrough
    {
        return $this->hasManyThrough(
            OrderFixedPrice::class,
            Order::class,
            'invoice_id',
            'id',
            'id',
            'order_fixed_price_id',
        )
            ->where('is_per_order', false)
            ->groupBy('id', 'invoice_id');
    }

    public function scopeOpen(Builder $query, string $type): Builder
    {
        $now = now();

        return $query->where('type', $type)
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->where('status', InvoiceStatusEnum::Open());
    }
}
