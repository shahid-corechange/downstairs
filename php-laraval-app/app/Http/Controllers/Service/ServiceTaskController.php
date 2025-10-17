<?php

namespace App\Http\Controllers\Service;

use App\DTOs\CustomTask\CreateCustomTaskRequestDTO;
use App\DTOs\CustomTask\UpdateCustomTaskRequestDTO;
use App\Http\Controllers\Controller;
use App\Models\Service;
use DB;
use Illuminate\Http\RedirectResponse;

class ServiceTaskController extends Controller
{
    /**
     * Add custom task to service.
     */
    public function store(
        Service $service,
        CreateCustomTaskRequestDTO $request,
    ): RedirectResponse {
        $data = $request->toArray();

        DB::transaction(function () use ($data, $service) {
            /** @var \App\Models\CustomTask $task */
            $task = $service->tasks()->create([]);
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
        Service $service,
        int $taskId,
        UpdateCustomTaskRequestDTO $request,
    ): RedirectResponse {
        $task = $service->tasks()->find($taskId);

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
     * Delete custom task from service.
     */
    public function destroy(
        Service $service,
        int $taskId,
    ): RedirectResponse {
        $task = $service->tasks()->find($taskId);

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
