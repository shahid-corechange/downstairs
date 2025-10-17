<?php

namespace Database\Seeders;

use Database\Seeders\Olddb\OldDatabaseCompanyCustomersSeeder;
use Database\Seeders\Olddb\OldDatabaseCustomersSeeder;
use Database\Seeders\Olddb\OldDatabaseEmployeesSeeder;
use Database\Seeders\Olddb\OldDatabaseTeamsSeeder;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Seeder;

class OldDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(ConnectionInterface $connection, string $category)
    {
        if (in_array($category, ['all', 'team'])) {
            (new OldDatabaseTeamsSeeder())->run($connection);
        }

        if (in_array($category, ['all', 'employee'])) {
            (new OldDatabaseEmployeesSeeder())->run($connection);
        }

        if (in_array($category, ['all', 'private'])) {
            (new OldDatabaseCustomersSeeder())->run($connection);
        }

        if (in_array($category, ['all', 'company'])) {
            (new OldDatabaseCompanyCustomersSeeder())->run($connection);
        }
    }
}
