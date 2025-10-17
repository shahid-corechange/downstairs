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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('cellphone')->nullable();
            $table->string('dial_code')->nullable();
            $table->timestamp('cellphone_verified_at')->nullable();
            $table->text('identity_number')->nullable();
            $table->timestamp('identity_number_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamp('last_seen')->nullable();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
