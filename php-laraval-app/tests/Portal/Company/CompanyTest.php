<?php

namespace Tests\Portal\Company;

use App\DTOs\User\UserResponseDTO;
use App\Enums\Contact\ContactTypeEnum;
use App\Enums\Invoice\InvoiceMethodEnum;
use App\Enums\MembershipTypeEnum;
use App\Jobs\CreateFortnoxCustomerJob;
use App\Models\Country;
use App\Models\Customer;
use App\Models\KeyPlace;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Bus;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    public function testAdminCanAccessCompanies(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = Customer::where('membership_type', MembershipTypeEnum::Company())
            ->where('type', ContactTypeEnum::Primary())->count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/companies')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Overview/index')
                ->has('companies', $total)
                ->has('companies.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('name')
                    ->has('email')
                    ->has('formattedPhone1')
                    ->etc())
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessCompanies(): void
    {
        $this->actingAs($this->user)
            ->get('/companies')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterCompanies(): void
    {
        $data = Customer::where('membership_type', MembershipTypeEnum::Company())
            ->where('type', ContactTypeEnum::Primary())->first();
        $pageSize = config('downstairs.pageSize');

        $this->actingAs($this->admin)
            ->get("/companies?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Overview/index')
                ->has('companies', 1)
                ->has('companies.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('name', $data->name)
                    ->where('email', $data->email)
                    ->where('formattedPhone1', $data->formatted_phone1)
                    ->etc())
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCanAccessCompanyUsers(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/companies/users');
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
            'pagination' => [
                'total',
                'size',
                'currentCursor',
                'nextCursor',
                'nextPageUrl',
                'previousCursor',
                'previousPageUrl',
            ],
        ]);
    }

    public function testCanAccessCompanyWizard(): void
    {
        $countries = Country::all();
        $propertyTypes = PropertyType::whereNot('id', 1)->get();

        $this->actingAs($this->admin)
            ->get('/companies/wizard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Wizard/index')
                ->has('countries', $countries->count())
                ->has('countries.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('name'))
                ->has('propertyTypes', $propertyTypes->count())
                ->has('propertyTypes.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('name')));
    }

    public function testCanCreateCompanyFromWizard(): void
    {
        $keyPlace = KeyPlace::whereNull('property_id')->first();
        Property::whereNotNull('id')->forceDelete();

        /** @var (string|string[])[] $data */
        $data = [
            'companyName' => 'Test AB',
            'orgNumber' => '987654321',
            'companyEmail' => 'test_ab@test.com',
            'companyPhone' => '+46 11112222333',
            'dueDays' => 20,
            'invoiceMethod' => InvoiceMethodEnum::Print(),

            // Contact person
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john_doe@test.com',
            'cellphone' => '+46 123412341234',
            'identityNumber' => '198112289874',

            // Address
            'cityId' => config('downstairs.test.city_id'),
            'address' => '1234 Test Street',
            'postalCode' => '42234',
            'latitude' => 59.334591,
            'longitude' => 18.063240,

            // Property
            'propertyTypeId' => 3,
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
            ->post('/companies/wizard', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('company created successfully'));

        Bus::assertDispatchedAfterResponse(CreateFortnoxCustomerJob::class);

        $phones = explode(' ', $data['companyPhone']);
        $dialCode = str_replace('+', '', $phones[0]);
        $contactPhones = explode(' ', $data['cellphone']);
        $contactDialCode = str_replace('+', '', $contactPhones[0]);
        $keyInformation = array_keys_to_snake_case([
            ...$data['keyInformation'],
            'keyPlace' => $keyPlace->id,
        ]);

        $this->assertDatabaseHas('users', [
            'first_name' => $data['firstName'],
            'last_name' => $data['lastName'],
            'email' => $data['email'],
            'cellphone' => $contactDialCode.$contactPhones[1],
        ]);

        $this->assertDatabaseHas('users', [
            'first_name' => $data['companyName'],
            'last_name' => '',
            'email' => $data['companyEmail'],
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
        $this->assertDatabaseHas('properties', [
            'property_type_id' => 3,
            // 'square_meter' => (float) $data['squareMeter'],
            // 'key_information' => json_encode($keyInformation),
        ]);

        $this->assertDatabaseHas('addresses', [
            'city_id' => $data['invoiceCityId'],
            'address' => $data['invoiceAddress'],
            'postal_code' => $data['invoicePostalCode'],
        ]);

        $this->assertDatabaseHas('customers', [
            'membership_type' => MembershipTypeEnum::Company(),
            'type' => ContactTypeEnum::Primary(),
            'name' => $data['companyName'],
            'email' => $data['companyEmail'],
            'phone1' => $dialCode.$phones[1],
            'dial_code' => $dialCode,
            'due_days' => $data['dueDays'],
            'invoice_method' => $data['invoiceMethod'],
        ]);

        $this->assertDatabaseHas('customers', [
            'membership_type' => MembershipTypeEnum::Company(),
            'type' => ContactTypeEnum::Invoice(),
            'name' => $data['companyName'],
            'email' => $data['companyEmail'],
            'phone1' => $dialCode.$phones[1],
            'dial_code' => $dialCode,
            'due_days' => $data['dueDays'],
            'invoice_method' => $data['invoiceMethod'],
        ]);
    }

    public function testCanUpdateCompany(): void
    {
        $data = [
            'name' => 'Test Company',
            'email' => 'company@test.com',
            'phone1' => '+46 123456789',
        ];

        /** @var Customer $customer */
        $customer = $this->userCompany->customers()
            ->where('membership_type', MembershipTypeEnum::Company())
            ->first();

        $phones = explode(' ', $data['phone1']);
        $dialCode = str_replace('+', '', $phones[0]);
        $this->actingAs($this->admin)
            ->patch("/companies/{$customer->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('company updated successfully'));

        $this->assertDatabaseHas('users', [
            'id' => $this->userCompany->id,
            'first_name' => $data['name'],
            'last_name' => '',
            'email' => $data['email'],
            'cellphone' => $dialCode.$phones[1],
            'dial_code' => $dialCode,
        ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone1' => $dialCode.$phones[1],
            'dial_code' => $dialCode,
        ]);
    }

    public function testCanNotUpdateCompany(): void
    {
        /** @var Customer $customer */
        $customer = $this->user->customers()
            ->first();

        $this->actingAs($this->admin)
            ->patch("/companies/{$customer->id}", [])
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanDeleteCompany(): void
    {
        $this->userCompany->subscriptions()->delete();

        /** @var Customer $customer */
        $customer = $this->userCompany->customers()
            ->where('membership_type', MembershipTypeEnum::Company())
            ->first();

        $this->userCompany->subscriptions()
            ->withTrashed()
            ->where(function (Builder $query) {
                $query->whereHas('scheduleCleanings', function ($query) {
                    $query->active();
                })
                    ->orWhereNull('deleted_at');
            })
            ->forceDelete();

        $this->actingAs($this->admin)
            ->delete("/companies/{$customer->id}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('company deleted successfully'));

        $this->assertSoftDeleted('customers', [
            'id' => $customer->id,
        ]);
    }

    public function testCanNotDeleteCompanyIfNotFound(): void
    {
        $this->user->subscriptions()->delete();

        /** @var Customer $customer */
        $customer = $this->user->customers()
            ->first();

        $this->actingAs($this->admin)
            ->delete("/companies/{$customer->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanNotDeleteCompanyIfHasActiveSubscription(): void
    {
        /** @var Customer $customer */
        $customer = $this->userCompany->customers()
            ->where('membership_type', MembershipTypeEnum::Company())
            ->first();

        $this->actingAs($this->admin)
            ->delete("/companies/{$customer->id}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                __('company has active schedules or subscriptions')
            );
    }

    public function testCanRestoreCompany(): void
    {
        /** @var Customer $customer */
        $customer = $this->userCompany->customers()
            ->where('membership_type', MembershipTypeEnum::Company())
            ->first();
        $customer->delete();

        $this->actingAs($this->admin)
            ->post("/companies/{$customer->id}/restore")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('company restored successfully'));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'deleted_at' => null,
        ]);
    }

    public function testCanNotRestoreCompany(): void
    {
        /** @var Customer $customer */
        $customer = $this->user->customers()
            ->first();
        $customer->delete();

        $this->actingAs($this->admin)
            ->post("/companies/{$customer->id}/restore")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }
}
