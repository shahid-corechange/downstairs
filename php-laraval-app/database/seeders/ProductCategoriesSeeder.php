<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->getNames() as $value) {
            $category = ProductCategory::create([]);

            $category->translations()->create([
                'key' => 'name',
                ...$value['name'],
            ]);
        }
    }

    private function getNames(): array
    {
        return [
            [
                'name' => [
                    'en_US' => 'Add On',
                    'nn_NO' => 'Tillegg',
                    'sv_SE' => 'Tillägg',
                ],
            ],
            [
                'name' => [
                    'en_US' => 'Utility',
                    'nn_NO' => 'Nytte',
                    'sv_SE' => 'Verktyg',
                ],
            ],
        ];
    }
}
