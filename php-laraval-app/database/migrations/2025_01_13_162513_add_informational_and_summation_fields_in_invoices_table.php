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
        Schema::table('invoices', function (Blueprint $table) {
            $table->text('remark')->nullable()->after('year');
            $table->decimal('total_gross', 12, 2)->default(0)->after('remark');
            $table->decimal('total_net', 12, 2)->default(0)->after('total_gross');
            $table->decimal('total_vat', 12, 2)->default(0)->after('total_net');
            $table->decimal('total_rut', 12, 2)->default(0)->after('total_vat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('remark');
            $table->dropColumn('total_gross');
            $table->dropColumn('total_net');
            $table->dropColumn('total_vat');
            $table->dropColumn('total_rut');
        });
    }
};
