<?php

use App\Enums\MembershipTypeEnum;
use App\Enums\User\UserNotificationMethodEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> */
        $companyUsers = User::withTrashed()
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', 'Company');
            })
            ->whereHas('customers', function (Builder $query) {
                $query->where('membership_type', MembershipTypeEnum::Company());
            })
            ->with(['info' => function (HasOne $query) {
                $query->withTrashed();
            }])
            ->get();

        foreach ($companyUsers as $user) {
            $user->info->update([
                'notification_method' => UserNotificationMethodEnum::Email(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
