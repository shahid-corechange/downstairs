<?php

namespace Tests\Model;

use App\Models\CustomTask;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductTest extends TestCase
{
    /** @test */
    public function productsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('products', [
                'id',
                'fortnox_article_id',
                'unit',
                'price',
                'credit_price',
                'vat_group',
                'has_rut',
                'thumbnail_image',
                'color',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    // /** @test */
    // public function productHasName(): void
    // {
    //     $product = Product::first();

    //     if ($product) {
    //         $this->assertIsString($product->name);
    //     } else {
    //         $this->assertNull($product);
    //     }
    // }

    // /** @test */
    // public function productCanSetName(): void
    // {
    //     $product = Product::first();
    //     $product->setName('test');

    //     if ($product) {
    //         $this->assertIsString($product->name);
    //         $this->assertEquals('test', $product->name);
    //     } else {
    //         $this->assertNull($product);
    //     }
    // }

    // /** @test */
    // public function productCanSetNameDefault(): void
    // {
    //     $product = Product::first();
    //     $product->nameDefault('test');

    //     app()->setLocale('sv_SE');
    //     $this->assertEquals('test', $product->name);

    //     app()->setLocale('en_US');
    //     $this->assertEquals('', $product->name);

    //     app()->setLocale('nn_NO');
    //     $this->assertEquals('', $product->name);
    // }

    // /** @test */
    // public function productHasDescription(): void
    // {
    //     $product = Product::first();

    //     if ($product) {
    //         $this->assertIsString($product->description);
    //     } else {
    //         $this->assertNull($product);
    //     }
    // }

    // /** @test */
    // public function productCanSetDescription(): void
    // {
    //     $product = Product::first();
    //     $product->setDescription('test');

    //     if ($product) {
    //         $this->assertIsString($product->description);
    //         $this->assertEquals('test', $product->description);
    //     } else {
    //         $this->assertNull($product);
    //     }
    // }

    // /** @test */
    // public function productCanSetDescriptionDefault(): void
    // {
    //     $product = Product::first();
    //     $product->descriptionDefault('test');

    //     app()->setLocale('sv_SE');
    //     $this->assertEquals('test', $product->description);

    //     app()->setLocale('en_US');
    //     $this->assertEquals('', $product->description);

    //     app()->setLocale('nn_NO');
    //     $this->assertEquals('', $product->description);
    // }

    // /** @test */
    // public function productHasPriceWithVat(): void
    // {
    //     $product = Product::first();

    //     if ($product) {
    //         $this->assertIsFloat($product->price_with_vat);
    //     } else {
    //         $this->assertNull($product);
    //     }
    // }

    // /** @test */
    // public function productHasService(): void
    // {
    //     $product = Product::first();

    //     if ($product) {
    //         $this->assertInstanceOf(Service::class, $product->service);
    //     } else {
    //         $this->assertNull($product);
    //     }
    // }

    // /** @test */
    // public function productHasCategory(): void
    // {
    //     $product = Product::first();

    //     if ($product) {
    //         $this->assertInstanceOf(ProductCategory::class, $product->category);
    //     } else {
    //         $this->assertNull($product);
    //     }
    // }

    // /** @test */
    // public function productHasTasks(): void
    // {
    //     $product = Product::first();

    //     if ($product) {
    //         $this->assertIsObject($product->tasks);
    //         $this->assertInstanceOf(CustomTask::class, $product->tasks->first());
    //     } else {
    //         $this->assertNull($product);
    //     }
    // }

    // /** @test */
    // public function productHasTranslations(): void
    // {
    //     $product = Product::first();

    //     if ($product) {
    //         $this->assertIsObject($product->translations);
    //     } else {
    //         $this->assertNull($product);
    //     }
    // }
}
