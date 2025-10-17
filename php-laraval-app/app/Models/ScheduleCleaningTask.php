<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleCleaningTask extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'custom_task_id',
        'schedule_cleaning_id',
        'is_completed',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'name',
        'description',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_completed' => 'boolean',
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'name' => ['customTask' => ['name']],
        'description' => ['customTask' => ['description']],
    ];

    public function getNameAttribute(): string
    {
        return $this->customTask->name;
    }

    public function getDescriptionAttribute(): string
    {
        return $this->customTask->description;
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ScheduleCleaning::class, 'schedule_cleaning_id')->withTrashed();
    }

    public function customTask(): BelongsTo
    {
        return $this->belongsTo(CustomTask::class, 'custom_task_id');
    }
}
