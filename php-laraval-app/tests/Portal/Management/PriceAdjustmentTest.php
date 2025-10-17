<?php

namespace Tests\Portal\Management;

use App\DTOs\PriceAdjustment\PriceAdjustmentResponseDTO;
use App\Enums\PriceAdjustment\PriceAdjustmentPriceTypeEnum;
use App\Enums\PriceAdjustment\PriceAdjustmentTypeEnum;
use App\Models\PriceAdjustment;
use App\Models\Service;
use Database\Seeders\PriceAdjustmentSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PriceAdjustmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        PriceAdjustmentSeeder::createPriceAdjustment();
    }

    public function testAdminCanAccessPriceAdjustments(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = PriceAdjustment::count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/price-adjustments')
            ->assertInertia(fn (Assert $page) => $page
                ->component('PriceAdjustment/Overview/index')
                ->has('priceAdjustments', $total)
                ->has('priceAdjustments.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('type')
                    ->has('status')
                    ->has('description')
                    ->has('priceType')
                    ->has('price')
                    ->has('executionDate')
                    ->has('rows.0', fn (Assert $page) => $page
                        ->has('id')
                        ->has('adjustableId')
                        ->has('previousPrice')
                        ->has('adjustableName')
                        ->has('priceWithVat')
                        ->has('status'))
                    ->has('causer', fn (Assert $page) => $page
                        ->has('fullname'))
                    ->etc())
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessPriceAdjustments(): void
    {
        $this->actingAs($this->user)
            ->get('/price-adjustments')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterPriceAdjustments(): void
    {
        $data = PriceAdjustment::first();
        $pageSize = config('downstairs.pageSize');

        $this->actingAs($this->admin)
            ->get("/price-adjustments?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('PriceAdjustment/Overview/index')
                ->has('priceAdjustments', 1)
                ->has('priceAdjustments.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('type')
                    ->has('status')
                    ->has('description')
                    ->has('priceType')
                    ->has('price')
                    ->has('executionDate')
                    ->has('rows.0', fn (Assert $page) => $page
                        ->has('id')
                        ->has('adjustableId')
                        ->has('previousPrice')
                        ->has('adjustableName')
                        ->has('priceWithVat')
                        ->has('status'))
                    ->has('causer', fn (Assert $page) => $page
                        ->has('fullname'))
                    ->etc())
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessPriceAdjustmentsJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/price-adjustments/json');
        $keys = array_keys(
            PriceAdjustmentResponseDTO::from(PriceAdjustment::first())->toArray()
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

    public function testCanCreatePriceAdjustment(): void
    {
        $serviceids = Service::limit(3)->get()->pluck('id')->toArray();
        $data = [
            'type' => PriceAdjustmentTypeEnum::Service(),
            'description' => 'test',
            'priceType' => PriceAdjustmentPriceTypeEnum::FixedPriceWithVat(),
            'price' => 100.0,
            'executionDate' => now()->addDay()->format('Y-m-d'),
            'rowIds' => $serviceids,
        ];

        $this->actingAs($this->admin)
            ->post('/price-adjustments', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('price adjustment created successfully'));

        $this->assertDatabaseHas('price_adjustments', [
            'type' => $data['type'],
            'description' => $data['description'],
            'price_type' => $data['priceType'],
            'price' => $data['price'],
            'execution_date' => $data['executionDate'],
            'status' => 'pending',
        ]);

        foreach ($data['rowIds'] as $id) {
            $this->assertDatabaseHas('price_adjustment_rows', [
                'adjustable_type' => Service::class,
                'adjustable_id' => $id,
                'status' => 'pending',
            ]);
        }
    }

    public function testCanUpdatePriceAdjustment(): void
    {
        $priceAdjustment = PriceAdjustment::first();
        $data = [
            'type' => PriceAdjustmentTypeEnum::Service(),
            'description' => 'test',
            'priceType' => PriceAdjustmentPriceTypeEnum::FixedPriceWithVat(),
            'price' => 100.0,
            'executionDate' => now()->addDay()->format('Y-m-d'),
            'rowIds' => Service::limit(3)->inRandomOrder()->get()->pluck('id')->toArray(),
        ];

        $this->actingAs($this->admin)
            ->put("/price-adjustments/{$priceAdjustment->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('price adjustment updated successfully'));

        $this->assertDatabaseHas('price_adjustments', [
            'id' => $priceAdjustment->id,
            'type' => $data['type'],
            'description' => $data['description'],
            'price_type' => $data['priceType'],
            'price' => $data['price'],
            'execution_date' => $data['executionDate'],
            'status' => 'pending',
        ]);

        foreach ($data['rowIds'] as $id) {
            $this->assertDatabaseHas('price_adjustment_rows', [
                'adjustable_type' => Service::class,
                'adjustable_id' => $id,
                'status' => 'pending',
            ]);
        }
    }

    public function testCanDeletePriceAdjustment(): void
    {
        $priceAdjustment = PriceAdjustment::first();

        $this->actingAs($this->admin)
            ->delete("/price-adjustments/{$priceAdjustment->id}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('price adjustment deleted successfully'));

        $this->assertSoftDeleted('price_adjustments', [
            'id' => $priceAdjustment->id,
        ]);
    }
}
