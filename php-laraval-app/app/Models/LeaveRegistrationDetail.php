<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRegistrationDetail extends Model
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
        'leave_registration_id',
        'fortnox_absence_transaction_id',
        'start_at',
        'end_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'hours',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'hours' => ['start_at', 'end_at'],
    ];

    public function getHoursAttribute(): float
    {
        $hours = ceil($this->start_at->diffInHours($this->end_at));

        return $hours > 8 ? 8 : $hours;
    }

    public function leaveRegistration(): BelongsTo
    {
        return $this->belongsTo(LeaveRegistration::class);
    }
}
