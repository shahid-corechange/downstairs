<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashierAttendance extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'store_id',
        'work_hour_id',
        'check_in_at',
        'check_out_at',
        'check_in_causer_id',
        'check_out_causer_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'total_hours',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'total_hours' => ['check_in_at', 'check_out_at'],
    ];

    public function getTotalHoursAttribute(): float
    {
        return ceil($this->check_in_at->diffInMinutes($this->check_out_at) / 15) / 4;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class)->withTrashed();
    }

    public function workHour(): BelongsTo
    {
        return $this->belongsTo(WorkHour::class);
    }

    public function checkInCauser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'check_in_causer_id')->withTrashed();
    }

    public function checkOutCauser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'check_out_causer_id')->withTrashed();
    }
}
