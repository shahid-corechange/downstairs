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
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
            $table->dropColumn('category_id');
            $table->dropColumn('in_app');
            $table->dropColumn('in_store');
            $table->string('color')->default('#718096')->after('thumbnail_image');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('in_app');
            $table->boolean('in_store');
        });
    }
};
