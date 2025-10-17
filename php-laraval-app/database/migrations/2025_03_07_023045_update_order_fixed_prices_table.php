<?php

use App\Enums\FixedPrice\FixedPriceTypeEnum;
use App\Models\FixedPrice;
use App\Models\OrderFixedPrice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_fixed_prices', function (Blueprint $table) {
            $table->string('type')->default(FixedPriceTypeEnum::Cleaning())->after('fixed_price_id');
        });

        $fixedPriceIds = FixedPrice::withTrashed()
            ->where('type', FixedPriceTypeEnum::CleaningAndLaundry())
            ->pluck('id');

        OrderFixedPrice::whereIn('fixed_price_id', $fixedPriceIds)
            ->update(['type' => FixedPriceTypeEnum::CleaningAndLaundry()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fixed_prices', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
