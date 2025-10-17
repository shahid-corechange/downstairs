<?php

namespace App\Http\Controllers\Employee;

use App\DTOs\Employee\UpdateEmployeeRoleRequestDTO;
use App\Enums\PermissionsEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Models\Store;
use App\Models\User;
use Auth;
use DB;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EmployeeRoleController extends BaseUserController
{
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRoleRequestDTO $request, User $user): RedirectResponse
    {
        // add validation if user not employee
        if (! $user->isEmployee()) {
            throw new NotFoundHttpException();
        }

        $roles = $request->roles;
        $authUser = Auth::user();

        if (! $authUser->can(PermissionsEnum::EmployeeRolesAppointSuperadmin())) {
            $roles = array_diff($request->roles, ['Superadmin']);
        }

        DB::transaction(function () use ($user, $roles) {
            $user->syncRoles($roles);

            // Assign to stores if user superadmin
            if (in_array('Superadmin', $roles)) {
                $user->stores()->sync(Store::all()->pluck('id')->toArray());
            }

            // Remove from stores if user not superadmin
            if (! in_array('Superadmin', $roles)) {
                $user->stores()->sync([]);
            }
        });

        return back()->with('success', __('employee roles updated successfully'));
    }
}
