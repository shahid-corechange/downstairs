<?php

namespace Tests\Model;

use App\Models\CustomTask;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    /** @test */
    public function servicesDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('services', [
                'id',
                'fortnox_article_id',
                'type',
                'price',
                'vat_group',
                'has_rut',
                'thumbnail_image',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    // /** @test */
    // public function serviceHasName(): void
    // {
    //     $service = Service::first();

    //     $this->assertIsString($service->name);
    // }

    // /** @test */
    // public function serviceCanSetName(): void
    // {
    //     $service = Service::first();
    //     $service->setName('test');

    //     if ($service) {
    //         $this->assertIsString($service->name);
    //         $this->assertEquals('test', $service->name);
    //     } else {
    //         $this->assertNull($service);
    //     }
    // }

    // /** @test */
    // public function serviceCanSetNameDefault(): void
    // {
    //     $service = Service::first();
    //     $service->nameDefault('test');

    //     app()->setLocale('sv_SE');
    //     $this->assertEquals('test', $service->name);

    //     app()->setLocale('en_US');
    //     $this->assertEquals('', $service->name);

    //     app()->setLocale('nn_NO');
    //     $this->assertEquals('', $service->name);
    // }

    // /** @test */
    // public function serviceHasDescription(): void
    // {
    //     $service = Service::first();

    //     if ($service) {
    //         $this->assertIsString($service->description);
    //     } else {
    //         $this->assertNull($service);
    //     }
    // }

    // /** @test */
    // public function serviceCanSetDescription(): void
    // {
    //     $service = Service::first();
    //     $service->setDescription('test');

    //     if ($service) {
    //         $this->assertIsString($service->description);
    //         $this->assertEquals('test', $service->description);
    //     } else {
    //         $this->assertNull($service);
    //     }
    // }

    // /** @test */
    // public function serviceCanSetDescriptionDefault(): void
    // {
    //     $service = Service::first();
    //     $service->descriptionDefault('test');

    //     app()->setLocale('sv_SE');
    //     $this->assertEquals('test', $service->description);

    //     app()->setLocale('en_US');
    //     $this->assertEquals('', $service->description);

    //     app()->setLocale('nn_NO');
    //     $this->assertEquals('', $service->description);
    // }

    // /** @test */
    // public function serviceHasPriceWithVat(): void
    // {
    //     $service = Service::first();

    //     if ($service) {
    //         $this->assertIsFloat($service->price_with_vat);
    //     } else {
    //         $this->assertNull($service);
    //     }
    // }

    // /** @test */
    // public function serviceHasPrducts(): void
    // {
    //     $service = Service::first();

    //     if ($service) {
    //         $this->assertIsObject($service->products);
    //         $this->assertInstanceOf(Product::class, $service->products->first());
    //     } else {
    //         $this->assertNull($service);
    //     }
    // }

    // /** @test */
    // public function serviceHasTasks(): void
    // {
    //     $service = Service::first();

    //     if ($service) {
    //         $this->assertIsObject($service->tasks);
    //         $this->assertInstanceOf(CustomTask::class, $service->tasks->first());
    //     } else {
    //         $this->assertNull($service);
    //     }
    // }

    // /** @test */
    // public function serviceHasTranslations(): void
    // {
    //     $service = Service::first();

    //     if ($service) {
    //         $this->assertIsObject($service->translations);
    //     } else {
    //         $this->assertNull($service);
    //     }
    // }
}
