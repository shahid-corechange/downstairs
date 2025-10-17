<?php

use App\Enums\Invoice\InvoiceStatusEnum;
use App\Enums\Invoice\InvoiceTypeEnum;
use App\Enums\MembershipTypeEnum;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update type to invoices table
     * Company membership type customer will have type Cleaning And Laundry
     * Private membership type customer will have type Cleaning
     */
    public function up(): void
    {
        DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->select('*')
            ->whereNull('invoices.type')
            ->where('customers.membership_type', '=', MembershipTypeEnum::Company())
            ->where('status', '=', InvoiceStatusEnum::Open())
            ->update([
                'invoices.type' => InvoiceTypeEnum::CleaningAndLaundry(),
            ]);

        DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->select('*')
            ->whereNull('invoices.type')
            ->where('customers.membership_type', '=', MembershipTypeEnum::Private())
            ->where('status', '=', InvoiceStatusEnum::Open())
            ->update([
                'invoices.type' => InvoiceTypeEnum::Cleaning(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // nothing to do
    }
};
