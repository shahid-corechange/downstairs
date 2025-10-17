<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Store;
use App\Models\User;
use DB;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment() !== 'testing') {
            foreach ($this->getStores() as $storeData) {
                $addressData = $storeData['address'];

                DB::transaction(function () use ($storeData, $addressData) {
                    $address = Address::create($addressData);

                    // Remove the address from store data before creating
                    unset($storeData['address']);

                    $store = Store::create([...$storeData, 'address_id' => $address->id]);
                    $this->assignToUsers($store);
                });
            }
        } else {
            Store::factory(3)->create()->each(function (Store $store) {
                $this->assignToUsers($store);
            });
        }
    }

    private function assignToUsers(Store $store): void
    {
        $users = User::role('Superadmin')->get();
        $store->users()->sync($users->pluck('id')->toArray());
    }

    private function getStores(): array
    {
        return json_decode(file_get_contents(storage_path('app/seeders/stores.json')), true);
    }
}
