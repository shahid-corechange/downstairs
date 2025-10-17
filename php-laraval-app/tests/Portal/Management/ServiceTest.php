<?php

namespace Tests\Portal\Management;

use App\Contracts\StorageService;
use App\Enums\MembershipTypeEnum;
use App\Enums\Service\ServiceTypeEnum;
use App\Enums\TranslationEnum;
use App\Enums\VatNumbersEnum;
use App\Jobs\CreateServiceArticleJob;
use App\Jobs\UpdateServiceArticleJob;
use App\Models\CustomTask;
use App\Models\Service;
use App\Models\Translation;
use Bus;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;
use Session;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    public function testAdminCanAccessServices(): void
    {
        $service = Service::all();

        $this->actingAs($this->admin)
            ->get('/services')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Service/Overview/index')
                ->has('services', $service->count())
                ->has('services.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('type')
                    ->has('name')
                    ->has('vatGroup')
                    ->has('hasRut')
                    ->has('thumbnailImage')
                    ->has('priceWithVat')
                    ->etc()
                    ->has('tasks.0', fn (Assert $page) => $page
                        ->has('id')
                        ->has('name')
                        ->has('description')
                        ->etc())));
    }

    public function testCustomerCanNotAccessServices(): void
    {
        $this->actingAs($this->user)
            ->get('/services')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanCreateService(): void
    {
        $this->mock(StorageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->never();
        });

        $data = [
            'name' => 'Service 1',
            'type' => ServiceTypeEnum::Cleaning(),
            'membership_type' => MembershipTypeEnum::Private(),
            'description' => 'Service 1 description',
            'price' => 100,
            'vatGroup' => VatNumbersEnum::TwentyFive(),
            'hasRut' => true,
            'tasks' => [
                [
                    'nameSvSe' => 'testserviceuppgift',
                    'descriptionSvSe' => 'testserviceuppgift beskrivning',
                    'nameEnUs' => 'test service task',
                    'descriptionEnUs' => 'test service task description',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post('/services', $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('service created successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('services', [
            'type' => $data['type'],
            'price' => $data['price'] / (1 + $data['vatGroup'] / 100),
            'vat_group' => $data['vatGroup'],
            'has_rut' => $data['hasRut'],
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => Service::class,
            'key' => 'name',
            'sv_SE' => $data['name'],
            'nn_NO' => '',
            'en_US' => '',
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => Service::class,
            'key' => 'description',
            'sv_SE' => $data['description'],
            'nn_NO' => '',
            'en_US' => '',
        ]);

        $service = Service::latest()->first();
        $this->assertDatabaseHas('custom_tasks', [
            'taskable_type' => Service::class,
            'taskable_id' => $service->id,
        ]);

        $task = $service->tasks()->first();

        $this->assertDatabaseHas('translations', [
            'translationable_type' => CustomTask::class,
            'translationable_id' => $task->id,
            'key' => 'name',
            'sv_SE' => $data['tasks'][0]['nameSvSe'],
            'nn_NO' => null,
            'en_US' => $data['tasks'][0]['nameEnUs'],
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => CustomTask::class,
            'translationable_id' => $task->id,
            'key' => 'description',
            'sv_SE' => $data['tasks'][0]['descriptionSvSe'],
            'nn_NO' => null,
            'en_US' => $data['tasks'][0]['descriptionEnUs'],
        ]);

        Bus::assertDispatchedAfterResponse(CreateServiceArticleJob::class);
    }

    public function testCanUpdateService(): void
    {
        $this->mock(StorageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->never();
        });

        $data = [
            'name' => 'Service 1',
            'type' => ServiceTypeEnum::Cleaning(),
            'membership_type' => MembershipTypeEnum::Private(),
            'description' => 'Service 1 description',
            'price' => 100,
            'vatGroup' => VatNumbersEnum::TwentyFive(),
            'hasRut' => true,
        ];
        $serviceId = 1;

        $response = $this->actingAs($this->admin)
            ->patch("/services/{$serviceId}", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('service updated successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('services', [
            'id' => $serviceId,
            'type' => $data['type'],
            'price' => $data['price'] / (1 + $data['vatGroup'] / 100),
            'vat_group' => $data['vatGroup'],
            'has_rut' => $data['hasRut'],
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => Service::class,
            'translationable_id' => $serviceId,
            'key' => 'name',
            'sv_SE' => $data['name'],
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => Service::class,
            'translationable_id' => $serviceId,
            'key' => 'description',
            'sv_SE' => $data['description'],
        ]);

        Bus::assertDispatchedAfterResponse(UpdateServiceArticleJob::class);
    }

    public function testCanDeleteService(): void
    {
        $serviceId = 1;
        $service = Service::find($serviceId);
        $service->products()->forceDelete();
        $service->subscriptions()
            ->withTrashed()
            ->where(function (Builder $query) {
                $query->whereHas('scheduleCleanings', function ($query) {
                    $query->active();
                })
                    ->orWhereNull('deleted_at');
            })
            ->forceDelete();

        $response = $this->actingAs($this->admin)
            ->delete("/services/{$serviceId}");

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('service deleted successfully'),
            Session::get('success')
        );

        $this->assertSoftDeleted('services', [
            'id' => $serviceId,
        ]);
    }

    public function testCanNotDeleteServiceIfHasProducts(): void
    {
        $serviceId = 1;

        $response = $this->actingAs($this->admin)
            ->delete("/services/{$serviceId}");

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('service has products'),
            Session::get('error')
        );
    }

    public function testCanNotDeleteServiceIfHasSchedulesOrSubscriptions(): void
    {
        $serviceId = 1;
        $service = Service::find($serviceId);
        $service->products()->forceDelete();

        $response = $this->actingAs($this->admin)
            ->delete("/services/{$serviceId}");

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('service has active schedules or subscriptions'),
            Session::get('error')
        );
    }

    public function testCanRestoreService(): void
    {
        $service = Service::first();
        $service->delete();

        $response = $this->actingAs($this->admin)
            ->post("/services/{$service->id}/restore");

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('service restored successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'deleted_at' => null,
        ]);
    }

    public function testCanUpdateServiceTranslation(): void
    {
        $serviceId = 1;
        $serviceName = Translation::where('key', 'name')
            ->where('translationable_type', Service::class)
            ->Where('translationable_id', $serviceId)
            ->first();
        $serviceDescription = Translation::where('key', 'description')
            ->where('translationable_type', Service::class)
            ->Where('translationable_id', $serviceId)
            ->first();
        $newName = 'Service 1';
        $newDescription = 'Service 1 description';
        $data = [
            'language' => 'en_US',
            'translations' => [
                'name' => [
                    'id' => $serviceName->id,
                    'value' => $newName,
                ],
                'description' => [
                    'id' => $serviceDescription->id,
                    'value' => $newDescription,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/services/{$serviceId}/translations", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('translation updated successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('translations', [
            'translationable_type' => Service::class,
            'translationable_id' => $serviceId,
            'key' => 'name',
            'en_US' => $newName,
            'nn_NO' => $serviceName->nn_NO,
            'sv_SE' => $serviceName->sv_SE,
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => Service::class,
            'translationable_id' => $serviceId,
            'key' => 'description',
            'en_US' => $newDescription,
            'nn_NO' => $serviceDescription->nn_NO,
            'sv_SE' => $serviceDescription->sv_SE,
        ]);
    }

    public function testCanCreateServiceTask(): void
    {
        $service = Service::find(1);
        $service->tasks()->forceDelete();
        $data = [
            'nameSvSe' => 'Service uppgift 1',
            'descriptionSvSe' => 'Service uppgift 1 beskrivning',
            'nameEnUs' => 'Service task 1',
            'descriptionEnUs' => 'Service task 1 description',
        ];

        $response = $this->actingAs($this->admin)
            ->post("/services/{$service->id}/tasks", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('task created successfully'),
            Session::get('success')
        );

        $task = $service->tasks()->first();
        $this->assertDatabaseHas('custom_tasks', [
            'taskable_type' => Service::class,
            'taskable_id' => $service->id,
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => CustomTask::class,
            'translationable_id' => $task->id,
            'key' => 'name',
            'sv_SE' => $data['nameSvSe'],
            'nn_NO' => null,
            'en_US' => $data['nameEnUs'],
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => CustomTask::class,
            'translationable_id' => $task->id,
            'key' => 'description',
            'sv_SE' => $data['descriptionSvSe'],
            'nn_NO' => null,
            'en_US' => $data['descriptionEnUs'],
        ]);
    }

    public function testCanUpdateServiceTask(): void
    {
        $service = Service::first();
        $service->tasks()->delete();

        $task = $service->tasks()->create([]);
        $task->setName('', TranslationEnum::Swedish());
        $task->setDescription('', TranslationEnum::Swedish());
        $task->setName('', TranslationEnum::English());
        $task->setDescription('', TranslationEnum::English());

        $data = [
            'nameSvSe' => 'Service uppgift 1',
            'descriptionSvSe' => 'Service uppgift 1 beskrivning',
            'nameEnUs' => 'Service task 1',
            'descriptionEnUs' => 'Service task 1 description',
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/services/{$service->id}/tasks/{$task->id}", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('task updated successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('custom_tasks', [
            'id' => $task->id,
            'taskable_type' => Service::class,
            'taskable_id' => $service->id,
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => CustomTask::class,
            'translationable_id' => $task->id,
            'key' => 'name',
            'sv_SE' => $data['nameSvSe'],
            'nn_NO' => null,
            'en_US' => $data['nameEnUs'],
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => CustomTask::class,
            'translationable_id' => $task->id,
            'key' => 'description',
            'sv_SE' => $data['descriptionSvSe'],
            'nn_NO' => null,
            'en_US' => $data['descriptionEnUs'],
        ]);
    }

    public function testCanNotUpdateServiceTaskIfNotFound(): void
    {
        $service = Service::first();

        $data = [
            'nameSvSe' => 'Service uppgift 1',
            'descriptionSvSe' => 'Service uppgift 1 beskrivning',
            'nameEnUs' => 'Service task 1',
            'descriptionEnUs' => 'Service task 1 description',
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/services/{$service->id}/tasks/1000", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('task not found'),
            Session::get('error')
        );
    }

    public function testCanDeleteServiceTask(): void
    {
        $service = Service::first();
        $service->tasks()->delete();

        $task = $service->tasks()->create([]);
        $task->setName('', TranslationEnum::Swedish());
        $task->setDescription('', TranslationEnum::Swedish());
        $task->setName('', TranslationEnum::English());
        $task->setDescription('', TranslationEnum::English());

        $response = $this->actingAs($this->admin)
            ->delete("/services/{$service->id}/tasks/{$task->id}");

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('task deleted successfully'),
            Session::get('success')
        );

        $this->assertDatabaseMissing('custom_tasks', [
            'id' => $task->id,
        ]);

        $this->assertDatabaseMissing('translations', [
            'translationable_type' => CustomTask::class,
            'translationable_id' => $task->id,
        ]);
    }

    public function testCanNotDeleteServiceTaskIfNotFound(): void
    {
        $service = Service::first();

        $response = $this->actingAs($this->admin)
            ->delete("/services/{$service->id}/tasks/1000");

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('task not found'),
            Session::get('error')
        );
    }
}
