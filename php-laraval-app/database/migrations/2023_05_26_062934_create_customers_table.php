<?php

use App\Enums\MembershipTypeEnum;
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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->text('fortnox_id')->nullable();
            $table->unsignedBigInteger('address_id');
            $table->string('membership_type')->default(MembershipTypeEnum::Private());
            $table->string('type')->default('primary');
            $table->text('identity_number');
            $table->string('name');
            $table->string('email');
            $table->string('phone1')->nullable();
            $table->string('dial_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
