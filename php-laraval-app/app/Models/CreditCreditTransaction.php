<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditCreditTransaction extends Model
{
    protected $table = 'credit_credit_transaction';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'credit_id',
        'credit_transaction_id',
        'amount',
    ];

    public function credit(): BelongsTo
    {
        return $this->belongsTo(Credit::class);
    }

    public function creditTransaction(): BelongsTo
    {
        return $this->belongsTo(CreditTransaction::class);
    }
}
