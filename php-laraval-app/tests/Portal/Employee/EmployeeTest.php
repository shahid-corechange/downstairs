<?php

namespace Tests\Portal\Employee;

use App\DTOs\Fortnox\Employee\EmployeeDTO;
use App\DTOs\User\UserResponseDTO;
use App\Events\EmployeeCreated;
use App\Models\Country;
use App\Models\Role;
use App\Models\User;
use App\Services\Fortnox\FortnoxEmployeeService;
use Event;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    public function testAdminCanAccessEmployees(): void
    {
        $pageSize = config('downstairs.pageSize');
        $hiddenRoles = ['Customer', 'Company'];
        $roles = Role::whereNotIn('name', $hiddenRoles)->get();
        $count = User::whereHas(
            'roles',
            fn (Builder $query) => $query->whereNotIn('name', $hiddenRoles)
        )->count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/employees')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Employee/Overview/index')
                ->has('employees', $total)
                ->has('employees.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('firstName')
                    ->has('lastName')
                    ->has('email')
                    ->etc()
                    ->has('employee', fn (Assert $page) => $page
                        ->has('isValidIdentity')
                        ->etc())
                    ->has('roles.0', fn (Assert $page) => $page
                        ->has('id')
                        ->has('name')
                        ->etc()))
                ->has('roles', $roles->count())
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessEmployees(): void
    {
        $this->actingAs($this->user)
            ->get('/employees')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterEmployees(): void
    {
        $data = User::whereHas(
            'roles',
            fn (Builder $query) => $query->where('name', 'Employee')
        )->first();
        $pageSize = config('downstairs.pageSize');

        $this->actingAs($this->admin)
            ->get("/employees?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Employee/Overview/index')
                ->has('employees', 1)
                ->has('employees.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('firstName', $data->first_name)
                    ->where('lastName', $data->last_name)
                    ->where('email', $data->email)
                    ->etc()
                    ->has('employee', fn (Assert $page) => $page
                        ->where('isValidIdentity', $data->employee->is_valid_identity)
                        ->etc())
                    ->has('roles.0', fn (Assert $page) => $page
                        ->where('id', $data->roles->first()->id)
                        ->where('name', $data->roles->first()->name)
                        ->etc()))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessEmployeesJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/employees/json');
        $employee = User::whereHas(
            'roles',
            fn (Builder $query) => $query->where('name', 'Employee')
        )->first();
        $keys = array_keys(
            UserResponseDTO::from($employee)->toArray()
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => $keys,
            ],
            'meta' => [
                'etag',
            ],
        ]);
    }

    public function testCanAccessEmployeeWizard(): void
    {
        $countries = Country::all();

        $this->actingAs($this->admin)
            ->get('/employees/wizard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Employee/Wizard/index')
                ->has('countries', $countries->count())
                ->has('countries.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('name'))
                ->has('roles'));
    }

    public function testCanCreateEmployeeFromWizard(): void
    {
        Event::fake(EmployeeCreated::class);

        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john_doe@worker.com',
            'cellphone' => '+46 123412341234',
            'identityNumber' => '1234567890',
            'roles' => [
                'Employee',
                'Worker',
            ],

            // Address
            'cityId' => config('downstairs.test.city_id'),
            'address' => '1234 Test Street',
            'postalCode' => '42234',

            // Info
            'timezone' => 'Sweden/Stockholm',
            'language' => 'en_US',
            'currency' => 'SEK',
            'twoFactorAuth' => 'disabled',
        ];

        $this->actingAs($this->admin)
            ->post('/employees/wizard', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('employee created successfully'));

        Event::assertDispatched(EmployeeCreated::class);

        $phones = explode(' ', $data['cellphone']);
        $dialCode = str_replace('+', '', $phones[0]);

        $this->assertDatabaseHas('users', [
            'first_name' => $data['firstName'],
            'last_name' => $data['lastName'],
            'email' => $data['email'],
            'cellphone' => $dialCode.$phones[1],
        ]);

        $this->assertDatabaseHas('user_infos', [
            'timezone' => $data['timezone'],
            'language' => $data['language'],
            'currency' => $data['currency'],
        ]);

        $this->assertDatabaseHas('addresses', [
            'city_id' => $data['cityId'],
            'address' => $data['address'],
            'postal_code' => $data['postalCode'],
        ]);

        $this->assertDatabaseHas('employees', [
            'name' => $data['firstName'].' '.$data['lastName'],
            'email' => $data['email'],
            'phone1' => $dialCode.$phones[1],
            'dial_code' => $dialCode,
        ]);

        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => Role::where('name', 'Worker')->first()->id,
            'model_id' => User::where('email', $data['email'])->first()->id,
            'model_type' => User::class,
        ]);

        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => Role::where('name', 'Employee')->first()->id,
            'model_id' => User::where('email', $data['email'])->first()->id,
            'model_type' => User::class,
        ]);
    }

    public function testCanUpdateEmployee(): void
    {
        $this->mock(FortnoxEmployeeService::class, function (MockInterface $mock) {
            $mock->shouldReceive('updateEmployee')
                ->andReturn(EmployeeDTO::from(['employee_id' => 1]));
        });

        $this->actingAs($this->admin)
            ->patch("/employees/{$this->worker->id}", [
                'firstName' => 'John',
                'lastName' => 'Doe',
            ])
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('employee updated successfully'));

        $this->assertDatabaseHas('users', [
            'id' => $this->worker->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    public function testCanNotUpdateEmployee(): void
    {
        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
        ];

        $this->actingAs($this->admin)
            ->patch('/employees/1000', $data)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanDeleteEmployee(): void
    {
        $employeeId = $this->worker->employee->id;
        $this->worker->scheduleEmployees()->active()->forceDelete();

        $this->actingAs($this->admin)
            ->delete("/employees/{$this->worker->id}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('employee deleted successfully'));

        $this->assertSoftDeleted('users', [
            'id' => $this->worker->id,
        ]);

        $this->assertSoftDeleted('employees', [
            'id' => $employeeId,
        ]);
    }

    public function testCanNotDeleteEmployee(): void
    {
        $this->actingAs($this->admin)
            ->delete('/employees/1000')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanDeleteEmployeeIfHasActiveSchedules(): void
    {
        $worker = User::whereHas('scheduleEmployees')->first();

        $this->actingAs($this->admin)
            ->delete("/employees/{$worker->id}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('employee has active schedules'));
    }

    public function testCanRestoreEmployee(): void
    {
        $userId = $this->worker->id;
        $employeeId = $this->worker->employee->id;

        $this->worker->employee->delete();
        $this->worker->delete();

        $this->actingAs($this->admin)
            ->post("/employees/{$userId}/restore")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('employee restored successfully'));

        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employeeId,
            'deleted_at' => null,
        ]);
    }

    public function testCanNotRestoreEmployee(): void
    {
        $this->user->delete();

        $this->actingAs($this->admin)
            ->post("/employees/{$this->user->id}/restore")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanUpdateRoleEmployee(): void
    {
        $data = [
            'roles' => [
                'Worker',
            ],
        ];

        $this->actingAs($this->admin)
            ->put("/employees/{$this->admin->id}/roles", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('employee roles updated successfully'));

        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => Role::where('name', 'Worker')->first()->id,
            'model_id' => $this->admin->id,
            'model_type' => User::class,
        ]);
    }

    public function testCanNotUpdateRoleEmployee(): void
    {
        $data = [
            'roles' => [
                'Employee',
            ],
        ];

        $this->actingAs($this->admin)
            ->put("/employees/{$this->user->id}/roles", $data)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }
}
