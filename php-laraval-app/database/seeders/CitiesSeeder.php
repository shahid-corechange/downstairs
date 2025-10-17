<?php

namespace Database\Seeders;

use App\Models\City;
use DB;
use Illuminate\Database\Seeder;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment() === 'testing') {
            City::create([
                'id' => config('downstairs.test.city_id'),
                'country_id' => 217,
                'name' => 'Gothenburg',
            ]);
        } else {
            $sqlFile = storage_path('app/seeders/cities.sql');

            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                DB::unprepared($sql);
            }
        }
    }
}
