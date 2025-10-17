<?php

namespace Tests\Model;

use App\Models\Address;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Str;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    /** @test */
    public function employeesDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('employees', [
                'id',
                'fortnox_id',
                'user_id',
                'address_id',
                'identity_number',
                'name',
                'email',
                'phone1',
                'dial_code',
                'is_valid_identity',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function employeeHasFormattedPhone1(): void
    {
        $employee = Employee::first();
        $formatedPhone1 = Str::replaceFirst(
            $employee->dial_code,
            "+{$employee->dial_code} ",
            $employee->phone1
        );

        $this->assertIsString($employee->formatted_phone1);
        $this->assertEquals($formatedPhone1, $employee->formatted_phone1);
    }

    /** @test */
    public function employeeHasAddress(): void
    {
        $employee = Employee::first();

        $this->assertInstanceOf(Address::class, $employee->address);
    }

    /** @test */
    public function employeeHasUser(): void
    {
        $employee = Employee::first();

        $this->assertInstanceOf(User::class, $employee->user);
    }
}
