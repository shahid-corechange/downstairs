<?php

namespace Tests\Portal\CompanyDiscount;

use App\DTOs\CustomerDiscount\CustomerDiscountResponseDTO;
use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Models\CustomerDiscount;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CompanyDiscountTest extends TestCase
{
    public function testAdminCanAccessCompanyDiscounts(): void
    {
        $pageSize = config('downstairs.pageSize');
        $discounts = CustomerDiscount::whereHas('user', function (Builder $query) {
            $query->whereHas('roles', function (Builder $query) {
                $query->where('name', 'Company');
            });
        })->get();
        $count = $discounts->where('is_active', true)->count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/companies/discounts')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Discount/index')
                ->has('customerDiscounts', $total)
                ->has('customerDiscounts.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('type')
                    ->has('value')
                    ->has('usageLimit')
                    ->has('startDate')
                    ->has('endDate')
                    ->etc()
                    ->has('user', fn (Assert $page) => $page
                        ->has('fullname')))
                ->has('customerDiscountTypes')
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessCompanyDiscounts(): void
    {
        $this->actingAs($this->user)
            ->get('/companies/discounts')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterCompanyDiscounts(): void
    {
        $data = CustomerDiscount::whereHas('user', function (Builder $query) {
            $query->whereHas('roles', function (Builder $query) {
                $query->where('name', 'Company');
            });
        })->first();
        $pageSize = config('downstairs.pageSize');
        $data->update([
            'usage_limit' => 10,
            'start_date' => $data->start_date->subMonth(),
            'end_date' => $data->end_date->addYear(),
        ]);

        $this->actingAs($this->admin)
            ->get("/companies/discounts?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Discount/index')
                ->has('customerDiscounts', 1)
                ->has('customerDiscounts.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('type', $data->type)
                    ->where('value', $data->value)
                    ->where('usageLimit', $data->usage_limit)
                    ->has('startDate')
                    ->has('endDate')
                    ->etc()
                    ->has('user', fn (Assert $page) => $page
                        ->where('fullname', $data->user->fullname)))
                ->has('customerDiscountTypes')
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessCompanyDiscountsJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/companies/discounts/json');
        $keys = array_keys(
            CustomerDiscountResponseDTO::from(CustomerDiscount::first())->toArray()
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

    public function testCanNotAccessCompanyDiscountJson(): void
    {
        $discount = CustomerDiscount::whereHas('user', function (Builder $query) {
            $query->whereHas('roles', function (Builder $query) {
                $query->where('name', 'Customer');
            });
        })->first();

        $this->actingAs($this->user)
            ->get("/companies/discounts/{$discount->id}/json")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanCreateCompanyDiscount(): void
    {
        CustomerDiscount::where('user_id', $this->user->id)
            ->forceDelete();

        $data = [
            'userId' => $this->user->id,
            'type' => CustomerDiscountTypeEnum::Cleaning(),
            'value' => 10,
            'usageLimit' => 10,
            'startDate' => now()->utc(),
            'endDate' => now()->addMonths(1)->utc(),
        ];

        $this->actingAs($this->admin)
            ->post('/companies/discounts', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('company discount created successfully'));

        $this->assertDatabaseHas('customer_discounts', [
            'user_id' => $data['userId'],
            'type' => $data['type'],
            'value' => $data['value'],
            'usage_limit' => $data['usageLimit'],
        ]);
    }

    public function testCanCreateCompanyUnlimitedUsageDiscount(): void
    {
        CustomerDiscount::where('user_id', $this->user->id)
            ->forceDelete();

        $data = [
            'userId' => $this->user->id,
            'type' => CustomerDiscountTypeEnum::Cleaning(),
            'value' => 10,
            'usageLimit' => null,
            'startDate' => now()->utc(),
            'endDate' => now()->addMonths(1)->utc(),
        ];

        $this->actingAs($this->admin)
            ->post('/companies/discounts', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('company discount created successfully'));

        $this->assertDatabaseHas('customer_discounts', [
            'user_id' => $data['userId'],
            'type' => $data['type'],
            'value' => $data['value'],
            'usage_limit' => $data['usageLimit'],
        ]);
    }

    public function testCanCreateCompanyIndefinitelyDiscount(): void
    {
        CustomerDiscount::where('user_id', $this->user->id)
            ->forceDelete();

        $data = [
            'userId' => $this->user->id,
            'type' => CustomerDiscountTypeEnum::Cleaning(),
            'value' => 10,
            'usageLimit' => 1,
            'startDate' => null,
            'endDate' => null,
        ];

        $this->actingAs($this->admin)
            ->post('/companies/discounts', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('company discount created successfully'));

        $this->assertDatabaseHas('customer_discounts', [
            'user_id' => $data['userId'],
            'type' => $data['type'],
            'value' => $data['value'],
            'usage_limit' => $data['usageLimit'],
            'start_date' => $data['startDate'],
            'end_date' => $data['endDate'],
        ]);
    }

    public function testCanCreateCompanyUnlimitedUsageAndIndefinitelyDiscount(): void
    {
        CustomerDiscount::where('user_id', $this->user->id)
            ->forceDelete();

        $data = [
            'userId' => $this->user->id,
            'type' => CustomerDiscountTypeEnum::Cleaning(),
            'value' => 10,
            'usageLimit' => null,
            'startDate' => null,
            'endDate' => null,
        ];

        $this->actingAs($this->admin)
            ->post('/companies/discounts', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('company discount created successfully'));

        $this->assertDatabaseHas('customer_discounts', [
            'user_id' => $data['userId'],
            'type' => $data['type'],
            'value' => $data['value'],
            'usage_limit' => $data['usageLimit'],
            'start_date' => $data['startDate'],
            'end_date' => $data['endDate'],
        ]);
    }

    public function testCanNotCreateCompanyDiscount(): void
    {
        CustomerDiscount::create([
            'user_id' => $this->user->id,
            'type' => CustomerDiscountTypeEnum::Cleaning(),
            'value' => 10,
            'usage_limit' => 10,
            'start_date' => now(),
            'end_date' => now()->addMonths(1),
        ]);

        $data = [
            'userId' => $this->user->id,
            'type' => CustomerDiscountTypeEnum::Cleaning(),
            'value' => 10,
            'usageLimit' => 10,
            'startDate' => now(),
            'endDate' => now()->addMonths(1),
        ];

        $this->actingAs($this->admin)
            ->post('/companies/discounts', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __(
                'company discount overlap with other discounts',
                ['action' => __('create action')]
            ));
    }

    public function testCanUpdateCompanyDiscount(): void
    {
        $discount = CustomerDiscount::whereHas('user', function (Builder $query) {
            $query->whereHas('roles', function (Builder $query) {
                $query->where('name', 'Company');
            });
        })->first();

        $data = [
            'userId' => $discount->user_id,
            'type' => CustomerDiscountTypeEnum::Cleaning(),
            'value' => 20,
            'usageLimit' => 20,
            'startDate' => now()->addYear()->utc(),
            'endDate' => now()->addYear()->addMonths(2)->utc(),
        ];

        $this->actingAs($this->admin)
            ->patch("/companies/discounts/{$discount->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('company discount updated successfully'));

        $this->assertDatabaseHas('customer_discounts', [
            'id' => $discount->id,
            'user_id' => $data['userId'],
            'type' => $data['type'],
            'value' => $data['value'],
            'usage_limit' => $data['usageLimit'],
        ]);
    }

    public function testCanNotUpdateCompanyDiscount(): void
    {
        $discount = CustomerDiscount::whereHas('user', function (Builder $query) {
            $query->whereHas('roles', function (Builder $query) {
                $query->where('name', 'Company');
            });
        })->first();

        $data = [
            'userId' => $discount->user_id,
            'type' => CustomerDiscountTypeEnum::Cleaning(),
            'value' => 20,
            'usageLimit' => 20,
            'startDate' => now(),
            'endDate' => now()->addMonths(2),
        ];

        CustomerDiscount::create([
            'user_id' => $data['userId'],
            'type' => $data['type'],
            'value' => $data['value'],
            'usage_limit' => $data['usageLimit'],
            'start_date' => $data['startDate'],
            'end_date' => $data['endDate'],
        ]);

        $this->actingAs($this->admin)
            ->patch("/companies/discounts/{$discount->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __(
                'company discount overlap with other discounts',
                ['action' => __('update action')]
            ));
    }

    public function testCanDeleteCompanyDiscount(): void
    {
        $discount = CustomerDiscount::whereHas('user', function (Builder $query) {
            $query->whereHas('roles', function (Builder $query) {
                $query->where('name', 'Company');
            });
        })->first();

        $this->actingAs($this->admin)
            ->delete("/companies/discounts/{$discount->id}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('company discount deleted successfully'));

        $this->assertSoftDeleted('customer_discounts', [
            'id' => $discount->id,
        ]);
    }

    public function testCanRestoreCompanyDiscount(): void
    {
        $discount = CustomerDiscount::whereHas('user', function (Builder $query) {
            $query->whereHas('roles', function (Builder $query) {
                $query->where('name', 'Company');
            });
        })->first();

        $discount->delete();
        CustomerDiscount::whereNot('id', $discount->id)->forceDelete();

        $this->actingAs($this->admin)
            ->post("/companies/discounts/{$discount->id}/restore")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('company discount restored successfully'));

        $this->assertDatabaseHas('customer_discounts', [
            'id' => $discount->id,
        ]);
    }

    public function testCanNotRestoreCompanyDiscount(): void
    {
        $discount = CustomerDiscount::whereHas('user', function (Builder $query) {
            $query->whereHas('roles', function (Builder $query) {
                $query->where('name', 'Company');
            });
        })->first();
        $newDiscount = CustomerDiscount::create([
            'user_id' => $discount->user_id,
            'type' => $discount->type,
            'value' => $discount->value,
            'usage_limit' => $discount->usage_limit,
            'start_date' => $discount->start_date,
            'end_date' => $discount->end_date,
        ]);
        $newDiscount->delete();

        $this->actingAs($this->admin)
            ->post("/companies/discounts/{$newDiscount->id}/restore")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __(
                'company discount overlap with other discounts',
                ['action' => __('restore action')]
            ));
    }
}
