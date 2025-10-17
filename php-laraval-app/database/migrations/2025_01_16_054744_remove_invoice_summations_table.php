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
        Schema::dropIfExists('invoice_summations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('invoice_summations', function (Blueprint $table) {
            $table->id();
            $table->char('invoice_period', 7)->after('id');
            $table->char('sent_period', 7)->after('invoice_period');
            $table->unsignedDecimal('total_gross', 12, 2)->after('sent_period');
            $table->unsignedDecimal('total_net', 12, 2)->after('total_gross');
            $table->unsignedDecimal('total_vat', 12, 2)->after('total_net');
            $table->unsignedDecimal('total_include_vat', 12, 2)->after('total_vat');
            $table->unsignedDecimal('total_rut', 12, 2)->after('total_include_vat');
            $table->unsignedDecimal('total_invoiced', 12, 2)->after('total_rut');
            $table->unsignedSmallInteger('invoice_count')->after('total_invoiced');
        });
    }
};
