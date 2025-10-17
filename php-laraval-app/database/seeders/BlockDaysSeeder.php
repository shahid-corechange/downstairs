<?php

namespace Database\Seeders;

use App\Models\BlockDay;
use Illuminate\Database\Seeder;

class BlockDaysSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment() !== 'testing') {
            BlockDay::factory(10)->create();
        }
    }
}
