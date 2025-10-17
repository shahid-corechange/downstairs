<?php

namespace App\Events;

use App\DTOs\Property\GeocodeResponseDTO;
use App\Models\Property;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GeocodeObtained
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $geocode;

    public $property;

    /**
     * Create a new event instance.
     */
    public function __construct(GeocodeResponseDTO $geocode, Property $property)
    {
        $this->geocode = $geocode;
        $this->property = $property;
    }
}
