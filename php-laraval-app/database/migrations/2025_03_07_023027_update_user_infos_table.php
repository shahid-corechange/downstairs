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
        Schema::table('user_infos', function (Blueprint $table) {
            $table->string('language')->default('sv_SE')->change();
            $table->string('timezone')->default('Europe/Stockholm')->change();
            $table->string('currency')->default('SEK')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_infos', function (Blueprint $table) {
            $table->string('language')->default(null)->change();
            $table->string('timezone')->default(null)->change();
            $table->string('currency')->default(null)->change();
        });
    }
};
