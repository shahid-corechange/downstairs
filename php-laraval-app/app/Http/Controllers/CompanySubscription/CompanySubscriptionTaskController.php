<?php

namespace App\Http\Controllers\CompanySubscription;

use App\DTOs\CustomTask\CreateCustomTaskRequestDTO;
use App\DTOs\CustomTask\UpdateCustomTaskRequestDTO;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use DB;
use Illuminate\Http\RedirectResponse;

class CompanySubscriptionTaskController extends Controller
{
    /**
     * Add custom task to subscription.
     */
    public function store(
        Subscription $subscription,
        CreateCustomTaskRequestDTO $request,
    ): RedirectResponse {
        $data = $request->toArray();

        DB::transaction(function () use ($data, $subscription) {
            /** @var \App\Models\CustomTask $task */
            $task = $subscription->tasks()->create([]);
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
        Subscription $subscription,
        int $taskId,
        UpdateCustomTaskRequestDTO $request,
    ): RedirectResponse {
        $task = $subscription->tasks()->find($taskId);

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
     * Delete custom task from subscription.
     */
    public function destroy(
        Subscription $subscription,
        int $taskId,
    ): RedirectResponse {
        $task = $subscription->tasks()->find($taskId);

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
