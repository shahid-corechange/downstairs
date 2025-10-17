<?php

use App\Enums\ScheduleCleaning\CleaningItemPaymentMethodEnum;
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
        Schema::create('schedule_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->morphs('itemable');
            $table->unsignedDecimal('price');
            $table->unsignedDecimal('quantity')->default(1);
            $table->unsignedTinyInteger('discount_percentage')->default(0);
            $table->string('payment_method')
                ->default(CleaningItemPaymentMethodEnum::Invoice());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_items');
    }
};
