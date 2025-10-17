<?php

namespace App\Helpers\Util;

class TextTranslation
{
    /**
     * Create a new message translation instance.
     *
     * @return string|array|null
     */
    public function __construct(
        public ?string $key = null,
        public array $replace = [],
        public ?string $locale = null,
    ) {
    }

    public function get(): ?string
    {
        return __($this->key, $this->replace, $this->locale);
    }
}
