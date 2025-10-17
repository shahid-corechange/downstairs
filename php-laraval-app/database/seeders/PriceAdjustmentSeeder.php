<?php

namespace Database\Seeders;

use App\Enums\PriceAdjustment\PriceAdjustmentPriceTypeEnum;
use App\Enums\PriceAdjustment\PriceAdjustmentRowStatusEnum;
use App\Enums\PriceAdjustment\PriceAdjustmentStatusEnum;
use App\Enums\PriceAdjustment\PriceAdjustmentTypeEnum;
use App\Models\PriceAdjustment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class PriceAdjustmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment() !== 'testing') {
            self::createPriceAdjustment();
        }
    }

    public static function createPriceAdjustment(): void
    {
        $serviceAdjustment = PriceAdjustment::create([
            'causer_id' => User::role('Superadmin')->first()->id,
            'type' => PriceAdjustmentTypeEnum::Service(),
            'description' => 'Service price adjustment',
            'price_type' => PriceAdjustmentPriceTypeEnum::FixedPriceWithVat(),
            'price' => 100.00,
            'execution_date' => now(),
            'status' => PriceAdjustmentStatusEnum::Pending(),
        ]);

        $serviceAdjustment->rows()->create([
            'adjustable_type' => Service::class,
            'adjustable_id' => Service::first()->id,
            'previous_price' => 200.00,
            'price' => 100.00,
            'status' => PriceAdjustmentRowStatusEnum::Pending(),
        ]);
    }
}
