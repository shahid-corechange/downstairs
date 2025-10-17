<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;

class Store extends Model
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
        'address_id',
        'name',
        'company_number',
        'phone',
        'dial_code',
        'email',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'formatted_phone',
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
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'formatted_phone' => ['dial_code', 'phone'],
    ];

    /**
     * Get the formatted customer's phone number.
     */
    public function getFormattedPhoneAttribute(): string
    {
        if (! $this->phone || $this->phone === $this->dial_code) {
            return '';
        }

        if (Str::startsWith($this->phone, $this->dial_code)) {
            return Str::replaceFirst($this->dial_code, "+{$this->dial_code} ", $this->phone);
        }

        return "+{$this->dial_code} {$this->phone}";
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class)->withTrashed();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'store_products')->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_users');
    }

    public function laundryOrders(): HasMany
    {
        return $this->hasMany(LaundryOrder::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(StoreSale::class);
    }
}
