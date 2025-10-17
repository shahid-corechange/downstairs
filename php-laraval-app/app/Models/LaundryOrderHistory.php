<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaundryOrderHistory extends Model
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
        'laundry_order_id',
        'type',
        'note',
        'causer_id',
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

    public function laundryOrder(): BelongsTo
    {
        return $this->belongsTo(LaundryOrder::class)->withTrashed();
    }

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
