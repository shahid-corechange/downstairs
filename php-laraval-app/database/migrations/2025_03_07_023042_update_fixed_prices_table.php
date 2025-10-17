<?php

use App\Enums\FixedPrice\FixedPriceTypeEnum;
use App\Models\FixedPrice;
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
        Schema::table('fixed_prices', function (Blueprint $table) {
            $table->string('type')->default(FixedPriceTypeEnum::Cleaning())->after('user_id');
        });

        FixedPrice::withTrashed()
            ->whereMeta('include_laundry', true)
            ->update([
                'type' => FixedPriceTypeEnum::CleaningAndLaundry(),
            ]);
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
