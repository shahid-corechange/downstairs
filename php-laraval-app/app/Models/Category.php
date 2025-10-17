<?php

namespace App\Models;

use App\Http\Traits\TranslationsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;
    use TranslationsTrait;

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
        'thumbnail_image',
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
        'deleted_at' => 'datetime',
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

    public function getNameAttribute(): ?string
    {
        return $this->getTranslation('name');
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->getTranslation('description');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function services(): MorphToMany
    {
        return $this->morphedByMany(Service::class, 'categoryable');
    }

    public function addons(): MorphToMany
    {
        return $this->morphedByMany(Addon::class, 'categoryable');
    }

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'categoryable');
    }
}
