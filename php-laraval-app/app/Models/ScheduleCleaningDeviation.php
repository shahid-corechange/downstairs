<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleCleaningDeviation extends Model
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
        'schedule_cleaning_id',
        'types',
        'is_handled',
        'meta',
    ];

    /**
     * Define the alias of the columns or relations.
     *
     * @var array<string,string>
     */
    protected array $aliases = [
        'schedule_id' => 'schedule_cleaning_id',
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
     * Find schedule cleaning deviation by schedule cleaning id
     * or create new one.
     */
    public static function findOrCreate(int $scheduleCleaningId): ScheduleCleaningDeviation
    {
        $deviation = self::where('schedule_cleaning_id', $scheduleCleaningId)->first();

        if (! $deviation) {
            $deviation = self::create([
                'schedule_cleaning_id' => $scheduleCleaningId,
                'types' => [],
                'is_handled' => false,
                'meta' => [],
            ]);
        }

        return $deviation;
    }

    /**
     * Schedule cleaning product relation to schedule cleaning model.
     */
    public function scheduleCleaning(): BelongsTo
    {
        return $this->belongsTo(ScheduleCleaning::class, 'schedule_cleaning_id')->withTrashed();
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
