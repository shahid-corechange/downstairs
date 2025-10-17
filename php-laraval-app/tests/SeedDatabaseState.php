<?php

namespace Tests;

class SeedDatabaseState
{
    /**
     * Indicates if the test database has been seeded.
     */
    public static bool $seeded = false;

    /**
     * Indicates if the seeders should run once at the beginning of the suite.
     */
    public static bool $seedOnce = true;

    /**
     * Runs only these registered seeders instead of running all seeders.
     */
    public static array $seeders = [];
}
