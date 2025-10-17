<?php

namespace Tests\Portal\Management;

use App\Enums\PermissionsEnum;
use App\Models\Role;
use DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RoleTest extends TestCase
{
    public function testAdminCanAccessRoles(): void
    {
        $roles = Role::all();

        $this->actingAs($this->admin)
            ->get('/roles')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Role/Overview/index')
                ->has('roles', $roles->count())
                ->has('roles.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('name')
                    ->has('permissions')));
    }

    public function testWorkerCanNotAccessRoles(): void
    {
        $this->actingAs($this->worker)
            ->get('/roles')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanCreateRole(): void
    {
        $count = DB::table('role_has_permissions')->count();
        $data = [
            'name' => 'Role 1',
            'permissions' => [
                PermissionsEnum::CustomersCreate(),
                PermissionsEnum::CustomersUpdate(),
                PermissionsEnum::CustomersDelete(),
            ],
        ];

        $this->actingAs($this->admin)
            ->post('/roles', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('role created successfully'));

        $this->assertDatabaseHas('roles', [
            'name' => $data['name'],
        ]);

        $this->assertDatabaseCount('role_has_permissions', $count + 3);
    }

    public function testCanUpdateRole(): void
    {
        $count = DB::table('role_has_permissions')->count();
        $role = Role::create([
            'name' => 'Role 1',
        ]);
        $data = [
            'name' => 'Test Role 1',
            'permissions' => [
                PermissionsEnum::CustomersCreate(),
                PermissionsEnum::CustomersUpdate(),
                PermissionsEnum::CustomersDelete(),
            ],
        ];

        $this->actingAs($this->admin)
            ->patch("/roles/{$role->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('role updated successfully'));

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => $data['name'],
        ]);

        $this->assertDatabaseCount('role_has_permissions', $count + 3);
    }

    public function testCanDeleteRole(): void
    {
        $role = Role::first();

        $this->actingAs($this->admin)
            ->delete("/roles/{$role->id}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('role deleted successfully'));

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ]);
    }
}
