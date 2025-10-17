<?php

namespace Tests\Model;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RutCoApplicantTest extends TestCase
{
    /** @test */
    public function rutCoApplicantsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('rut_co_applicants', [
                'id',
                'user_id',
                'name',
                'identity_number',
                'phone',
                'dial_code',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function rutCoApplicantHasUser(): void
    {
        $user = User::first();
        $rutCoApplicant = $user->rutCoApplicants()->create([
            'name' => 'John Doe',
            'identity_number' => '198112289874',
            'phone' => '123412341234',
            'dial_code' => '46',
        ]);

        $this->assertInstanceOf(User::class, $rutCoApplicant->user);
    }
}
