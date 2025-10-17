<?php

use App\Enums\MembershipTypeEnum;
use App\Enums\Property\PropertyStatusEnum;
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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('address_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_type_id')->constrained()->cascadeOnDelete();
            $table->string('membership_type')->default(MembershipTypeEnum::Private());
            $table->decimal('square_meter');
            $table->string('status')->default(PropertyStatusEnum::Active());
            $table->json('key_information')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
