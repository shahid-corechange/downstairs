<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deviation extends Model
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
        'user_id',
        'schedule_id',
        'type',
        'reason',
        'is_handled',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_handled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class)->withTrashed();
    }

    /**
     * Scope a query to only include handled deviations.
     */
    public function scopeHandled(Builder $query)
    {
        return $query->where('is_handled', true);
    }

    /**
     * Scope a query to only include unhandled deviations.
     */
    public function scopeUnhandled(Builder $query)
    {
        return $query->where('is_handled', false);
    }
}
