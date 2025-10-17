<?php

namespace Tests\Portal\Customer;

use App\DTOs\RutCoApplicant\RutCoApplicantResponseDTO;
use App\Models\RutCoApplicant;
use Tests\TestCase;

class CustomerRutCoApplicantTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->user->rutCoApplicants()->create([
            'name' => 'John Doe',
            'identity_number' => '198112289874',
            'phone' => '46123412341234',
            'dial_code' => '46',
        ]);
    }

    public function testCanAccessCustomerRutCoApplicants(): void
    {
        $response = $this->actingAs($this->admin)
            ->get("/customers/{$this->user->id}/rut-co-applicants");
        $keys = array_keys(
            RutCoApplicantResponseDTO::from(RutCoApplicant::first())->toArray()
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

    public function testCanNotAccessCustomerRutCoApplicants(): void
    {
        $this->actingAs($this->admin)
            ->get("/customers/{$this->worker->id}/rut-co-applicants")
            ->assertStatus(404);
    }

    public function testCanCreateCustomerRutCoApplicant(): void
    {
        $this->user->rutCoApplicants()->forceDelete();

        $data = [
            'identityNumber' => '198112289874',
            'name' => 'John Doe',
            'phone' => '+46 123412341234',
        ];

        $this->actingAs($this->admin)
            ->post("/customers/{$this->user->id}/rut-co-applicants", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('customer rut co applicant created successfully'));

        $phones = explode(' ', $data['phone']);
        $dialCode = str_replace('+', '', $phones[0]);

        $this->assertDatabaseHas('rut_co_applicants', [
            'user_id' => $this->user->id,
            'name' => $data['name'],
            'phone' => $dialCode.$phones[1],
            'dial_code' => $dialCode,
        ]);
    }

    public function testCanNotCreateCustomerRutCoApplicantIfAlreadyExists(): void
    {
        $data = [
            'identityNumber' => '198112289874',
            'name' => 'John Doe',
            'phone' => '+46 123412341234',
        ];

        $this->actingAs($this->admin)
            ->post("/customers/{$this->user->id}/rut-co-applicants", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('customer rut co applicant already exists'));
    }

    public function testCanUpdateCustomerRutCoApplicant(): void
    {
        $rutCoApplicant = $this->user->rutCoApplicants()->first();
        $rutCoApplicantId = $rutCoApplicant->id;

        $data = [
            'identityNumber' => '198112289874',
            'name' => 'Customer 1',
            'phone' => '+46 123412341235',
        ];

        $this->actingAs($this->admin)
            ->patch("/customers/{$this->user->id}/rut-co-applicants/{$rutCoApplicantId}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('customer rut co applicant updated successfully'));

        $phones = explode(' ', $data['phone']);
        $dialCode = str_replace('+', '', $phones[0]);

        $this->assertDatabaseHas('rut_co_applicants', [
            'user_id' => $this->user->id,
            'name' => $data['name'],
            'phone' => $dialCode.$phones[1],
            'dial_code' => $dialCode,
        ]);
    }

    public function testCanNotUpdateCustomerRutCoApplicantIfAlreadyExists(): void
    {
        $rutCoApplicant = $this->user->rutCoApplicants()->first();
        $rutCoApplicantId = $rutCoApplicant->id;
        $this->user->rutCoApplicants()->create([
            'name' => 'Customer 2',
            'identity_number' => '198112289874',
            'phone' => '46123412341234',
            'dial_code' => '46',
        ]);

        $data = [
            'identityNumber' => '198112289874',
            'name' => 'Customer 1',
            'phone' => '+46 123412341235',
        ];

        $this->actingAs($this->admin)
            ->patch("/customers/{$this->user->id}/rut-co-applicants/{$rutCoApplicantId}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('customer rut co applicant already exists'));
    }

    public function testCanDeleteCustomerRutCoApplicant(): void
    {
        $rutCoApplicantId = $this->user->rutCoApplicants()->first()->id;

        $this->actingAs($this->admin)
            ->delete("/customers/{$this->user->id}/rut-co-applicants/{$rutCoApplicantId}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('customer rut co applicant deleted successfully'));

        $this->assertSoftDeleted('rut_co_applicants', [
            'id' => $rutCoApplicantId,
        ]);
    }

    public function testCanEnableCustomerRutCoApplicant(): void
    {
        $rutCoApplicant = $this->user->rutCoApplicants()->first();
        $rutCoApplicantId = $rutCoApplicant->id;

        $this->actingAs($this->admin)
            ->post("/customers/{$this->user->id}/rut-co-applicants/{$rutCoApplicantId}/enable")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('customer rut co applicant enabled successfully'));

        $this->assertDatabaseHas('rut_co_applicants', [
            'id' => $rutCoApplicantId,
            'is_enabled' => true,
        ]);
    }

    public function testCanNotEnableCustomerRutCoApplicant(): void
    {
        $rutCoApplicant = $this->user->rutCoApplicants()->createMany([
            [
                'name' => 'Customer 2',
                'identity_number' => '194009207459',
                'phone' => '46123412341234',
                'dial_code' => '46',
                'is_enabled' => true,
            ],
            [
                'name' => 'Customer 3',
                'identity_number' => '201603233055',
                'phone' => '46123412341235',
                'dial_code' => '46',
                'is_enabled' => true,
            ],
        ]);
        $rutCoApplicant = $this->user->rutCoApplicants()
            ->where('is_enabled', false)->first();
        $rutCoApplicantId = $rutCoApplicant->id;

        $this->actingAs($this->admin)
            ->post("/customers/{$this->user->id}/rut-co-applicants/{$rutCoApplicantId}/enable")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('customer rut co applicant enable limit reached'));
    }

    public function testCanDisableCustomerRutCoApplicant(): void
    {
        $rutCoApplicant = $this->user->rutCoApplicants()->first();
        $rutCoApplicant->update([
            'is_enabled' => true,
        ]);
        $rutCoApplicantId = $rutCoApplicant->id;

        $this->actingAs($this->admin)
            ->post("/customers/{$this->user->id}/rut-co-applicants/{$rutCoApplicantId}/disable")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('customer rut co applicant disabled successfully'));

        $this->assertDatabaseHas('rut_co_applicants', [
            'id' => $rutCoApplicantId,
            'is_enabled' => false,
        ]);
    }

    public function testCanPauseCustomerRutCoApplicant(): void
    {
        $rutCoApplicant = $this->user->rutCoApplicants()->first();
        $rutCoApplicant->update([
            'is_enabled' => true,
        ]);
        $rutCoApplicantId = $rutCoApplicant->id;

        $data = [
            'pauseStartDate' => now()->format('Y-m-d'),
            'pauseEndDate' => now()->addDays(1)->format('Y-m-d'),
        ];

        $this->actingAs($this->admin)
            ->post("/customers/{$this->user->id}/rut-co-applicants/{$rutCoApplicantId}/pause", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('customer rut co applicant pause data updated successfully'));

        $this->assertDatabaseHas('rut_co_applicants', [
            'id' => $rutCoApplicantId,
            'is_enabled' => true,
            'pause_start_date' => $data['pauseStartDate'],
            'pause_end_date' => $data['pauseEndDate'],
        ]);
    }

    public function testCanNotPauseCustomerRutCoApplicantIfNotEnabled(): void
    {
        $rutCoApplicant = $this->user->rutCoApplicants()->first();
        $rutCoApplicantId = $rutCoApplicant->id;

        $data = [
            'pauseStartDate' => now()->format('Y-m-d'),
            'pauseEndDate' => now()->addDays(1)->format('Y-m-d'),
        ];

        $this->actingAs($this->admin)
            ->post("/customers/{$this->user->id}/rut-co-applicants/{$rutCoApplicantId}/pause", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('customer rut co applicant not enabled'));
    }

    public function testCanContinueCustomerRutCoApplicant(): void
    {
        $rutCoApplicant = $this->user->rutCoApplicants()->first();
        $rutCoApplicant->update([
            'is_enabled' => true,
        ]);
        $rutCoApplicantId = $rutCoApplicant->id;

        $this->actingAs($this->admin)
            ->post("/customers/{$this->user->id}/rut-co-applicants/{$rutCoApplicantId}/continue")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('customer rut co applicant continued successfully'));

        $this->assertDatabaseHas('rut_co_applicants', [
            'id' => $rutCoApplicantId,
            'is_enabled' => true,
            'pause_start_date' => null,
            'pause_end_date' => null,
        ]);
    }
}
