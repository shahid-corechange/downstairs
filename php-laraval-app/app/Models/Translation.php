<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Translation extends Model
{
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
        'translationable_type',
        'translationable_id',
        'key',
        'en_US',
        'nn_NO',
        'sv_SE',
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
    ];

    /**
     * Define the columns that always be returned.
     *
     * @var string[]
     */
    protected array $includes = ['id', 'key', 'en_US', 'nn_NO', 'sv_SE'];

    /**
     * Define the models for polymorphic relations.
     *
     * @var array<string,string[]>
     */
    protected array $relationsMorphMap = [
        'translationable' => [
            Service::class,
            Addon::class,
            Product::class,
            Category::class,
            GlobalSetting::class,
            CustomTask::class,
            LaundryPreference::class,
            Option::class,
            ProductCategory::class,
            PropertyType::class,
        ],
    ];

    /**
     * Get the parent.
     */
    public function translationable(): MorphTo
    {
        return $this->morphTo('translationable');
    }
}
