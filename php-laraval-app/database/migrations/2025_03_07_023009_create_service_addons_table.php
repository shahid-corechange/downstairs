<?php

use App\Enums\Product\ProductUnitEnum;
use App\Enums\VatNumbersEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_addons', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('addon_id')->constrained()->cascadeOnDelete();
        });

        foreach ($this->createAddons() as $addonData) {
            $nameData = $addonData['name'];
            $descriptionData = $addonData['description'];
            $serviceIds = $addonData['service_ids'];

            DB::transaction(function () use ($addonData, $nameData, $descriptionData, $serviceIds) {
                // Create addon
                $addonId = DB::table('addons')->insertGetId([
                    'fortnox_article_id' => $addonData['fortnox_article_id'] ?? null,
                    'unit' => $addonData['unit'],
                    'price' => $addonData['price'],
                    'credit_price' => $addonData['credit_price'],
                    'vat_group' => $addonData['vat_group'],
                    'has_rut' => $addonData['has_rut'],
                    'thumbnail_image' => $addonData['thumbnail_image'],
                    'color' => $addonData['color'] ?? '#718096',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create translations
                DB::table('translations')->insert([
                    [
                        'translationable_type' => 'App\\Models\\Addon',
                        'translationable_id' => $addonId,
                        'key' => 'name',
                        'en_US' => $nameData['en_US'],
                        'sv_SE' => $nameData['sv_SE'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'translationable_type' => 'App\\Models\\Addon',
                        'translationable_id' => $addonId,
                        'key' => 'description',
                        'en_US' => $descriptionData['en_US'],
                        'sv_SE' => $descriptionData['sv_SE'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);

                // Create service_addons relationships
                foreach ($serviceIds as $serviceId) {
                    // check if service is exists
                    if (! DB::table('services')->where('id', $serviceId)->exists()) {
                        continue;
                    }

                    DB::table('service_addons')->insert([
                        'service_id' => $serviceId,
                        'addon_id' => $addonId,
                    ]);
                }

                // add category to addon
                DB::table('categoryables')->insert([
                    'category_id' => config('downstairs.categories.laundry.id'),
                    'categoryable_type' => 'App\\Models\\Addon',
                    'categoryable_id' => $addonId,
                ]);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_addons');
    }

    private function createAddons(): array
    {
        return [
            [
                'unit' => ProductUnitEnum::Piece(),
                'price' => 0.00,
                'credit_price' => 0,
                'vat_group' => VatNumbersEnum::Zero(),
                'has_rut' => false,
                'name' => [
                    'en_US' => 'Laundry',
                    'sv_SE' => 'Tvätt',
                ],
                'description' => [
                    'en_US' => 'Pick up the laundry, bring it to the store, and deliver it back when it is done.',
                    'sv_SE' => 'Hämta tvätten, ta med till butiken och leverera tillbaka när det är klart.',
                ],
                'thumbnail_image' => 'https://storagestagingdownstairs.blob.core.windows.net/images/laundry.svg',
                'service_ids' => [1, 3],
            ],
        ];
    }
};
