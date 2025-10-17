<?php

namespace Tests\Model;

use App\Models\Address;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Str;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    /** @test */
    public function customersDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('customers', [
                'id',
                'fortnox_id',
                'address_id',
                'membership_type',
                'type',
                'identity_number',
                'name',
                'email',
                'phone1',
                'dial_code',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function customerHasCompanyUser(): void
    {
        $customer = Customer::where('membership_type', 'company')->first();

        if ($customer->companyUser) {
            $this->assertInstanceOf(User::class, $customer->companyUser);
        } else {
            $this->assertNull($customer->companyUser);
        }
    }

    /** @test */
    public function customerHasCompanyContactUsers(): void
    {
        $customer = Customer::where('membership_type', 'company')->first();

        if ($customer->company_contact_users) {
            $this->assertIsObject($customer->company_contact_users);
            $this->assertInstanceOf(User::class, $customer->company_contact_users->first());
        } else {
            $this->assertNull($customer->company_contact_users);
        }
    }

    /** @test */
    public function customerHasFormattedPhone1(): void
    {
        $customer = Customer::first();
        $formatedPhone1 = Str::replaceFirst(
            $customer->dial_code,
            "+{$customer->dial_code} ",
            $customer->phone1
        );

        $this->assertIsString($customer->formatted_phone1);
        $this->assertEquals($formatedPhone1, $customer->formatted_phone1);
    }

    /** @test */
    public function customerHasAddress(): void
    {
        $customer = Customer::first();

        $this->assertInstanceOf(Address::class, $customer->address);
    }

    /** @test */
    public function customerHasUsers(): void
    {
        $customer = Customer::first();

        $this->assertIsObject($customer->users);
        $this->assertInstanceOf(User::class, $customer->users->first());
    }

    /** @test */
    public function customerHasInvoices(): void
    {
        $invoice = Invoice::first();
        if ($invoice) {
            $customer = $invoice->customer;

            $this->assertIsObject($customer->invoices);
            $this->assertInstanceOf(Invoice::class, $customer->invoices->first());
        } else {
            $this->assertNull($invoice);
        }
    }
}
