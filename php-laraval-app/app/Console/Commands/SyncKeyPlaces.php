<?php

namespace App\Console\Commands;

use App\Models\KeyPlace;
use App\Models\Property;
use Illuminate\Console\Command;

class SyncKeyPlaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keyplaces:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync key places data from property key information';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $properties = Property::all();
        foreach ($properties as $property) {
            if ($property->key_place) {
                KeyPlace::where('id', $property->key_place)
                    ->update(['property_id' => $property->id]);
            }
        }
    }
}
