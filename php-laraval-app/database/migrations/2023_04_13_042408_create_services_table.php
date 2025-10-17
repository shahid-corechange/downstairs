<?php

use App\Enums\VatNumbersEnum;
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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('fortnox_article_id')->nullable();
            $table->string('type');
            $table->unsignedDecimal('price');
            $table->unsignedTinyInteger('vat_group')->default(VatNumbersEnum::TwentyFive());
            $table->boolean('has_rut');
            $table->text('thumbnail_image')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
