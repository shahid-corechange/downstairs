<?php

use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\TranslationEnum;
use App\Models\GlobalSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $key = Str::snake(GlobalSettingEnum::SubscriptionRefillSequence());
        $globalSetting = GlobalSetting::where('key', strtoupper($key))->first();

        if ($globalSetting) {
            $globalSetting->setTranslation('description', $globalSetting->id, 'Default subscription active booking', TranslationEnum::English());
            $globalSetting->setTranslation('description', $globalSetting->id, 'Standard prenumeration aktiv bokning', TranslationEnum::Swedish());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
