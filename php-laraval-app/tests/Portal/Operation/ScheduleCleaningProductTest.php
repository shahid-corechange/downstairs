<?php

namespace Tests\Portal\Operation;

use App\DTOs\ScheduleCleaningProduct\ScheduleCleaningProductResponseDTO;
use App\Models\ScheduleCleaningProduct;
use Tests\TestCase;

class ScheduleCleaningProductTest extends TestCase
{
    public function testAdminCanAccessCleaningProductsJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/schedules/products/json');
        $keys = array_keys(
            ScheduleCleaningProductResponseDTO::from(
                ScheduleCleaningProduct::first()
            )->toArray()
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => $keys,
            ],
            'meta' => [
                'etag',
            ],
        ]);
    }
}
