<?php

namespace Database\Seeders;

use App\Models\GlobalSetting;
use Illuminate\Database\Seeder;
use Str;

class GlobalSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (config('downstairs.globalSettings') as $value) {
            $setting = GlobalSetting::create([
                'key' => strtoupper(Str::snake($value['key'])),
                'value' => $value['value'],
                'type' => $value['type'],
            ]);
            $setting->translations()->create([
                'key' => 'description',
                ...$value['description'],
            ]);
        }
    }
}
