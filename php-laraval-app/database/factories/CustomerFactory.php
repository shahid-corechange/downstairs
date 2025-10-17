<?php

namespace Database\Factories;

use App\Enums\Contact\ContactTypeEnum;
use App\Enums\MembershipTypeEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fortnox_id' => '12621',
            'membership_type' => MembershipTypeEnum::Private(),
            'address_id' => 1,
        ];
    }

    public function forUser(User $user, string $addressId)
    {
        $primary = $user->customers()->where('type', ContactTypeEnum::Primary())->first();
        $type = $primary ? ContactTypeEnum::Invoice() : ContactTypeEnum::Primary();

        return $this->state(function ($attributes) use ($user, $addressId, $type) {
            return [
                'user_id' => $user->id,
                'type' => $type,
                'address_id' => $addressId,
                'identity_number' => $user->identity_number,
                'name' => "{$user->first_name} {$user->last_name}",
                'email' => $user->email,
                'phone1' => $user->cellphone,
                'dial_code' => $user->dial_code,
            ];
        });
    }

    public function setMembershipType(string $type)
    {
        return $this->state(function ($attributes) use ($type) {
            return [
                'membership_type' => $type,
            ];
        });
    }
}
