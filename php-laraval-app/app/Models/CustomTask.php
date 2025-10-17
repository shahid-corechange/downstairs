<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomTask extends Model
{
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
        'taskable_id',
        'taskable_type',
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'name' => ['translations' => ['id']],
        'description' => ['translations' => ['id']],
    ];

    /**
     * Define the models for polymorphic relations.
     *
     * @var array<string,string[]>
     */
    protected array $relationsMorphMap = [
        'taskable' => [Subscription::class, Schedule::class, Service::class, Addon::class, Product::class],
    ];

    public function getNameAttribute(): ?string
    {
        return $this->getTranslation('name');
    }

    /**
     * Set name attribute base on language.
     */
    public function setName(string $value, string $lang = null)
    {
        $this->setTranslation('name', $this->id, $value, $lang);
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->getTranslation('description');
    }

    /**
     * Set description attribute base on language.
     */
    public function setDescription(string $value, string $lang = null)
    {
        $this->setTranslation('description', $this->id, $value, $lang);
    }

    /**
     * Get the parent.
     */
    public function taskable(): MorphTo
    {
        return $this->morphTo('taskable');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }
}
