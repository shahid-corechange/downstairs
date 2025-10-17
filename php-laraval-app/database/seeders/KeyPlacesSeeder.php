<?php

namespace Database\Seeders;

use App\Models\KeyPlace;
use Illuminate\Database\Seeder;

class KeyPlacesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $numberOfInstances = app()->environment() !== 'testing' ? 300 : 10;
        KeyPlace::factory($numberOfInstances)->create();
    }
}
