<?php

namespace App\Http\Controllers\Role;

use App\DTOs\Role\CreateRoleRequestDTO;
use App\DTOs\Role\RoleResponseDTO;
use App\DTOs\Role\UpdateRoleRequestDTO;
use App\Http\Controllers\Controller;
use App\Models\Role;
use DB;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(size: -1);

        $paginatedData = Role::applyFilterSortAndPaginate($queries);

        return Inertia::render('Role/Overview/index', [
            'roles' => RoleResponseDTO::transformCollection($paginatedData->data, ['permissions']),
        ]);
    }

    /**
     * Store resource in storage.
     */
    public function store(CreateRoleRequestDTO $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            /** @var \app\Models\Role */
            $role = Role::create([
                'name' => $request->name,
            ]);
            $role->syncPermissions($request->permissions);
        });

        return back()->with('success', __('role created successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateRoleRequestDTO $request,
        Role $role,
    ): RedirectResponse {
        DB::transaction(function () use ($request, $role) {
            $role->update([
                'name' => $request->name,
            ]);
            $role->syncPermissions($request->permissions);
        });

        return back()->with('success', __('role updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();

        return back()->with('success', __('role deleted successfully'));
    }
}
