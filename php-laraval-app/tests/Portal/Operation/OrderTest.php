<?php

namespace Tests\Portal\Operation;

use App\DTOs\Order\OrderResponseDTO;
use App\Enums\Product\ProductUnitEnum;
use App\Enums\VatNumbersEnum;
use App\Models\Order;
use Inertia\Testing\AssertableInertia as Assert;
use Session;
use Tests\TestCase;

class OrderTest extends TestCase
{
    public function testAdminCanAccessOrders(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = Order::count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/orders')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Order/Overview/index')
                ->has('orders', $total)
                ->has('orders.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('paidBy')
                    ->has('paidAt')
                    ->has('user', fn (Assert $page) => $page
                        ->has('fullname'))
                    ->has('customer', fn (Assert $page) => $page
                        ->has('membershipType')
                        ->has('address', fn (Assert $page) => $page
                            ->has('fullAddress')
                            ->etc()))
                    ->etc())
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessOrders(): void
    {
        $this->actingAs($this->user)
            ->get('/orders')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterOrders(): void
    {
        $data = Order::first();
        $pageSize = config('downstairs.pageSize');

        $this->actingAs($this->admin)
            ->get("/orders?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Order/Overview/index')
                ->has('orders', 1)
                ->has('orders.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('paidBy', $data->paid_by)
                    ->where('paidAt', $data->paid_at)
                    ->has('user', fn (Assert $page) => $page
                        ->has('fullname'))
                    ->has('customer', fn (Assert $page) => $page
                        ->has('membershipType')
                        ->has('address', fn (Assert $page) => $page
                            ->has('fullAddress')
                            ->etc()))
                    ->etc())
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessOrdersJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/orders/json');
        $keys = array_keys(
            OrderResponseDTO::from(Order::first())->toArray()
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

    public function testAdminCanAccessOrderJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/orders/1/json');
        $keys = array_keys(
            OrderResponseDTO::from(Order::first())->toArray()
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => $keys,
            'meta' => [
                'etag',
            ],
        ]);
    }

    public function testCanCreateOrderRow(): void
    {
        $data = [
            'description' => 'order row description',
            'quantity' => 1,
            'unit' => ProductUnitEnum::Piece(),
            'price' => 100,
            'discountPercentage' => 0,
            'vat' => VatNumbersEnum::TwentyFive(),
            'hasRut' => false,
        ];

        $response = $this->actingAs($this->admin)
            ->post('/orders/1/rows', $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('order row created successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('order_rows', [
            'order_id' => 1,
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'price' => $data['price'] / (1 + $data['vat'] / 100),
            'discount_percentage' => $data['discountPercentage'],
            'vat' => $data['vat'],
            'has_rut' => $data['hasRut'],
        ]);
    }

    public function testCanUpdateOrderRow(): void
    {
        $orderRow = Order::first()->rows()->first();
        $data = [
            'description' => 'updated order row description',
            'quantity' => 2,
            'unit' => ProductUnitEnum::Piece(),
            'price' => 200,
            'discountPercentage' => 0,
            'vat' => VatNumbersEnum::TwentyFive(),
            'hasRut' => false,
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/orders/1/rows/{$orderRow->id}", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('order row updated successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('order_rows', [
            'id' => $orderRow->id,
            'order_id' => 1,
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'price' => $data['price'] / (1 + $data['vat'] / 100),
            'discount_percentage' => $data['discountPercentage'],
            'vat' => $data['vat'],
            'has_rut' => $data['hasRut'],
        ]);
    }

    public function testCanNotUpdateOrderRowIfNotFound(): void
    {
        $data = [
            'description' => 'updated order row description',
            'quantity' => 2,
            'unit' => ProductUnitEnum::Piece(),
            'price' => 200,
            'discountPercentage' => 0,
            'vat' => VatNumbersEnum::TwentyFive(),
            'hasRut' => false,
        ];

        $response = $this->actingAs($this->admin)
            ->patch('/orders/1/rows/999', $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('order row not found'),
            Session::get('error')
        );
    }

    public function testCanDeleteOrderRow(): void
    {
        $orderRow = Order::first()->rows()->first();

        $response = $this->actingAs($this->admin)
            ->delete("/orders/1/rows/{$orderRow->id}");

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('order row deleted successfully'),
            Session::get('success')
        );

        $this->assertDatabaseMissing('order_rows', [
            'id' => $orderRow->id,
        ]);
    }

    public function testCanNotDeleteOrderRowIfNotFound(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete('/orders/1/rows/999');

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('order row not found'),
            Session::get('error')
        );
    }
}
