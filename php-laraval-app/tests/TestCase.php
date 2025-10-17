<?php

namespace Tests;

use App\Enums\MembershipTypeEnum;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Traits\UserSettingTrait;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Property;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use Bus;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Inertia\Inertia;
use Plannr\Laravel\FastRefreshDatabase\Traits\FastRefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use FastRefreshDatabase;
    use UserSettingTrait;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    public User $user;

    public User $userCompany;

    public User $worker;

    public User $admin;

    public Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        // now re-register all the roles and permissions (clears cache and reloads relations)
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();

        // create a test user customer
        $this->user = $this->createUser();
        $this->addUserData($this->user, MembershipTypeEnum::Private());

        // create a test user company
        $this->userCompany = User::role('Company')->first();

        // create a test user worker
        $this->worker = $this->createUser([], 'Worker');
        $this->worker->assignRole('Employee');
        $this->addEmployeeData($this->worker);

        // create a test user superadmin
        $this->admin = $this->createAdmin();
        $this->team = Team::factory()->create();
        $this->team->users()->attach($this->worker->id);
        $this->withoutMiddleware(\App\Http\Middleware\Cache::class);

        // set inertia root to test blade template for testing
        $inertiaMiddleware = new HandleInertiaRequests();
        $inertiaMiddleware->setRootView('test');
        $this->instance('App\Http\Middleware\HandleInertiaRequests', $inertiaMiddleware);
        Inertia::setRootView('test');

        Bus::fake();
    }

    protected function createUser(array $options = [], string $role = null): User
    {
        try {
            $user = User::factory()->hasInfo(1)->create($options);

            $role = empty($role) ? 'Customer' : $role;
            $user->assignRole($role);

            return $user;
        } catch (\Illuminate\Database\QueryException) {
            return $this->createUser($options, $role);
        }
    }

    protected function createAdmin(array $options = []): User
    {
        return $this->createUser($options, 'Superadmin');
    }

    protected function addUserData(User $user, string $setMembershipType)
    {
        $address = Address::factory()->create();
        Property::factory()->assignAddress($address->id)
            ->hasAttached($user)->setMembershipType($setMembershipType)->create();
        Customer::factory()->forUser($user, $address->id)
            ->hasAttached($user)->setMembershipType($setMembershipType)->create();
        // Subscription::factory()->forUser($user)->create()->each(function (Subscription $subscription) {
        //     $subscription->update(['team_id' => Team::all()->random()->id]);
        // });
        $this->createDefaultSettings($user);
    }

    protected function addEmployeeData(User $user)
    {
        $addresses = Address::factory()->count(1)->create();
        Employee::factory()->count(1)->forUser($user, $addresses[0]->id)->create();
    }
}
