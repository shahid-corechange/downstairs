<?php

namespace Database\Seeders;

use App\Enums\Product\ProductUnitEnum;
use App\Enums\TranslationEnum;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DefaultProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        foreach ($this->getProducts() as $key => $data) {
            $product = Product::create([
                'service_id' => $data['service_id'],
                'category_id' => $data['category_id'],
                'fortnox_article_id' => $data['fortnox_article_id'] ?? null,
                'unit' => ProductUnitEnum::Piece(),
                'price' => $data['price'],
                'credit_price' => $data['credit_price'],
                'vat_group' => $data['vat_group'],
                'has_rut' => $data['has_rut'],
                'in_app' => $data['in_app'],
                'in_store' => $data['in_store'],
                'color' => $data['meta']['color'] ?? '#718096',
                'thumbnail_image' => ! isset($data['image']) ? null :
                    'https://storagestagingdownstairs.blob.core.windows.net/images/'.
                    $data['image'],
            ]);

            $product->translations()->createMany([
                [
                    'key' => 'name',
                    ...$data['name'],
                ],
                [
                    'key' => 'description',
                    ...$data['description'],
                ],
            ]);

            // Color is now a regular attribute, not meta

            if (isset($data['task'])) {
                foreach ($data['task'] as $task) {
                    $customTask = $product->tasks()->create([]);
                    $customTask->setName($task['name_sv_se'], TranslationEnum::Swedish());
                    $customTask->setDescription($task['description_sv_se'], TranslationEnum::Swedish());
                    $customTask->setName($task['name_en_us'], TranslationEnum::English());
                    $customTask->setDescription($task['description_en_us'], TranslationEnum::English());
                }
            }
        }
    }

    private function getProducts(): array
    {
        return [
            [
                'service_id' => 1,
                'category_id' => 1,
                'fortnox_article_id' => 36,
                'price' => 122.4,
                'credit_price' => 1,
                'vat_group' => 25,
                'has_rut' => true,
                'in_app' => true,
                'in_store' => false,
                'image' => 'bottle-recycle.svg',
                'name' => [
                    'en_US' => 'Recycling',
                    'nn_NO' => 'Gjenvinning',
                    'sv_SE' => 'Återvinning',
                ],
                'description' => [
                    'en_US' => 'Downstairs takes care of paper, plastic, glass and metal for recycling. Must be sorted',
                    'nn_NO' => 'Downstairs tar med papir, plast, glass og metall for gjenvinning. Må være sortert.',
                    'sv_SE' => 'Downstairs tar med papp, plast, glas och metall för återvinning. Ska vara sorterat.',
                ],
                'meta' => [
                    'color' => '#4E827A',
                ],
                'task' => [
                    [
                        'name_sv_se' => 'Återvinning',
                        'description_sv_se' => 'Ta med papp, plast, glas och metall. '.
                            'Sortera och släng i närmaste återvinningstation.',
                        'name_en_us' => 'Recycling',
                        'description_en_us' => 'Bring cardboard, plastic, glass and metal. '.
                            'Sort and throw away at the nearest recycling station.',
                    ],
                ],
            ],
            [
                'service_id' => 1,
                'category_id' => 1,
                'fortnox_article_id' => 37,
                'price' => 122.4,
                'credit_price' => 1,
                'vat_group' => 25,
                'has_rut' => true,
                'in_app' => true,
                'in_store' => false,
                'image' => 'oven-cleaning.svg',
                'name' => [
                    'en_US' => 'Oven',
                    'nn_NO' => 'Ovn',
                    'sv_SE' => 'Ugn',
                ],
                'description' => [
                    'en_US' => 'Downstairs cleans the oven inside and out. The oven must be cold.',
                    'nn_NO' => 'Downstairs rengjer ovnen innvendig og utvendig. Ovnen må være kald.',
                    'sv_SE' => 'Downstairs rengör ugnen inuti och utanpå. Ugnen måste vara kall.',
                ],
                'meta' => [
                    'color' => '#F96B3F',
                ],
                'task' => [
                    [
                        'name_sv_se' => 'Ugn',
                        'description_sv_se' => 'Rengör ugnen.',
                        'name_en_us' => 'Oven',
                        'description_en_us' => 'Clean the oven.',
                    ],
                ],
            ],
            [
                'service_id' => 1,
                'category_id' => 1,
                'fortnox_article_id' => 38,
                'price' => 122.4,
                'credit_price' => 1,
                'vat_group' => 25,
                'has_rut' => true,
                'in_app' => true,
                'in_store' => false,
                'image' => 'fridge.svg',
                'name' => [
                    'en_US' => 'Cleaning fridge',
                    'nn_NO' => 'Rengjøring kjøleskap',
                    'sv_SE' => 'Rengöring kyl',
                ],
                'description' => [
                    'en_US' => 'Downstairs empties the fridge, wipes clean. Sets the food back nice and tidy. '.
                        'Throws away food that is not edible.',
                    'nn_NO' => 'Downstairs tømmer kjøleskapet, tørker av rengjør. Setter maten tilbake pent og ryddig.',
                    'sv_SE' => 'Downstairs tömmer kylen torkar av rengör. Ställer in maten igen snyggt och iordning. '.
                        'Slänger mat som ej är ätbart.',
                ],
                'meta' => [
                    'color' => '#4E6382',
                ],
                'task' => [
                    [
                        'name_sv_se' => 'Rengöring kyl',
                        'description_sv_se' => 'Rengöring av kylen.',
                        'name_en_us' => 'Cleaning fridge',
                        'description_en_us' => 'Cleaning the fridge.',
                    ],
                ],
            ],
            [
                'service_id' => 1,
                'category_id' => 1,
                'fortnox_article_id' => 39,
                'price' => 122.4,
                'credit_price' => 1,
                'vat_group' => 25,
                'has_rut' => false,
                'in_app' => true,
                'in_store' => false,
                'image' => 'toilet-paper.svg',
                'name' => [
                    'en_US' => 'Toilet paper',
                    'nn_NO' => 'Toalettpapir',
                    'sv_SE' => 'Toalettpapper',
                ],
                'description' => [
                    'en_US' => 'Downstairs fills up the toilet paper',
                    'nn_NO' => 'Downstairs fyller på toalettpapir',
                    'sv_SE' => 'Downstairs fyller på toalettpapper',
                ],
                'meta' => [
                    'color' => '#FCA53F',
                ],
                'task' => [
                    [
                        'name_sv_se' => 'Toalettpapper',
                        'description_sv_se' => 'Fylla på toalettpapper.',
                        'name_en_us' => 'Toilet paper',
                        'description_en_us' => 'Refill toilet paper.',
                    ],
                ],
            ],
            [
                'service_id' => null,
                'category_id' => 2,
                'fortnox_article_id' => 40,
                'price' => 87.2,
                'credit_price' => null,
                'vat_group' => 25,
                'has_rut' => false,
                'in_app' => false,
                'in_store' => false,
                'name' => [
                    'en_US' => 'Drive fee',
                    'nn_NO' => 'Kjøreavgift',
                    'sv_SE' => 'Framkörningsavgift',
                ],
                'description' => [
                    'en_US' => 'Drive fee',
                    'nn_NO' => 'Kjøreavgift',
                    'sv_SE' => 'Framkörningsavgift',
                ],
            ],
            [
                'service_id' => null,
                'category_id' => 2,
                'fortnox_article_id' => 41,
                'price' => 4.8,
                'credit_price' => null,
                'vat_group' => 25,
                'has_rut' => false,
                'in_app' => false,
                'in_store' => false,
                'name' => [
                    'en_US' => 'Material',
                    'nn_NO' => 'Materiale',
                    'sv_SE' => 'Material',
                ],
                'description' => [
                    'en_US' => 'Material that is used',
                    'nn_NO' => 'Materiale som blir brukt',
                    'sv_SE' => 'Material som används',
                ],
            ],
        ];
    }
}
