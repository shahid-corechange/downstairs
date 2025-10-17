<?php

namespace Tests\Portal\Customer;

use App\DTOs\Property\PropertyResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Enums\Contact\ContactTypeEnum;
use App\Enums\Invoice\InvoiceMethodEnum;
use App\Enums\MembershipTypeEnum;
use App\Jobs\CreateFortnoxCustomerJob;
use App\Models\Country;
use App\Models\KeyPlace;
use App\Models\Property;
use App\Models\User;
use Bus;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    public function testAdminCanAccessCustomers(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = User::whereHas(
            'roles',
            fn (Builder $query) => $query->where('name', 'Customer')
        )->count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/customers')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/Overview/index')
                ->has('customers', $total)
                ->has('customers.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('firstName')
                    ->has('lastName')
                    ->has('email')
                    ->has('formattedCellphone')
                    ->etc()
                    ->has('info', fn (Assert $page) => $page
                        ->has('timezone')
                        ->has('language')
                        ->has('notificationMethod')))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessCustomers(): void
    {
        $this->actingAs($this->user)
            ->get('/customers')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterCustomers(): void
    {
        $data = User::whereHas(
            'roles',
            fn (Builder $query) => $query->where('name', 'Customer')
        )->first();
        $pageSize = config('downstairs.pageSize');

        $this->actingAs($this->admin)
            ->get("/customers?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/Overview/index')
                ->has('customers', 1)
                ->has('customers.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('firstName', $data->first_name)
                    ->where('lastName', $data->last_name)
                    ->where('email', $data->email)
                    ->etc()
                    ->has('info', fn (Assert $page) => $page
                        ->where('timezone', $data->info->timezone)
                        ->where('language', $data->info->language)
                        ->has('notificationMethod')))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCanAccessCustomersJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/customers/json');
        $keys = array_keys(
            UserResponseDTO::from(User::first())->toArray()
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

    public function testCanAccessCustomerProperties(): void
    {
        $response = $this->actingAs($this->admin)
            ->get("/customers/{$this->user->id}/properties");
        $keys = array_keys(
            PropertyResponseDTO::from(Property::first())->toArray()
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

    public function testCanNotAccessCustomerProperties(): void
    {
        $this->actingAs($this->admin)
            ->get("/customers/{$this->worker->id}/properties")
            ->assertStatus(404);
    }

    public function testCanAccessCustomerWizard(): void
    {
        $countries = Country::all();

        $this->actingAs($this->admin)
            ->get('/customers/wizard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/Wizard/index')
                ->has('countries', $countries->count())
                ->has('countries.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('name')));
    }

    public function testCanCreateCustomerFromWizard(): void
    {
        $keyPlace = KeyPlace::whereNull('property_id')->first();
        Property::whereNotNull('id')->forceDelete();

        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john_doe@test.com',
            'cellphone' => '+46 123412341234',
            'identityNumber' => '198112289874',
            'dueDays' => 20,
            'invoiceMethod' => InvoiceMethodEnum::Print(),

            // Address
            'cityId' => config('downstairs.test.city_id'),
            'address' => '1234 Test Street',
            'postalCode' => '42234',
            'latitude' => 59.334591,
            'longitude' => 18.063240,

            // Property
            'squareMeter' => 1001,
            'keyInformation' => [
                'keyPlace' => (string) $keyPlace->id,
                'frontDoorCode' => '1234',
                'alarmCodeOff' => '4444',
                'alarmCodeOn' => '5555',
                'information' => 'Test information',
            ],

            // Info
            'timezone' => 'Sweden/Stockholm',
            'language' => 'en_US',
            'currency' => 'SEK',
            'twoFactorAuth' => 'disabled',

            // Address invoice
            'invoiceCityId' => config('downstairs.test.city_id'),
            'invoiceAddress' => '321 Test Street',
            'invoicePostalCode' => '42235',

        ];

        $this->actingAs($this->admin)
            ->post('/customers/wizard', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('customer created successfully'));

        Bus::assertDispatchedAfterResponse(CreateFortnoxCustomerJob::class);

        $phones = explode(' ', $data['cellphone']);
        $dialCode = str_replace('+', '', $phones[0]);
        $keyInformation = array_keys_to_snake_case([
            ...$data['keyInformation'],
            'keyPlace' => $keyPlace->id,
        ]);

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
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
        ]);

        // Must using MySQL
        // $this->assertDatabaseHas('properties', [
        //     'square_meter' => (float) $data['squareMeter'],
        //     'key_information' => json_encode($keyInformation),
        // ]);

        $this->assertDatabaseHas('addresses', [
            'city_id' => $data['invoiceCityId'],
            'address' => $data['invoiceAddress'],
            'postal_code' => $data['invoicePostalCode'],
        ]);

        $this->assertDatabaseHas('customers', [
            'membership_type' => MembershipTypeEnum::Private(),
            'type' => ContactTypeEnum::Primary(),
            'name' => $data['firstName'].' '.$data['lastName'],
            'email' => $data['email'],
            'phone1' => $dialCode.$phones[1],
            'dial_code' => $dialCode,
            'due_days' => $data['dueDays'],
            'invoice_method' => $data['invoiceMethod'],
        ]);

        $this->assertDatabaseHas('customers', [
            'membership_type' => MembershipTypeEnum::Private(),
            'type' => ContactTypeEnum::Invoice(),
            'name' => $data['firstName'].' '.$data['lastName'],
            'email' => $data['email'],
            'phone1' => $dialCode.$phones[1],
            'dial_code' => $dialCode,
            'due_days' => $data['dueDays'],
            'invoice_method' => $data['invoiceMethod'],
        ]);
    }

    public function testCanUpdateCustomer(): void
    {
        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john_doe@test.com',
            'cellphone' => '+64 123412341234',
            'timezone' => 'Pacific/Auckland',
            'language' => 'en_US',
        ];

        $this->actingAs($this->admin)
            ->patch("/customers/{$this->user->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('customer updated successfully'));

        $phones = explode(' ', $data['cellphone']);
        $dialCode = str_replace('+', '', $phones[0]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'first_name' => $data['firstName'],
            'last_name' => $data['lastName'],
            'email' => $data['email'],
            'cellphone' => $dialCode.$phones[1],
        ]);

        $this->assertDatabaseHas('user_infos', [
            'user_id' => $this->user->id,
            'timezone' => $data['timezone'],
            'language' => $data['language'],
        ]);
    }

    public function testCanNotUpdateCustomer(): void
    {
        $this->actingAs($this->admin)
            ->patch("/customers/{$this->userCompany->id}", [])
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanDeleteCustomer(): void
    {
        $this->user->subscriptions()
            ->withTrashed()
            ->where(function (Builder $query) {
                $query->whereHas('scheduleCleanings', function ($query) {
                    $query->active();
                })
                    ->orWhereNull('deleted_at');
            })
            ->forceDelete();

        $this->actingAs($this->admin)
            ->delete("/customers/{$this->user->id}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('customer deleted successfully'));

        $this->assertSoftDeleted('users', [
            'id' => $this->user->id,
        ]);
    }

    public function testCanNotDeleteCustomerIfNotFound(): void
    {
        $this->actingAs($this->admin)
            ->delete("/customers/{$this->userCompany->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanNotDeleteCustomerIfHasActiveSchedulesOrSubscriptions(): void
    {
        $this->actingAs($this->admin)
            ->delete("/customers/{$this->user->id}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                __('customer has active schedules or subscriptions')
            );
    }

    public function testCanRestoreCustomer(): void
    {
        $this->user->delete();

        $this->actingAs($this->admin)
            ->post("/customers/{$this->user->id}/restore")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('customer restored successfully'));

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'deleted_at' => null,
        ]);
    }

    public function testCanNotRestoreCustomer(): void
    {
        $this->userCompany->delete();

        $this->actingAs($this->admin)
            ->post("/customers/{$this->userCompany->id}/restore")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }
}
