<?php

namespace Tests\Model;

use App\Models\Translation;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TranslationTest extends TestCase
{
    /** @test */
    public function translationsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('translations', [
                'id',
                'translationable_type',
                'translationable_id',
                'key',
                'en_US',
                'nn_NO',
                'sv_SE',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function translationHasTranslationable(): void
    {
        $translation = Translation::first();

        $this->assertIsObject($translation->translationable);
    }
}
