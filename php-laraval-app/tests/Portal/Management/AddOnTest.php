<?php

namespace Tests\Portal\Management;

use App\Contracts\StorageService;
use App\Enums\Product\ProductUnitEnum;
use App\Enums\ScheduleCleaning\CleaningProductPaymentMethodEnum;
use App\Enums\TranslationEnum;
use App\Enums\VatNumbersEnum;
use App\Jobs\CreateProductArticleJob;
use App\Jobs\UpdateProductArticleJob;
use App\Models\CustomTask;
use App\Models\Product;
use App\Models\ScheduleCleaning;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Translation;
use Bus;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;
use Session;
use Tests\TestCase;

class AddOnTest extends TestCase
{
    public function testAdminCanAccessAddOns(): void
    {
        $addons = Product::where('category_id', 1)
            ->get();
        $services = Service::all();

        $this->actingAs($this->admin)
            ->get('/addons')
            ->assertInertia(fn (Assert $page) => $page
                ->component('AddOn/Overview/index')
                ->has('addons', $addons->count())
                ->has('addons.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('name')
                    ->has('unit')
                    ->has('priceWithVat')
                    ->has('creditPrice')
                    ->has('vatGroup')
                    ->has('hasRut')
                    ->has('thumbnailImage')
                    ->etc()
                    ->has('tasks.0', fn (Assert $page) => $page
                        ->has('id')
                        ->has('name')
                        ->has('description')
                        ->etc())
                    ->has('service', fn (Assert $page) => $page
                        ->has('id')
                        ->has('name')
                        ->etc()))
                ->has('services', $services->count())
                ->has('services.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('name')
                    ->etc()));
    }

