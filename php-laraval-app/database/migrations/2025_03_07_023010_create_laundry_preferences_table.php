<?php

use App\Enums\VatNumbersEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laundry_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedDecimal('price')->default(0);
            $table->unsignedDecimal('percentage')->default(0);
            $table->unsignedTinyInteger('vat_group')->default(VatNumbersEnum::TwentyFive());
            $table->unsignedSmallInteger('hours');
            $table->boolean('include_holidays')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        foreach ($this->preferences() as $preference) {
            DB::transaction(function () use ($preference) {
                // Create laundry preference using DB statement
                $laundryPreferenceId = DB::table('laundry_preferences')->insertGetId([
                    'price' => $preference['price'],
                    'percentage' => $preference['percentage'],
                    'vat_group' => $preference['vat_group'],
                    'hours' => $preference['hours'],
                    'include_holidays' => $preference['include_holidays'] ?? false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create translations using DB statements
                $translations = [
                    array_merge($preference['name'], ['key' => 'name']),
                    array_merge($preference['description'], ['key' => 'description']),
                ];

                foreach ($translations as $translation) {
                    DB::table('translations')->insert([
                        'translationable_type' => 'App\\Models\\LaundryPreference',
                        'translationable_id' => $laundryPreferenceId,
                        'key' => $translation['key'],
                        'en_US' => $translation['en_US'],
                        'sv_SE' => $translation['sv_SE'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundry_preferences');
    }

    private function preferences(): array
    {
        return [
            [
                'name' => [
                    'en_US' => 'Normal (3 days)',
                    'sv_SE' => 'Normal (3 dagar)',
                ],
                'description' => [
                    'en_US' => 'The laundry will be done in 3 days',
                    'sv_SE' => 'Kläderna kommer att vara klara på 3 dagar',
                ],
                'price' => 0,
                'percentage' => 0,
                'vat_group' => VatNumbersEnum::Zero(),
                'hours' => 72,
            ],
            [
                'name' => [
                    'en_US' => 'Express (2 days)',
                    'sv_SE' => 'Express (2 dagar)',
                ],
                'description' => [
                    'en_US' => 'The laundry will be done in 2 days',
                    'sv_SE' => 'Kläderna kommer att vara klara på 2 dagar',
                ],
                'price' => 0,
                'percentage' => 50,
                'vat_group' => VatNumbersEnum::Zero(),
                'hours' => 48,
            ],
            [
                'name' => [
                    'en_US' => '24h (1 day, not a holiday)',
                    'sv_SE' => '24h (1 dag, ej helgdag)',
                ],
                'description' => [
                    'en_US' => 'The laundry will be done in 24 hours, not a holiday',
                    'sv_SE' => 'Kläderna kommer att vara klara på 24 timmar, ej helgdag',
                ],
                'price' => 0,
                'percentage' => 100,
                'vat_group' => VatNumbersEnum::Zero(),
                'hours' => 24,
            ],
        ];
    }
};
