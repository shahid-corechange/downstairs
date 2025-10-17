<?php

use App\Enums\ScheduleCleaning\CleaningProductPaymentMethodEnum;
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
        Schema::table('schedule_cleaning_products', function (Blueprint $table) {
            $table->string('payment_method')
                ->after('discount_percentage')
                ->default(CleaningProductPaymentMethodEnum::Invoice());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_cleaning_products', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};
