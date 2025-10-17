<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditTransaction extends Model
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
        'type',
        'total_amount',
        'description',
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
     * Credit transaction relation to user model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Credit transaction relation to schedule model.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id')->withTrashed();
    }

    /**
     * Credit transaction relation to issuer model.
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issuer_id')->withTrashed();
    }

    /**
     * relation to pivot table.
     */
    public function creditCreditTransactions(): HasMany
    {
        return $this->hasMany(CreditCreditTransaction::class);
    }

    /**
     * relation to credit model.
     */
    public function credits(): BelongsToMany
    {
        return $this->belongsToMany(Credit::class, 'credit_credit_transaction')
            ->withPivot('amount');
    }
}
