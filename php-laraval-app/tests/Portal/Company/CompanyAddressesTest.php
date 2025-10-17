<?php

namespace Tests\Portal\Company;

use App\DTOs\Customer\CustomerResponseDTO;
use App\DTOs\Fortnox\Customer\CustomerDTO;
use App\Enums\Contact\ContactTypeEnum;
use App\Enums\Invoice\InvoiceMethodEnum;
use App\Enums\MembershipTypeEnum;
use App\Jobs\CreateFortnoxCustomerJob;
use App\Jobs\UpdateFortnoxCustomerJob;
use App\Models\Address;
use App\Models\Customer;
use App\Models\ScheduleCleaning;
use App\Models\Subscription;
use App\Services\Fortnox\FortnoxCustomerService;
use Bus;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;
use Tests\TestCase;

class CompanyAddressesTest extends TestCase
{
    public function testCanAccessCompanyAddresses(): void
    {
        $customerId = $this->userCompany->customers->first()->id;
        $response = $this->actingAs($this->admin)
            ->get("/companies/{$customerId}/addresses");
        $keys = array_keys(
            CustomerResponseDTO::from(Customer::first())->toArray()
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

    public function testCanNotAccessCompanyAddresses(): void
    {
        $customerId = $this->user->customers->first()->id;

        $this->actingAs($this->admin)
            ->get("/companies/{$customerId}/addresses")
            ->assertStatus(404);
    }

    public function testCanCreateCompanyAddress(): void
    {
        $this->mock(FortnoxCustomerService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getCustomers')->andReturn(CustomerDTO::collection([]));
        });

        $customer = $this->userCompany->customers()->first();
        $customerId = $customer->id;

        $data = [
            'identityNumber' => '198112289874',
            'membershipType' => MembershipTypeEnum::Company(),
            'name' => 'John Doe',
            'email' => 'john_doe@test.com',
            'phone1' => '+46 123412341234',
            'dueDays' => 20,
            'invoiceMethod' => InvoiceMethodEnum::Print(),

            // Address
            'cityId' => config('downstairs.test.city_id'),
            'address' => '1234 Test Street',
            'postalCode' => '42234',
            'latitude' => 59.334591,
            'longitude' => 18.063240,
        ];

        $this->actingAs($this->admin)
            ->post("/companies/{$customerId}/addresses", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('company address created successfully'));

        Bus::assertDispatchedAfterResponse(CreateFortnoxCustomerJob::class);

        $phones = explode(' ', $data['phone1']);
        $dialCode = str_replace('+', '', $phones[0]);
        $address = Address::where('city_id', $data['cityId'])
            ->where('address', $data['address'])
            ->where('postal_code', $data['postalCode'])
            ->first();
        $customer = Customer::where('address_id', $address->id)
            ->where('membership_type', MembershipTypeEnum::Company())
            ->where('type', ContactTypeEnum::Invoice())
            ->where('name', $data['name'])
            ->where('email', $data['email'])
            ->first();

        $this->assertDatabaseHas('addresses', [
            'city_id' => $data['cityId'],
            'address' => $data['address'],
            'postal_code' => $data['postalCode'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
        ]);

        $this->assertDatabaseHas('customers', [
            'address_id' => $address->id,
            'membership_type' => MembershipTypeEnum::Company(),
            'type' => ContactTypeEnum::Invoice(),
            'name' => $data['name'],
            'email' => $data['email'],
            'phone1' => $dialCode.$phones[1],
            'dial_code' => $dialCode,
            'due_days' => $data['dueDays'],
            'invoice_method' => $data['invoiceMethod'],
        ]);

        $this->assertDatabaseHas('customer_user', [
            'customer_id' => $customer->id,
            'user_id' => $this->userCompany->id,
        ]);
    }

    public function testCanNotCreateCompanyAddressIfNotFound(): void
    {
        $customer = $this->user->customers()->first();
        $customerId = $customer->id;

        $data = [
            'identityNumber' => '123456789',
            'membershipType' => MembershipTypeEnum::Company(),
            'name' => 'John Doe',
            'email' => 'john_doe@test.com',
            'phone1' => '+46 123412341234',
            'dueDays' => 20,
            'invoiceMethod' => InvoiceMethodEnum::Print(),

            // Address
            'cityId' => config('downstairs.test.city_id'),
            'address' => '1234 Test Street',
            'postalCode' => '42234',
            'latitude' => 59.334591,
            'longitude' => 18.063240,
        ];

        $this->actingAs($this->admin)
            ->post("/companies/{$customerId}/addresses", $data)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanUpdateCompanyAddress(): void
    {
        $customer = $this->userCompany->customers()->first();
        $customerId = $customer->id;
        $addressId = $customer->address_id;

        $data = [
            'identityNumber' => '123456789',
            'membershipType' => MembershipTypeEnum::Company(),
            'name' => 'John Doe',
            'email' => 'john_doe@test.com',
            'phone1' => '+46 123412341234',
            'dueDays' => 15,
            'invoiceMethod' => InvoiceMethodEnum::Email(),

            // Address
            'cityId' => config('downstairs.test.city_id'),
            'address' => '1234 Test Street',
            'postalCode' => '42234',
        ];

        $this->actingAs($this->admin)
            ->patch("/companies/{$customerId}/addresses/{$customerId}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('company address updated successfully'));

        Bus::assertDispatchedAfterResponse(UpdateFortnoxCustomerJob::class);

        $phones = explode(' ', $data['phone1']);
        $dialCode = str_replace('+', '', $phones[0]);

        $this->assertDatabaseHas('addresses', [
            'id' => $addressId,
            'city_id' => $data['cityId'],
            'address' => $data['address'],
            'postal_code' => $data['postalCode'],
        ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customerId,
            'address_id' => $addressId,
            'membership_type' => MembershipTypeEnum::Company(),
            'type' => ContactTypeEnum::Primary(),
            'name' => $data['name'],
            'email' => $data['email'],
            'phone1' => $dialCode.$phones[1],
            'dial_code' => $dialCode,
            'due_days' => $data['dueDays'],
            'invoice_method' => $data['invoiceMethod'],
        ]);
    }

    public function testCanNotUpdateCompanyAddressIfNotFound(): void
    {
        $customer = $this->user->customers()->first();
        $customerId = $customer->id;

        $this->actingAs($this->admin)
            ->patch(
                "/companies/{$customerId}/addresses/{$customerId}",
                ['membershipType' => MembershipTypeEnum::Company()]
            )
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanDeleteCompanyAddress(): void
    {
        $customerId = $this->userCompany->customers()->first()->id;

        $subscriptions = Subscription::withTrashed()
            ->where('customer_id', $customerId);

        $subscriptionIds = $subscriptions->get()->pluck('id')->toArray();
        ScheduleCleaning::withTrashed()
            ->whereIn('subscription_id', $subscriptionIds)
            ->forceDelete();
        $subscriptions->forceDelete();

        $this->actingAs($this->admin)
            ->delete("/companies/{$customerId}/addresses/{$customerId}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('company address deleted successfully'));

        $this->assertSoftDeleted('customers', [
            'id' => $customerId,
        ]);
    }

    public function testCanNotDeleteCompanyAddressIfNotFound(): void
    {
        $customer = $this->user->customers()->first();
        $customerId = $customer->id;

        $this->actingAs($this->admin)
            ->delete("/companies/{$customerId}/addresses/{$customer->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanNotDeleteCompanyAddressIfThereAreSubscriptions(): void
    {
        $customer = $this->userCompany->customers()->first();
        $customerId = $customer->id;

        $this->actingAs($this->admin)
            ->delete("/companies/{$customerId}/addresses/{$customerId}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                __('still used in schedules or subscriptions')
            );
    }

    public function testCanRestoreCompanyAddress(): void
    {
        $customer = $this->userCompany->customers()->first();
        $customerId = $customer->id;
        $customer->delete();

        $this->actingAs($this->admin)
            ->post("/companies/{$customerId}/addresses/{$customer->id}/restore")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('company address restored successfully'));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'deleted_at' => null,
        ]);
    }

    public function testCanNotRestoreCompanyAddress(): void
    {
        $customer = $this->user->customers()->first();
        $customerId = $customer->id;
        $customer->delete();

        $this->actingAs($this->admin)
            ->post("/companies/{$customerId}/addresses/{$customer->id}/restore")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }
}
