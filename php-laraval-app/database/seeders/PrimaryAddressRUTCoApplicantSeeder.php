<?php

namespace Database\Seeders;

use App\Enums\Contact\ContactTypeEnum;
use App\Enums\MembershipTypeEnum;
use App\Models\Customer;
use App\Models\RutCoApplicant;
use DB;
use Illuminate\Database\Seeder;

class PrimaryAddressRUTCoApplicantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::where('type', ContactTypeEnum::Primary())
            ->where('membership_type', MembershipTypeEnum::Private())
            ->get();

        DB::transaction(function () use ($customers) {
            foreach ($customers as $customer) {
                RutCoApplicant::create([
                    'user_id' => $customer->users[0]->id,
                    'name' => $customer->name,
                    'identity_number' => $customer->identity_number,
                    'phone' => $customer->phone1,
                    'dial_code' => $customer->dial_code,
                    'is_enabled' => true,
                ]);
            }
        });
    }
}
