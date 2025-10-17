<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseMergeSeeder extends Seeder
{
    /**
     * Only use for merge with old database migration.
     */
    public function run(): void
    {
        $this->call([
            UsersSeeder::class,
            SubscriptionSeeder::class,
            OauthSeeder::class,
            UserMergeSeeder::class,
        ]);
    }
}
