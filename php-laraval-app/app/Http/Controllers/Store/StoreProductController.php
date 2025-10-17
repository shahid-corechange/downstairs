<?php

namespace App\Http\Controllers\Store;

use App\DTOs\Store\UpdateStoreProductRequestDTO;
use App\Http\Controllers\Controller;
use App\Models\Store;
use DB;
use Illuminate\Http\RedirectResponse;

class StoreProductController extends Controller
{
    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateStoreProductRequestDTO $request,
        Store $store
    ): RedirectResponse {
        DB::transaction(function () use ($request, $store) {
            $store->products()->sync($request->product_ids);
        });

        return back()->with('success', __('store products updated successfully'));
    }
}
