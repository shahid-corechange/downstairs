<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;

class PropertyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->getTypes() as $type) {
            $propertyType = PropertyType::create();

            $propertyType->translations()->create([
                'key' => 'name',
                ...$type,
            ]);
        }
    }

    private function getTypes()
    {
        return [
            [
                'en_US' => 'House',
                'nn_NO' => 'Hus',
                'sv_SE' => 'Hus',
            ],
            [
                'en_US' => 'Apartment building',
                'nn_NO' => 'Trappeoppgang',
                'sv_SE' => 'Trapp-hus',
            ],
            [
                'en_US' => 'Office',
                'nn_NO' => 'Kontor',
                'sv_SE' => 'Kontor',
            ],
            [
                'en_US' => 'Others',
                'nn_NO' => 'Annet',
                'sv_SE' => 'Övrigt',
            ],
        ];
    }
}
