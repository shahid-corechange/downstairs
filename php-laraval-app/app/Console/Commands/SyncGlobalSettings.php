<?php

namespace App\Console\Commands;

use App\Models\GlobalSetting;
use DB;
use Illuminate\Console\Command;
use Str;

class SyncGlobalSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'global-settings:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync global settings to the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dbSettings = GlobalSetting::all();
        $configSettings = collect(config('downstairs.globalSettings'));

        if ($dbSettings->count() === $configSettings->count()) {
            return;
        }

        $removedSettings = $dbSettings->diffUsing($configSettings, function ($item1, $item2) {
            $value1 = is_array($item1) ? strtoupper(Str::snake($item1['key'])) : $item1->key;
            $value2 = is_array($item2) ? strtoupper(Str::snake($item2['key'])) : $item2->key;

            return $value1 <=> $value2;
        });
        $addedSettings = $configSettings->diffUsing($dbSettings, function ($item1, $item2) {
            $value1 = is_array($item1) ? strtoupper(Str::snake($item1['key'])) : $item1->key;
            $value2 = is_array($item2) ? strtoupper(Str::snake($item2['key'])) : $item2->key;

            return $value1 <=> $value2;
        });

        if ($removedSettings->count() > 0) {
            foreach ($removedSettings as $setting) {
                DB::transaction(function () use ($setting) {
                    $setting->delete();
                    $setting->translations()->forceDelete();
                });
            }
        }

        if ($addedSettings->count() > 0) {
            foreach ($addedSettings as $item) {
                DB::transaction(function () use ($item) {
                    $setting = GlobalSetting::create([
                        'key' => strtoupper(Str::snake($item['key'])),
                        'value' => $item['value'],
                        'type' => $item['type'],
                    ]);
                    $setting->translations()->create([
                        'key' => 'description',
                        ...$item['description'],
                    ]);
                });
            }
        }
    }
}
