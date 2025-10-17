<?php

namespace App\Http\Controllers\Addon;

use App\DTOs\CustomTask\CreateCustomTaskRequestDTO;
use App\DTOs\CustomTask\UpdateCustomTaskRequestDTO;
use App\Http\Controllers\Controller;
use App\Models\Addon;
use DB;
use Illuminate\Http\RedirectResponse;

class AddOnTaskController extends Controller
{
    /**
     * Add custom task to schedule.
     */
    public function store(
        Addon $addon,
        CreateCustomTaskRequestDTO $request,
    ): RedirectResponse {
        $data = $request->toArray();

        DB::transaction(function () use ($data, $addon) {
            /** @var \App\Models\CustomTask $task */
            $task = $addon->tasks()->create([]);
            $task->translations()->createMany([
                to_translation('name', $data['name']),
                to_translation('description', $data['description']),
            ]);
        });

        return back()->with('success', __('task created successfully'));
    }

    /**
     * Update custom task.
     */
    public function update(
        Addon $addon,
        int $taskId,
        UpdateCustomTaskRequestDTO $request,
    ): RedirectResponse {
        $task = $addon->tasks()->find($taskId);

        if (! $task) {
            return back()->with('error', __('task not found'));
        }

        $data = $request->toArray();
        DB::transaction(function () use ($data, $task) {
            /** @var \App\Models\CustomTask $task */
            $task->updateTranslations([
                to_translation('name', $data['name']),
                to_translation('description', $data['description']),
            ]);
        });

        return back()->with('success', __('task updated successfully'));
    }

    /**
     * Delete custom task from schedule.
     */
    public function destroy(
        Addon $addon,
        int $taskId,
    ): RedirectResponse {
        $task = $addon->tasks()->find($taskId);

        if (! $task) {
            return back()->with('error', __('task not found'));
        }

        DB::transaction(function () use ($task) {
            /** @var \App\Models\CustomTask $task */
            $task->translations()->forceDelete();
            $task->delete();
        });

        return back()->with('success', __('task deleted successfully'));
    }
}
