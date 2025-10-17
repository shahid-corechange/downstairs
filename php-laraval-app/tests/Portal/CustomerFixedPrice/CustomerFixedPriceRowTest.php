<?php

namespace Tests\Portal\CustomerFixedPrice;

use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use App\Models\FixedPriceRow;
use Tests\TestCase;

class CustomerFixedPriceRowTest extends TestCase
{
    public function testCanCreateCustomerFixedPriceRow(): void
    {
        FixedPriceRow::where('fixed_price_id', 1)->delete();

        $data = [
            'type' => FixedPriceRowTypeEnum::Service(),
            'quantity' => 1,
            'price' => 300,
            'vatGroup' => 25,
        ];

        $this->actingAs($this->admin)
            ->post('/customers/fixedprices/1/rows', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('fixed price row created successfully'));

        $this->assertDatabaseHas('fixed_price_rows', [
            'fixed_price_id' => 1,
            'type' => $data['type'],
            'quantity' => $data['quantity'],
            'price' => $data['price'] / 1.25,
            'vat_group' => $data['vatGroup'],
        ]);
    }

    public function testCanNotCreateCustomerFixedPriceRow(): void
    {
        $row = FixedPriceRow::first();
        $data = [
            'type' => $row->type,
            'quantity' => 1,
            'price' => 300,
            'vatGroup' => 25,
        ];

        $this->actingAs($this->admin)
            ->post("/customers/fixedprices/{$row->fixed_price_id}/rows", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('fixed price row type already exists'));
    }

    public function testCanUpdateCustomerFixedPriceRow(): void
    {
        $row = FixedPriceRow::first();
        $data = [
            'type' => $row->type,
            'quantity' => 2,
            'price' => 600,
            'vatGroup' => 25,
        ];

        $this->actingAs($this->admin)
            ->patch("/customers/fixedprices/{$row->fixed_price_id}/rows/{$row->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('fixed price row updated successfully'));

        $this->assertDatabaseHas('fixed_price_rows', [
            'id' => $row->id,
            'type' => $data['type'],
            'quantity' => $data['quantity'],
            'price' => $data['price'] / 1.25,
            'vat_group' => $data['vatGroup'],
        ]);
    }

    public function testCanNotUpdateCustomerFixedPriceRowIfNotFound(): void
    {
        $row = FixedPriceRow::first();
        $data = [
            'type' => $row->type,
            'quantity' => 2,
            'price' => 600,
            'vatGroup' => 25,
        ];

        $this->actingAs($this->admin)
            ->patch("/customers/fixedprices/{$row->fixed_price_id}/rows/100", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('fixed price row not found'));
    }

    // For differentiate the fixed price laundry and cleaning
    // public function testCanNotUpdateCustomerFixedPriceRowIfExists(): void
    // {
    //     $row = FixedPriceRow::first();
    //     $data = [
    //         'type' => FixedPriceRowTypeEnum::Material(),
    //         'quantity' => 2,
    //         'price' => 600,
    //         'vatGroup' => 25,
    //     ];

    //     $this->actingAs($this->admin)
    //         ->patch("/customers/fixedprices/{$row->fixed_price_id}/rows/{$row->id}", $data)
    //         ->assertStatus(302)
    //         ->assertRedirect()
    //         ->assertSessionHas('error', __('fixed price row type already exists'));
    // }

    public function testCanDeleteCustomerFixedPriceRow(): void
    {
        $row = FixedPriceRow::first();

        $this->actingAs($this->admin)
            ->delete("/customers/fixedprices/{$row->fixed_price_id}/rows/{$row->id}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('fixed price row deleted successfully'));

        $this->assertDatabaseMissing('fixed_price_rows', [
            'id' => $row->id,
        ]);
    }

    public function testCanNotDeleteCustomerFixedPriceRowIfNotFound(): void
    {
        $row = FixedPriceRow::first();

        $this->actingAs($this->admin)
            ->delete("/customers/fixedprices/{$row->fixed_price_id}/rows/100")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('fixed price row not found'));
    }
}
