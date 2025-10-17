<?php

namespace Tests\Model;

use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Models\GlobalSetting;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GlobalSettingTest extends TestCase
{
    /** @test */
    public function globalSettingsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('global_settings', [
                'id',
                'key',
                'value',
                'type',
                'created_at',
                'updated_at',
            ]),
        );
    }

    /** @test */
    public function globalSettingHasDescription(): void
    {
        $globalSetting = GlobalSetting::first();

        $this->assertIsString($globalSetting->description);
    }

    /** @test */
    public function globalSettingHasTranslations(): void
    {
        $globalSetting = GlobalSetting::first();

        $this->assertIsObject($globalSetting->translations);
    }

    /** @test */
    public function globalSettingCanGetValue(): void
    {
        $value = get_setting(GlobalSettingEnum::MaxMonthShow());

        $this->assertIsInt($value);
    }
}
