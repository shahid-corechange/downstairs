<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->getCategories() as $value) {
            $category = Category::create([
                'thumbnail_image' => 'https://storagestagingdownstairs.blob.core.windows.net/images/'.
                    $value['thumbnail_image'],
            ]);

            $category->translations()->createMany([
                [
                    'key' => 'name',
                    ...$value['name'],
                ],
                [
                    'key' => 'description',
                    'en_US' => fake()->paragraph,
                    'nn_NO' => fake()->paragraph,
                    'sv_SE' => fake()->paragraph,
                ],
            ]);
        }
    }

    private function getCategories(): array
    {
        return [
            [
                'thumbnail_image' => 'garden-cleaning.png',
                'name' => [
                    'en_US' => 'Laundry',
                    'nn_NO' => 'Vask',
                    'sv_SE' => 'Tvätt',
                ],
            ],
            [
                'thumbnail_image' => 'window-cleaning.png',
                'name' => [
                    'en_US' => 'Dry Cleaning',
                    'nn_NO' => 'Renseri',
                    'sv_SE' => 'Torkning',
                ],
            ],
            [
                'thumbnail_image' => 'window-cleaning.png',
                'name' => [
                    'en_US' => 'Regular Laundry',
                    'nn_NO' => 'Vanlig Vask',
                    'sv_SE' => 'Vanlig Tvätt',
                ],
            ],
        ];
    }
}
