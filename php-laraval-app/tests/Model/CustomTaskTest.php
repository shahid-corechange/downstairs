<?php

namespace Tests\Model;

use App\Models\CustomTask;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomTaskTest extends TestCase
{
    /** @test */
    public function customTasksDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('custom_tasks', [
                'id',
                'taskable_type',
                'taskable_id',
                'created_at',
                'updated_at',
            ]),
        );
    }

    /** @test */
    public function customTaskHasTaskable(): void
    {
        $customTask = CustomTask::first();

        $this->assertIsObject($customTask->taskable);
    }

    /** @test */
    public function customTaskHasName(): void
    {
        $customTask = CustomTask::first();

        $this->assertIsString($customTask->name);
    }

    /** @test */
    public function customTaskCanSetName(): void
    {
        $customTask = CustomTask::first();
        $customTask->setName('test');

        $this->assertIsString($customTask->name);
        $this->assertEquals('test', $customTask->name);
    }

    /** @test */
    public function customTaskHasDescription(): void
    {
        $customTask = CustomTask::first();

        $this->assertIsString($customTask->description);
    }

    /** @test */
    public function customTaskCanSetDescription(): void
    {
        $customTask = CustomTask::first();
        $customTask->setDescription('test');

        $this->assertIsString($customTask->description);
        $this->assertEquals('test', $customTask->description);
    }

    /** @test */
    public function customTaskHasTranslations(): void
    {
        $customTask = CustomTask::first();

        $this->assertIsObject($customTask->translations);
    }
}
