<?php

namespace App\Models;

use App\Http\Traits\ModelQueryStringTrait;
use DateTimeInterface;
use Eloquent;

class ViewModel extends Eloquent
{
    use ModelQueryStringTrait;

    /**
     * Define the relationship of the model.
     *
     * @var string[]
     */
    protected array $relationships = [];

    /**
     * Define the alias of the columns or relations.
     *
     * @var array<string,string>
     */
    protected array $aliases = [];

    /**
     * Prepare a date for array or JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format(config('data.date_format'));
    }

    /**
     * Get translation.
     */
    public function getTranslation(
        string $id,
        string $key,
        string $className
    ): ?string {
        $translation = Translation::where('translationable_id', $id)
            ->where('translationable_type', $className)
            ->where('key', $key)->first();

        return $translation ? $translation[app()->getLocale()] : null;
    }
}
