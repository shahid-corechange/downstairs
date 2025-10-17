<?php

namespace Database\Seeders;

use App\Enums\Product\ProductUnitEnum;
use App\Enums\TranslationEnum;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        foreach ($this->getProducts() as $key => $productData) {
            $credit = fake()->randomElement([1, 2, 3, 4]);

            $product = Product::create([
                'service_id' => $productData['id'],
                'category_id' => 1,
                'unit' => ProductUnitEnum::Piece(),
                'price' => $credit * 122.4,
                'credit_price' => $credit,
                'vat_group' => 25,
                'has_rut' => true,
                'in_app' => true,
                'in_store' => false,
                'color' => $productData['color'],
                'thumbnail_image' => 'https://storagestagingdownstairs.blob.core.windows.net/images/'.
                    $productData['image'],
            ]);

            $product->translations()->createMany([
                [
                    'key' => 'name',
                    ...$this->getNames()[$key],
                ],
                [
                    'key' => 'description',
                    'en_US' => fake()->paragraph,
                    'nn_NO' => fake()->paragraph,
                    'sv_SE' => fake()->paragraph,
                ],
            ]);

            // Color is now a regular attribute, not meta

            for ($i = 0; $i < 2; $i++) {
                $task = fake()->randomElement($this->getTasks());
                $customTask = $product->tasks()->create([]);
                $customTask->setName($task['name_sv_se'], TranslationEnum::Swedish());
                $customTask->setDescription($task['desc_sv_se'], TranslationEnum::Swedish());
                $customTask->setName($task['name_en_us'], TranslationEnum::English());
                $customTask->setDescription($task['desc_en_us'], TranslationEnum::English());
            }
        }
    }

    private function getProducts()
    {
        return [
            ['id' => 3, 'image' => 'laundry-pickup.svg', 'color' => '#4E827A'],
            ['id' => 3, 'image' => 'toilet-paper.svg', 'color' => '#FCA53F'],
            ['id' => 4, 'image' => 'laundry-pickup.svg', 'color' => '#FCA53F'],
            ['id' => 4, 'image' => 'toilet-paper.svg', 'color' => '#F96B3F'],
        ];
    }

    private function getNames(): array
    {
        return [
            [
                'en_US' => 'Garden broom',
                'nn_NO' => 'Hagekost',
                'sv_SE' => 'Trädgårdsborste',
            ],
            [
                'en_US' => 'Watering',
                'nn_NO' => 'Vanning',
                'sv_SE' => 'Vattning',
            ],
            [
                'en_US' => 'Window Scrubber',
                'nn_NO' => 'Vindusvask',
                'sv_SE' => 'Fönstertvätt',
            ],
            [
                'en_US' => 'Window Squeegee',
                'nn_NO' => 'Vindusnal',
                'sv_SE' => 'Fönsterskrapa',
            ],
        ];
    }

    private function getTasks()
    {
        return [
            [
                'name_en_us' => 'standard cleaning',
                'desc_en_us' => 'Removing dirt, dust, stains, and other unwanted substances.',
                'name_sv_se' => 'standardrengöring',
                'desc_sv_se' => 'Ta bort smuts, damm, fläckar och andra oönskade ämnen.',
            ],
            [
                'name_en_us' => 'dusting',
                'desc_en_us' => 'Removing dust from surfaces using a duster or a microfiber cloth.',
                'name_sv_se' => 'dammning',
                'desc_sv_se' => 'Ta bort damm från ytor med en dammvippa eller en mikrofiberduk.',
            ],
            [
                'name_en_us' => 'vacuuming',
                'desc_en_us' => 'Using a vacuum cleaner to remove dirt, '.
                    'debris, and pet hair from carpets, rugs, and floors.',
                'name_sv_se' => 'dammsugning',
                'desc_sv_se' => 'Använda en dammsugare för att ta bort smuts, '.
                    'skräp och hundhår från mattor, mattor och golv.',
            ],
        ];
    }
}
