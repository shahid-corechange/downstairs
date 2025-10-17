<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feedback extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'feedbacks';

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
        'option',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Define the alias of the columns or relations.
     *
     * @var array<string,string>
     */
    protected array $aliases = [
        'user' => 'feedbackable',
        'user_id' => 'feedbackable_id',
    ];

    /**
     * Define the models for polymorphic relations.
     *
     * @var array<string,string[]>
     */
    protected array $relationsMorphMap = [
        'feedbackable' => [User::class],
    ];

    /**
     * Get the parent feedbackable mode.
     */
    public function feedbackable(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }
}
