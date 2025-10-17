<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Service;
use Illuminate\Database\Seeder;

class AddFortnoxArticleIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment() !== 'testing') {
            // update multiple services
            foreach ($this->services() as $service) {
                Service::find($service['id'])->update([
                    'fortnox_article_id' => $service['fortnox_article_id'],
                ]);
            }

            // update transport
            Product::find(config('downstairs.products.transport.id'))->update([
                'fortnox_article_id' => '83',
            ]);

            // update material
            Product::find(config('downstairs.products.material.id'))->update([
                'fortnox_article_id' => '84',
            ]);
        }
    }

    public function services()
    {
        return [
            ['id' => 1, 'fortnox_article_id' => '72'],
            ['id' => 2, 'fortnox_article_id' => '73'],
            ['id' => 3, 'fortnox_article_id' => '74'],
            ['id' => 4, 'fortnox_article_id' => '75'],
        ];
    }
}
