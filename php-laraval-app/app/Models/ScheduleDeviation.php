<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleDeviation extends Model
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
        'schedule_id',
        'types',
        'is_handled',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'types' => 'array',
        'is_handled' => 'boolean',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Find schedule deviation by schedule id
     * or create new one.
     */
    public static function findOrCreate(int $scheduleId): ScheduleDeviation
    {
        $deviation = self::where('schedule_id', $scheduleId)->first();

        if (! $deviation) {
            $deviation = self::create([
                'schedule_id' => $scheduleId,
                'types' => [],
                'is_handled' => false,
                'meta' => [],
            ]);
        }

        return $deviation;
    }

    /**
     * Schedule deviation relation to schedule model.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class)->withTrashed();
    }

    /**
     * Scope a query to only include handled deviations.
     */
    public function scopeHandled($query)
    {
        return $query->where('is_handled', true);
    }

    /**
     * Scope a query to only include unhandled deviations.
     */
    public function scopeUnhandled($query)
    {
        return $query->where('is_handled', false);
    }
}
