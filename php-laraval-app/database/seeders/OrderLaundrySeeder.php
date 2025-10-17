<?php

namespace Database\Seeders;

use App\Enums\Invoice\InvoiceTypeEnum;
use App\Enums\MembershipTypeEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\Product\ProductUnitEnum;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\OldOrder;
use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderLaundrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment() !== 'testing') {
            $numberOfInstances = 20;

            for ($i = 0; $i < $numberOfInstances; $i++) {
                $this->createPrivateOrder();
                $this->createCompanyOrder();
            }
        }
    }

    private function createPrivateOrder()
    {
        $date = now();
        $customer = Customer::where('membership_type', MembershipTypeEnum::Private())
            ->inRandomOrder()->first();
        $user = $customer->users()->first();
        $type = Invoice::getUserType(
            $user->id,
            $customer->membership_type,
            InvoiceTypeEnum::Laundry()
        );
        $invoice = Invoice::findOrCreate(
            $user->id,
            $customer->id,
            $date->month,
            $date->year,
            $type
        );

        $order = Order::create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'service_id' => 2,
            'invoice_id' => $invoice->id,
            'orderable_type' => OldOrder::class,
            'orderable_id' => fake()->numberBetween(1, 100),
            'status' => OrderStatusEnum::Draft(),
            'ordered_at' => $date->subDays(5)->format('Y-m-d H:i:s'),
        ]);

        $this->createOrderRow($order);
    }

    private function createCompanyOrder()
    {
        $date = now();
        $customer = Customer::where('membership_type', MembershipTypeEnum::Company())
            ->inRandomOrder()->first();
        $user = $customer->users()->first();
        $invoice = Invoice::findOrCreate(
            $user->id,
            $customer->id,
            $date->month,
            $date->year,
            InvoiceTypeEnum::CleaningAndLaundry()
        );

        $order = Order::create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'service_id' => 4,
            'invoice_id' => $invoice->id,
            'orderable_type' => OldOrder::class,
            'orderable_id' => fake()->numberBetween(1, 100),
            'status' => OrderStatusEnum::Draft(),
            'ordered_at' => $date->subDays(5)->format('Y-m-d H:i:s'),
        ]);

        $this->createOrderRow($order);
    }

    private function createOrderRow(Order $order)
    {
        $order->rows()->createMany([
            [
                'description' => 'T-shirt',
                'quantity' => 1,
                'unit' => ProductUnitEnum::Piece(),
                'price' => 100,
                'discount_percentage' => 0,
                'vat' => 25,
                'has_rut' => $order->service_id === 2,
            ],
            [
                'description' => 'Jeans',
                'quantity' => 1,
                'unit' => ProductUnitEnum::Piece(),
                'price' => 120,
                'discount_percentage' => 0,
                'vat' => 25,
                'has_rut' => $order->service_id === 2,
            ],
            [
                'description' => 'Socks',
                'quantity' => 1,
                'unit' => ProductUnitEnum::Piece(),
                'price' => 20,
                'discount_percentage' => 0,
                'vat' => 25,
                'has_rut' => $order->service_id === 2,
            ],
        ]);
    }
}
