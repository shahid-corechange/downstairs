<?php

namespace Tests\Model;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BlockDayTest extends TestCase
{
    /** @test */
    public function blockDaysDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('block_days', [
                'id',
                'block_date',
                'start_block_time',
                'end_block_time',
                'created_at',
                'updated_at',
            ]),
        );
    }
}
