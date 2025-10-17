<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Throwable;

trait SeedDatabase
{
    /**
     * Seeds the database.
     *
     *
     * @throws Throwable
     */
    public function seedDatabase(): void
    {
        if (! SeedDatabaseState::$seeded) {
            $this->runSeeders(SeedDatabaseState::$seeders);

            $this->syncTransactionTraits();

            if (SeedDatabaseState::$seedOnce) {
                SeedDatabaseState::$seeded = true;
            }
        }
    }

    /**
     * Calls specific seeders if possible.
     */
    public function runSeeders(array $seeders): void
    {
        if (empty($seeders)) {
            $this->artisan('db:seed');

            $this->app[Kernel::class]->setArtisan(null);

            return;
        }

        $this->getSeederInstance()->call($seeders);
    }

    /**
     * Persists the seed and begins a new transaction
     * where the rollback has been already registered in Transaction traits.
     *
     *
     * @throws Throwable
     */
    public function syncTransactionTraits(): void
    {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[RefreshDatabase::class]) || isset($uses[DatabaseTransactions::class])) {
            $database = $this->app->make('db');

            foreach ($this->connectionsToTransact() as $name) {
                $database->connection($name)->commit();
                $database->connection($name)->beginTransaction();
            }
        }
    }

    /**
     * Builds a quick seeder instance.
     */
    private function getSeederInstance(): Seeder
    {
        return
            new class() extends Seeder
            {
                public function run()
                {
                }
            };
    }
}
