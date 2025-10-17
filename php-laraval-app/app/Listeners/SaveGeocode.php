<?php

namespace App\Listeners;

use App\Events\GeocodeObtained;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SaveGeocode implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Handle the event.
     */
    public function handle(GeocodeObtained $event): void
    {
        $event->property->address->fill([
            'latitude' => $event->geocode->latitude,
            'longitude' => $event->geocode->longitude,
        ])->save();
    }
}
