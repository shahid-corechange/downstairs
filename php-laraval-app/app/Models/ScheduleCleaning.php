<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ScheduleCleaning extends Model
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
        'laundry_order_id', // for laundry add on
        'laundry_type', // for laundry add on
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function laundryOrder(): BelongsTo
    {
        return $this->belongsTo(LaundryOrder::class)->withTrashed();
    }

    public function schedule(): MorphOne
    {
        return $this->morphOne(Schedule::class, 'scheduleable');
    }
}
