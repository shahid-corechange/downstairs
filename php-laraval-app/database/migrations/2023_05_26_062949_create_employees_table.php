<?php

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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->text('fortnox_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('address_id');
            $table->text('identity_number');
            $table->string('name');
            $table->string('email');
            $table->string('phone1')->nullable();
            $table->string('dial_code')->nullable();
            $table->boolean('is_valid_identity')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
