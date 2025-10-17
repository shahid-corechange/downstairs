<?php

namespace App\Models;

use App\Http\Traits\ModelQueryStringTrait;
use App\Http\Traits\TranslationsTrait;
use DateTimeInterface;
use Eloquent;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Model extends Eloquent
{
    use LogsActivity;
    use ModelQueryStringTrait;
    use TranslationsTrait;

    /**
     * Define the alias of the columns or relations.
     *
     * @var array<string,string>
     */
    protected array $aliases = [];

    /**
     * Define the columns that always be returned.
     *
     * @var string[]
     */
    protected array $includes = ['id'];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [];

    /**
     * Define the models for polymorphic relations.
     *
     * @var array<string,string[]>
     */
    protected array $relationsMorphMap = [];

    /**
     * Prepare a date for array or JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format(config('data.date_format'));
    }

    /**
     * Log the model changes.
     */
    public function getActivitylogOptions(): LogOptions
    {
        // exception models not to be logged
        if (in_array(get_class($this), [
            AuthenticationLog::class,
            Activity::class,
        ])) {
            return LogOptions::defaults()
                ->logOnly([])
                ->dontSubmitEmptyLogs();
        }

        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    /**
     * Get translation.
     */
    public function getTranslation(string $key): ?string
    {
        if (! $this->isRelation('translations')) {
            return null;
        }

        $translation = $this->translations->where('key', $key)->first();

        return $translation ? $translation[app()->getLocale()] : null;
    }

    /**
     * Set translation.
     */
    public function setTranslation(
        string $key,
        string $id,
        string $value,
        string $lang = null,
    ): void {
        $lang = $lang ?? app()->getLocale();
        $translation = Translation::where('translationable_id', $id)
            ->where('translationable_type', get_class($this))
            ->where('key', $key)->first();

        if ($translation) {
            $translation[$lang] = $value;
            $translation->save();
        } else {
            Translation::create([
                'translationable_type' => get_class($this),
                'translationable_id' => $id,
                'key' => $key,
                $lang => $value,
            ]);
        }
    }
}
