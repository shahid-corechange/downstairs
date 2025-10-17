<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeAdjustment extends Model
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
        'schedule_employee_id',
        'causer_id',
        'quarters',
        'reason',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Time adjustment relation to schedule employee model.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ScheduleEmployee::class, 'schedule_employee_id')->withTrashed();
    }

    /**
     * Time adjustment relation to user model.
     */
    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }
}
