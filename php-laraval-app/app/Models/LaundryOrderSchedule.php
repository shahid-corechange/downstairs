<?php

namespace App\Models;

use App\Enums\LaundryOrder\LaundryOrderScheduleTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaundryOrderSchedule extends Model
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
        'team_id',
        'type',
        'date',
        'time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function laundryOrder(): BelongsTo
    {
        return $this->belongsTo(LaundryOrder::class)->withTrashed();
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class)->withTrashed();
    }

    public function scopePickup(Builder $query): Builder
    {
        return $query->where('type', LaundryOrderScheduleTypeEnum::Pickup);
    }

    public function scopeDelivery(Builder $query): Builder
    {
        return $query->where('type', LaundryOrderScheduleTypeEnum::Delivery);
    }
}
