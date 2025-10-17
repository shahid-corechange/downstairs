<?php

namespace Tests\Model;

use App\Models\Service;
use App\Models\ServiceQuarter;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ServiceQuarterTest extends TestCase
{
    /** @test */
    public function serviceQuartersDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('service_quarters', [
                'id',
                'service_id',
                'min_square_meters',
                'max_square_meters',
                'quarters',
                'created_at',
                'updated_at',
            ]),
        );
    }

    /** @test */
    public function serviceQuarterHasHours(): void
    {
        $serviceQuarter = ServiceQuarter::first();

        $this->assertEquals($serviceQuarter->quarters / 4, $serviceQuarter->hours);
    }

    /** @test */
    public function serviceQuarterHasService(): void
    {
        $serviceQuarter = ServiceQuarter::first();

        $this->assertInstanceOf(Service::class, $serviceQuarter->service);
    }
}
