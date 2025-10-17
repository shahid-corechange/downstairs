<?php

namespace Tests\Portal\Customer;

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
use Mockery\MockInterface;
use Tests\TestCase;

class CustomerAddressesTest extends TestCase
{
    public function testCanAccessCustomerAddresses(): void
    {
        $response = $this->actingAs($this->admin)
            ->get("/customers/{$this->user->id}/addresses");
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

    public function testCanNotAccessCustomerAddresses(): void
    {
        $this->actingAs($this->admin)
            ->get("/customers/{$this->worker->id}/addresses")
            ->assertStatus(404);
    }

    public function testCanCreateCustomerAddress(): void
    {
        $this->mock(FortnoxCustomerService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getCustomers')->andReturn(CustomerDTO::collection([]));
        });

        $data = [
            'identityNumber' => '198112289874',
            'membershipType' => MembershipTypeEnum::Private(),
            'name' => 'John Doe',
            'email' => 'john_doe@test.com',
            'phone1' => '+46 123412341234',
            'dueDays' => 15,
            'invoiceMethod' => InvoiceMethodEnum::Print(),

            // Address
            'cityId' => config('downstairs.test.city_id'),
            'address' => '1234 Test Street',
            'postalCode' => '42234',
            'latitude' => 12.345678,
            'longitude' => 23.456789,
        ];

        $this->actingAs($this->admin)
            ->post("/customers/{$this->user->id}/addresses", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('customer address created successfully'));

        Bus::assertDispatchedAfterResponse(CreateFortnoxCustomerJob::class);

        $phones = explode(' ', $data['phone1']);
        $dialCode = str_replace('+', '', $phones[0]);
        $address = Address::where('city_id', $data['cityId'])
            ->where('address', $data['address'])
            ->where('postal_code', $data['postalCode'])
            ->first();
        $customer = Customer::where('address_id', $address->id)
            ->where('membership_type', MembershipTypeEnum::Private())
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
            'membership_type' => MembershipTypeEnum::Private(),
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
            'user_id' => $this->user->id,
        ]);
    }

    public function testCanUpdateCustomerAddress(): void
    {
        $customer = $this->user->customers()->first();
        $customerId = $customer->id;
        $addressId = $customer->address_id;

        $data = [
            'identityNumber' => '198112289874',
            'membershipType' => MembershipTypeEnum::Private(),
            'name' => 'John Doe',
            'email' => 'john_doe@test.com',
            'phone1' => '+46 123412341234',
            'dueDays' => 20,
            'invoiceMethod' => InvoiceMethodEnum::Email(),

            // Address
            'cityId' => config('downstairs.test.city_id'),
            'address' => '1234 Test Street',
            'postalCode' => '42234',
        ];

        $this->actingAs($this->admin)
            ->patch("/customers/{$this->user->id}/addresses/{$customerId}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('customer address updated successfully'));

        Bus::assertDispatchedAfterResponse(UpdateFortnoxCustomerJob::class);

        $phones = explode(' ', $data['phone1']);
        $dialCode = str_replace('+', '', $phones[0]);
        $address = Address::where('city_id', $data['cityId'])
            ->where('address', $data['address'])
            ->where('postal_code', $data['postalCode'])
            ->first();
        $customer = Customer::where('address_id', $address->id)
            ->where('membership_type', MembershipTypeEnum::Private())
            ->where('type', ContactTypeEnum::Invoice())
            ->where('name', $data['name'])
            ->where('email', $data['email'])
            ->first();

        $this->assertDatabaseHas('addresses', [
            'id' => $addressId,
            'city_id' => $data['cityId'],
            'address' => $data['address'],
            'postal_code' => $data['postalCode'],
        ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customerId,
            'address_id' => $address->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone1' => $dialCode.$phones[1],
            'dial_code' => $dialCode,
            'due_days' => $data['dueDays'],
            'invoice_method' => $data['invoiceMethod'],
        ]);
    }

    public function testCanDeleteCustomerAddress(): void
    {
        $customerId = $this->user->customers()->first()->id;
        $subscriptions = Subscription::withTrashed()
            ->where('customer_id', $customerId);

        $subscriptionIds = $subscriptions->get()->pluck('id')->toArray();
        ScheduleCleaning::withTrashed()
            ->whereIn('subscription_id', $subscriptionIds)
            ->forceDelete();
        $subscriptions->forceDelete();

        $this->actingAs($this->admin)
            ->delete("/customers/{$this->user->id}/addresses/{$customerId}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('customer address deleted successfully'));

        $this->assertSoftDeleted('customers', [
            'id' => $customerId,
        ]);
    }

    public function testCanNotDeleteCustomerAddress(): void
    {
        $customerId = $this->user->customers()->first()->id;

        $this->actingAs($this->admin)
            ->delete("/customers/{$this->user->id}/addresses/{$customerId}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                __('still used in schedules or subscriptions')
            );
    }

    public function testCanRestoreCustomerAddress(): void
    {
        $customer = $this->user->customers()->first();
        $customerId = $customer->id;
        $customer->delete();

        $this->actingAs($this->admin)
            ->post("/customers/{$this->user->id}/addresses/{$customerId}/restore")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('customer address restored successfully'));

        $this->assertDatabaseHas('customers', [
            'id' => $customerId,
            'deleted_at' => null,
        ]);
    }
}
