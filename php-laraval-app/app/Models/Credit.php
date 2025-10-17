<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Credit extends Model
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
        'user_id',
        'schedule_id',
        'issuer_id',
        'initial_amount',
        'remaining_amount',
        'type',
        'description',
        'valid_until',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'valid_until' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_system_created',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'is_system_created' => ['issuer_id'],
    ];

    public function getIsSystemCreatedAttribute(): bool
    {
        return is_null($this->issuer_id);
    }

    /**
     * Credit relation to user model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Credit relation to schedule model.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class)->withTrashed();
    }

    /**
     * Credit relation to credit transaction model.
     */
    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(CreditTransaction::class, 'credit_credit_transaction')
            ->withPivot('amount');
    }

    /**
     * Credit relation to issuer model.
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issuer_id')->withTrashed();
    }

    /**
     * Scope a query to only include valid credits.
     */
    public function scopeValid(Builder $query)
    {
        return $query->where('valid_until', '>=', now())
            ->where('remaining_amount', '>', 0);
    }
}