    public function testCustomerCanNotAccessAddOns(): void
    {
        $this->actingAs($this->user)
            ->get('/addons')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanCreateAddOn(): void
    {
        $this->mock(StorageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->never();
        });

        $data = [
            'serviceId' => 1,
            'name' => 'test',
            'categoryId' => 1,
            'unit' => ProductUnitEnum::Piece(),
            'price' => 100,
            'creditPrice' => 1,
            'vatGroup' => VatNumbersEnum::TwentyFive(),
            'hasRut' => false,
            'inApp' => false,
            'inStore' => false,
            'description' => 'test',
            'thumbnailImage' => 'test',
            'tasks' => [
                [
                    'nameSvSe' => 'Pålägg uppgift 1',
                    'descriptionSvSe' => 'Pålägg uppgift 1 beskrivning',
                    'nameEnUs' => 'Add on task 1',
                    'descriptionEnUs' => 'Add on task 1 description',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post('/addons', $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('add on created successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('products', [
            'service_id' => $data['serviceId'],
            'category_id' => $data['categoryId'],
            'unit' => $data['unit'],
            'price' => $data['price'] / (1 + $data['vatGroup'] / 100),
            'credit_price' => $data['creditPrice'],
            'vat_group' => $data['vatGroup'],
            'has_rut' => $data['hasRut'],
            'in_app' => $data['inApp'],
            'in_store' => $data['inStore'],
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => Product::class,
            'key' => 'name',
            'sv_SE' => $data['name'],
            'nn_NO' => '',
            'en_US' => '',
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => Product::class,
            'key' => 'description',
            'sv_SE' => $data['description'],
            'nn_NO' => '',
            'en_US' => '',
        ]);

        $addOn = Product::latest()->first();
        $this->assertDatabaseHas('custom_tasks', [
            'taskable_type' => Product::class,
            'taskable_id' => $addOn->id,
        ]);

        $task = $addOn->tasks()->first();

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

        Bus::assertDispatchedAfterResponse(CreateProductArticleJob::class);
    }

    public function testCanUpdateAddOn(): void
    {
        $this->mock(StorageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->never();
        });

        $data = [
            'serviceId' => 1,
            'name' => 'test',
            'categoryId' => 1,
            'unit' => ProductUnitEnum::Piece(),
            'price' => 100,
            'creditPrice' => 1,
            'vatGroup' => VatNumbersEnum::TwentyFive(),
            'hasRut' => false,
            'inApp' => false,
            'inStore' => false,
            'description' => 'test',
        ];
        $addOnId = 1;

        $response = $this->actingAs($this->admin)
            ->patch("/addons/{$addOnId}", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('add on updated successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('products', [
            'id' => $addOnId,
            'service_id' => $data['serviceId'],
            'category_id' => $data['categoryId'],
            'unit' => $data['unit'],
            'price' => $data['price'] / (1 + $data['vatGroup'] / 100),
            'credit_price' => $data['creditPrice'],
            'vat_group' => $data['vatGroup'],
            'has_rut' => $data['hasRut'],
            'in_app' => $data['inApp'],
            'in_store' => $data['inStore'],
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => Product::class,
            'translationable_id' => $addOnId,
            'key' => 'name',
            'sv_SE' => $data['name'],
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => Product::class,
            'translationable_id' => $addOnId,
            'key' => 'description',
            'sv_SE' => $data['description'],
        ]);

        Bus::assertDispatchedAfterResponse(UpdateProductArticleJob::class);
    }

    public function testCanDeleteAddOn(): void
    {
        $addOnId = 1;
        ScheduleCleaning::active()
            ->whereHas('products', function ($query) use ($addOnId) {
                $query->where('product_id', $addOnId);
            })
            ->forceDelete();

        Subscription::whereHas(
            'products',
            function ($query) use ($addOnId) {
                $query->where('product_id', $addOnId);
            }
        )
            ->forceDelete();

        $response = $this->actingAs($this->admin)
            ->delete("/addons/{$addOnId}");

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('add on deleted successfully'),
            Session::get('success')
        );

        $this->assertSoftDeleted('products', [
            'id' => $addOnId,
        ]);
    }

    public function testCanNotDeleteAddOnIfUseInActiveSchedules(): void
    {
        $product = Product::first();
        $scheduleCleaning = ScheduleCleaning::future()->first();
        $scheduleCleaning->products()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
            'discount_percentage' => 0,
            'payment_method' => CleaningProductPaymentMethodEnum::Invoice(),
        ]);
        $response = $this->actingAs($this->admin)
            ->delete("/addons/{$product->id}");

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('add on still use in active schedules'),
            Session::get('error')
        );
    }

    public function testCanNotDeleteAddOnIfUseInActiveSubscriptions(): void
    {
        $product = Product::first();
        ScheduleCleaning::active()
            ->whereHas('products', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->forceDelete();
        $subscription = Subscription::active()->first();
        $subscription->products()->create([
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/addons/{$product->id}");

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('add on still use in subscriptions'),
            Session::get('error')
        );
    }

    public function testCanRestoreAddOn(): void
    {
        $addOn = Product::first();
        $addOn->delete();

        $response = $this->actingAs($this->admin)
            ->post("/addons/{$addOn->id}/restore");

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('add on restored successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('products', [
            'id' => $addOn->id,
        ]);
    }

    public function testCanUpdateAddOnTranslation(): void
    {
        $addOnId = 1;
        $addOnName = Translation::where('key', 'name')
            ->where('translationable_type', Product::class)
            ->Where('translationable_id', $addOnId)
            ->first();
        $addOnDescription = Translation::where('key', 'description')
            ->where('translationable_type', Product::class)
            ->Where('translationable_id', $addOnId)
            ->first();
        $newName = 'Service 1';
        $newDescription = 'Service 1 description';

        $data = [
            'language' => 'en_US',
            'translations' => [
                'name' => [
                    'id' => $addOnName->id,
                    'value' => $newName,
                ],
                'description' => [
                    'id' => $addOnDescription->id,
                    'value' => $newDescription,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/addons/{$addOnId}/translations", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertDatabaseHas('translations', [
            'translationable_type' => Product::class,
            'translationable_id' => $addOnId,
            'key' => 'name',
            'en_US' => $newName,
            'nn_NO' => $addOnName->nn_NO,
            'sv_SE' => $addOnName->sv_SE,
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => Product::class,
            'translationable_id' => $addOnId,
            'key' => 'description',
            'en_US' => $newDescription,
            'nn_NO' => $addOnDescription->nn_NO,
            'sv_SE' => $addOnDescription->sv_SE,
        ]);
    }

    public function testCanCreateAddOnTask(): void
    {
        $addOn = Product::find(1);
        $addOn->tasks()->delete();
        $data = [
            'nameSvSe' => 'Pålägg uppgift 1',
            'descriptionSvSe' => 'Pålägg uppgift 1 beskrivning',
            'nameEnUs' => 'Add on task 1',
            'descriptionEnUs' => 'Add on task 1 description',
        ];

        $response = $this->actingAs($this->admin)
            ->post("/addons/{$addOn->id}/tasks", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('task created successfully'),
            Session::get('success')
        );

        $task = $addOn->tasks()->first();
        $this->assertDatabaseHas('custom_tasks', [
            'taskable_type' => Product::class,
            'taskable_id' => $addOn->id,
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

    public function testCanUpdateAddOnTask(): void
    {
        $addOn = Product::first();
        $addOn->tasks()->delete();

        $task = $addOn->tasks()->create([]);
        $task->setName('', TranslationEnum::Swedish());
        $task->setDescription('', TranslationEnum::Swedish());
        $task->setName('', TranslationEnum::English());
        $task->setDescription('', TranslationEnum::English());

        $data = [
            'nameSvSe' => 'Pålägg uppgift 1',
            'descriptionSvSe' => 'Pålägg uppgift 1 beskrivning',
            'nameEnUs' => 'Add on task 1',
            'descriptionEnUs' => 'Add on task 1 description',
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/addons/{$addOn->id}/tasks/{$task->id}", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('task updated successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('custom_tasks', [
            'id' => $task->id,
            'taskable_type' => Product::class,
            'taskable_id' => $addOn->id,
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

    public function testCanNotUpdateAddOnTaskIfNotFound(): void
    {
        $addOn = Product::find(1);
        $data = [
            'nameSvSe' => 'Pålägg uppgift 1',
            'descriptionSvSe' => 'Pålägg uppgift 1 beskrivning',
            'nameEnUs' => 'Add on task 1',
            'descriptionEnUs' => 'Add on task 1 description',
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/addons/{$addOn->id}/tasks/1000", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('task not found'),
            Session::get('error')
        );
    }

    public function testCanDeleteAddOnTask(): void
    {
        $addOn = Product::first();
        $addOn->tasks()->delete();

        $task = $addOn->tasks()->create([]);
        $task->setName('', TranslationEnum::Swedish());
        $task->setDescription('', TranslationEnum::Swedish());
        $task->setName('', TranslationEnum::English());
        $task->setDescription('', TranslationEnum::English());

        $response = $this->actingAs($this->admin)
            ->delete("/addons/{$addOn->id}/tasks/{$task->id}");

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

    public function testCanNotDeleteAddOnTaskIfNotFound(): void
    {
        $addOn = Product::first();

        $response = $this->actingAs($this->admin)
            ->delete("/addons/{$addOn->id}/tasks/1000");

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('task not found'),
            Session::get('error')
        );
    }
}
