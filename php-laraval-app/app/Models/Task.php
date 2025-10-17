<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
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
        'schedule_employee_id',
        'name',
        'description',
        'is_completed',
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
     * Define the alias of the columns or relations.
     *
     * @var array<string,string>
     */
    protected array $aliases = [
        'schedule_id' => 'schedule_employee_id',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ScheduleEmployee::class, 'schedule_employee_id')->withTrashed();
    }

    public function customTask(): BelongsTo
    {
        return $this->belongsTo(CustomTask::class, 'custom_task_id');
    }
}
