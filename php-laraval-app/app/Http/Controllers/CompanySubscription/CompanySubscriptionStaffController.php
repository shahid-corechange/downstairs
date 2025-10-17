<?php

namespace App\Http\Controllers\CompanySubscription;

use App\DTOs\Subscription\CreateSubscriptionRequestDTO;
use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Subscription;

class CompanySubscriptionStaffController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateSubscriptionRequestDTO $request)
    {
        $subscription = Subscription::create([
            ...$request->toArray(),
            'status' => SubscriptionStatusEnum::Active(),
        ]);

        return back()->with('success', __('subscription staff added successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription)
    {
        $subscription->status = SubscriptionStatusEnum::Deleted();
        $subscription->save();
        $subscription->delete();

        return back()->with('success', __('subscription staff removed successfully'));
    }
}
