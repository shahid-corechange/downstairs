<?php

namespace Tests\Portal\Management;

use App\DTOs\ServiceQuarter\ServiceQuarterResponseDTO;
use App\Models\Service;
use App\Models\ServiceQuarter;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ServiceQuarterTest extends TestCase
{
    public function testAdminCanAccessServiceQuarters(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = ServiceQuarter::count();
        $total = $count > $pageSize ? $pageSize : $count;
        $services = Service::whereNotIn('id', [2, 4])->get();

        $this->actingAs($this->admin)
            ->get('/services/quarters')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Service/Quarter/index')
                ->has('serviceQuarters', $total)
                ->has('serviceQuarters.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('serviceId')
                    ->has('minSquareMeters')
                    ->has('maxSquareMeters')
                    ->has('quarters')
                    ->has('hours')
                    ->etc()
                    ->has('service', fn (Assert $page) => $page
                        ->has('name')
                        ->has('translations')
                        ->etc()))
                ->has('services', $services->count())
                ->has('services.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('name')
                    ->has('type')
                    ->etc())
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessServiceQuarters(): void
    {
        $this->actingAs($this->user)
            ->get('/services/quarters')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterServiceQuarters(): void
    {
        $data = ServiceQuarter::first();
        $pageSize = config('downstairs.pageSize');
        $services = Service::whereNotIn('id', [2, 4])->get();

        $this->actingAs($this->admin)
            ->get("/services/quarters?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Service/Quarter/index')
                ->has('serviceQuarters', 1)
                ->has('serviceQuarters.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('serviceId', $data->service_id)
                    ->where('minSquareMeters', $data->min_square_meters)
                    ->where('maxSquareMeters', $data->max_square_meters)
                    ->where('quarters', $data->quarters)
                    ->etc()
                    ->has('service', fn (Assert $page) => $page
                        ->where('name', $data->service->name)
                        ->etc()))
                ->has('services', $services->count())
                ->has('services.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('name')
                    ->has('type')
                    ->etc())
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessServiceQuartersJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/services/quarters/json');
        $keys = array_keys(
            ServiceQuarterResponseDTO::fromModel(ServiceQuarter::first())->toArray()
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

    public function testCanCreateServiceQuarter(): void
    {
        $service = Service::first();
        $data = [
            'serviceId' => $service->id,
            'minSquareMeters' => 1000,
            'maxSquareMeters' => 1500,
            'quarters' => 3,
        ];

        $this->actingAs($this->admin)
            ->post('/services/quarters', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('service quarter created successfully'));

        $this->assertDatabaseHas('service_quarters', [
            'service_id' => $data['serviceId'],
            'min_square_meters' => $data['minSquareMeters'],
            'max_square_meters' => $data['maxSquareMeters'],
            'quarters' => $data['quarters'],
        ]);
    }

    public function testCanNotCreateServiceQuarterIfOverlap(): void
    {
        $service = Service::first();
        $data = [
            'serviceId' => $service->id,
            'minSquareMeters' => 1,
            'maxSquareMeters' => 2,
            'quarters' => 3,
        ];

        $this->actingAs($this->admin)
            ->post('/services/quarters', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __(
                'service quarter overlap',
                ['action' => __('create action')]
            ));
    }

    public function testCanUpdateServiceQuarter(): void
    {
        $service = Service::first();
        $serviceQuarter = ServiceQuarter::create([
            'service_id' => $service->id,
            'min_square_meters' => 1000,
            'max_square_meters' => 1500,
            'quarters' => 3,
        ]);
        $data = [
            'serviceId' => $service->id,
            'minSquareMeters' => 2000,
            'maxSquareMeters' => 2500,
            'quarters' => 4,
        ];

        $this->actingAs($this->admin)
            ->patch("/services/quarters/{$serviceQuarter->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('service quarter updated successfully'));

        $this->assertDatabaseHas('service_quarters', [
            'id' => $serviceQuarter->id,
            'service_id' => $data['serviceId'],
            'min_square_meters' => $data['minSquareMeters'],
            'max_square_meters' => $data['maxSquareMeters'],
            'quarters' => $data['quarters'],
        ]);
    }

    public function testCanNotUpdateServiceQuarterIfOverlap(): void
    {
        $service = Service::first();
        $serviceQuarter = ServiceQuarter::create([
            'service_id' => $service->id,
            'min_square_meters' => 1000,
            'max_square_meters' => 1500,
            'quarters' => 3,
        ]);
        $data = [
            'serviceId' => $service->id,
            'minSquareMeters' => 1,
            'maxSquareMeters' => 2,
            'quarters' => 3,
        ];

        $this->actingAs($this->admin)
            ->patch("/services/quarters/{$serviceQuarter->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __(
                'service quarter overlap',
                ['action' => __('update action')]
            ));
    }

    public function testCanDeleteServiceQuarter(): void
    {
        $serviceQuarter = ServiceQuarter::first();

        $this->actingAs($this->admin)
            ->delete("/services/quarters/{$serviceQuarter->id}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('service quarter deleted successfully'));

        $this->assertDatabaseMissing('service_quarters', [
            'id' => $serviceQuarter->id,
        ]);
    }
}
