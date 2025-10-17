<?php

namespace App\Http\Traits;

/**
 * Trait TranslationsTrait
 *
 * Add new method to the model
 */
trait TranslationsTrait
{
    /**
     * To update all of the translations
     */
    public function updateTranslations(array $translations)
    {
        foreach ($translations as $translation) {
            if ($translation['key']) {
                $this->translations()->where('key', $translation['key'])->update($translation);
            }
        }
    }
}
