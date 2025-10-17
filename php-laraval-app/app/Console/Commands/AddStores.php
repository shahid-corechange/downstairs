<?php

namespace App\Console\Commands;

use App\Models\Address;
use App\Models\Store;
use App\Models\User;
use DB;
use Illuminate\Console\Command;

class AddStores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stores:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add existing stores from old system';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        foreach ($this->stores() as $storeData) {
            $addressData = $storeData['address'];
            $nameData = $storeData['name'];

            // check if store already exists
            $isExists = Store::where('name', $nameData)->exists();

            if ($isExists) {
                continue;
            }

            DB::transaction(function () use ($storeData, $addressData) {
                $address = Address::create($addressData);

                // Remove the address from store data before creating
                unset($storeData['address']);

                $store = Store::create([...$storeData, 'address_id' => $address->id]);
                $this->assignToUsers($store);
            });
        }
    }

    private function assignToUsers(Store $store): void
    {
        $users = User::role('Superadmin')->get();
        $store->users()->sync($users->pluck('id')->toArray());
    }

    private function stores(): array
    {
        return json_decode(file_get_contents(storage_path('app/seeders/stores.json')), true);
    }
}
