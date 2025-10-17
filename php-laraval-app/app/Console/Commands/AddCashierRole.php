<?php

namespace App\Console\Commands;

use App\Enums\PermissionsEnum;
use App\Models\Role;
use Illuminate\Console\Command;

class AddCashierRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:add-cashier';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add cashier role';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $role = Role::firstOrCreate([
            'name' => 'Cashier',
        ]);

        $role->syncPermissions([PermissionsEnum::AccessCashier()]);
    }
}
