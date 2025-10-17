<?php

namespace Tests\Portal\Operation;

use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Enums\TranslationEnum;
use App\Models\CustomTask;
use App\Models\ScheduleCleaning;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ScheduleTaskTest extends TestCase
{
    public function testCanCreateScheduleTask(): void
    {
        $schedule = ScheduleCleaning::where(
            'status',
            ScheduleCleaningStatusEnum::Booked()
        )->first();
        $schedule->tasks()->delete();

        $data = [
            'nameSvSe' => 'Schemauppgift 1',
            'descriptionSvSe' => 'Schemauppgift 1 beskrivning',
            'nameEnUs' => 'Schedule task 1',
            'descriptionEnUs' => 'Schedule task 1 description',
        ];

        $this->actingAs($this->admin)
            ->postJson("/schedules/{$schedule->id}/tasks", $data)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                ])
                ->where('message', __('task created successfully')));

        $task = $schedule->tasks()->first();
        $this->assertDatabaseHas('custom_tasks', [
            'taskable_type' => ScheduleCleaning::class,
            'taskable_id' => $schedule->id,
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => CustomTask::class,
            'translationable_id' => $task->id,
            'key' => 'name',
            'sv_SE' => $data['nameSvSe'],
            'nn_NO' => null,
            'en_US' => $data['nameEnUs'],
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => CustomTask::class,
            'translationable_id' => $task->id,
            'key' => 'description',
            'sv_SE' => $data['descriptionSvSe'],
            'nn_NO' => null,
            'en_US' => $data['descriptionEnUs'],
        ]);
    }

    public function testCanNotCreateScheduleTask(): void
    {
        $schedule = ScheduleCleaning::whereNotIn(
            'status',
            [ScheduleCleaningStatusEnum::Booked(), ScheduleCleaningStatusEnum::Progress()]
        )->first();
        $schedule->tasks()->delete();

        $data = [
            'nameSvSe' => 'Schemauppgift 1',
            'descriptionSvSe' => 'Schemauppgift 1 beskrivning',
            'nameEnUs' => 'Schedule task 1',
            'descriptionEnUs' => 'Schedule task 1 description',
        ];

        $this->actingAs($this->admin)
            ->postJson("/schedules/{$schedule->id}/tasks", $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to add task to schedule due to schedule status')));
    }

    public function testCanUpdateScheduleTask(): void
    {
        $schedule = ScheduleCleaning::where(
            'status',
            ScheduleCleaningStatusEnum::Booked()
        )->first();
        $schedule->tasks()->delete();

        $task = $schedule->tasks()->create([]);
        $task->setName('', TranslationEnum::Swedish());
        $task->setDescription('', TranslationEnum::Swedish());
        $task->setName('', TranslationEnum::English());
        $task->setDescription('', TranslationEnum::English());

        $data = [
            'nameSvSe' => 'Schemauppgift 1',
            'descriptionSvSe' => 'Schemauppgift 1 beskrivning',
            'nameEnUs' => 'Schedule task 1',
            'descriptionEnUs' => 'Schedule task 1 description',
        ];

        $this->actingAs($this->admin)
            ->patchJson("/schedules/{$schedule->id}/tasks/{$task->id}", $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                ])
                ->where('message', __('task updated successfully')));

        $this->assertDatabaseHas('custom_tasks', [
            'id' => $task->id,
            'taskable_type' => ScheduleCleaning::class,
            'taskable_id' => $schedule->id,
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => CustomTask::class,
            'translationable_id' => $task->id,
            'key' => 'name',
            'sv_SE' => $data['nameSvSe'],
            'nn_NO' => null,
            'en_US' => $data['nameEnUs'],
        ]);

        $this->assertDatabaseHas('translations', [
            'translationable_type' => CustomTask::class,
            'translationable_id' => $task->id,
            'key' => 'description',
            'sv_SE' => $data['descriptionSvSe'],
            'nn_NO' => null,
            'en_US' => $data['descriptionEnUs'],
        ]);
    }

    public function testCanNotUpdateScheduleTaskIfNotBookedAndNotProgress(): void
    {
        $schedule = ScheduleCleaning::whereNotIn(
            'status',
            [ScheduleCleaningStatusEnum::Booked(), ScheduleCleaningStatusEnum::Progress()]
        )->first();
        $schedule->tasks()->delete();

        $task = $schedule->tasks()->create([]);
        $task->setName('', TranslationEnum::Swedish());
        $task->setDescription('', TranslationEnum::Swedish());
        $task->setName('', TranslationEnum::English());
        $task->setDescription('', TranslationEnum::English());

        $data = [
            'nameSvSe' => 'Schemauppgift 1',
            'descriptionSvSe' => 'Schemauppgift 1 beskrivning',
            'nameEnUs' => 'Schedule task 1',
            'descriptionEnUs' => 'Schedule task 1 description',
        ];

        $this->actingAs($this->admin)
            ->patchJson("/schedules/{$schedule->id}/tasks/{$task->id}", $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to update task due to schedule status')));
    }

    public function testCanNotUpdateScheduleTaskIfNotFound(): void
    {
        $schedule = ScheduleCleaning::where(
            'status',
            ScheduleCleaningStatusEnum::Booked()
        )->first();

        $data = [
            'nameSvSe' => 'Schemauppgift 1',
            'descriptionSvSe' => 'Schemauppgift 1 beskrivning',
            'nameEnUs' => 'Schedule task 1',
            'descriptionEnUs' => 'Schedule task 1 description',
        ];

        $this->actingAs($this->admin)
            ->patchJson("/schedules/{$schedule->id}/tasks/1000", $data)
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('task not found')));
    }

    public function testCanDeleteScheduleTask(): void
    {
        $schedule = ScheduleCleaning::where(
            'status',
            ScheduleCleaningStatusEnum::Booked()
        )->first();
        $schedule->tasks()->delete();

        $task = $schedule->tasks()->create([]);
        $task->setName('', TranslationEnum::Swedish());
        $task->setDescription('', TranslationEnum::Swedish());
        $task->setName('', TranslationEnum::English());
        $task->setDescription('', TranslationEnum::English());

        $this->actingAs($this->admin)
            ->deleteJson("/schedules/{$schedule->id}/tasks/{$task->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                ])
                ->where('message', __('task deleted successfully')));

        $this->assertDatabaseMissing('custom_tasks', [
            'id' => $task->id,
        ]);

        $this->assertDatabaseMissing('translations', [
            'translationable_type' => CustomTask::class,
            'translationable_id' => $task->id,
        ]);
    }

    public function testCanNotDeleteScheduleTaskIfNotBookedAndNotProgress(): void
    {
        $schedule = ScheduleCleaning::whereNotIn(
            'status',
            [ScheduleCleaningStatusEnum::Booked(), ScheduleCleaningStatusEnum::Progress()]
        )->first();
        $schedule->tasks()->delete();

        $task = $schedule->tasks()->create([]);
        $task->setName('', TranslationEnum::Swedish());
        $task->setDescription('', TranslationEnum::Swedish());
        $task->setName('', TranslationEnum::English());
        $task->setDescription('', TranslationEnum::English());

        $this->actingAs($this->admin)
            ->deleteJson("/schedules/{$schedule->id}/tasks/{$task->id}")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to delete task due to schedule status')));
    }

    public function testCanNotDeleteScheduleTaskIfNotFound(): void
    {
        $schedule = ScheduleCleaning::where(
            'status',
            ScheduleCleaningStatusEnum::Booked()
        )->first();

        $this->actingAs($this->admin)
            ->deleteJson("/schedules/{$schedule->id}/tasks/1000")
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('task not found')));
    }
}
