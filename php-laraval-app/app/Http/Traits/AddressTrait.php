<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\DB;

trait AddressTrait
{
    private function userAddresses(int $userId)
    {
        $addresses = DB::table('addresses')
            ->join('customers', 'addresses.address_id', '=', 'customers.address_id')
            ->join('customer_user', 'customer_user.customer_id', '=', 'customer_user.customer_id')
            ->where('customer_user.user_id', $userId)
            ->select('addresses.*')
            ->distinct();

        $properties = DB::table('addresses')
            ->join('properties', 'addresses.address_id', '=', 'properties.address_id')
            ->join('property_user', 'properties.property_id', '=', 'property_user.property_id')
            ->where('property_user.user_id', $userId)
            ->select('addresses.*')
            ->distinct();

        $results = $addresses->union($properties)->get();

        return $results;
    }
}
