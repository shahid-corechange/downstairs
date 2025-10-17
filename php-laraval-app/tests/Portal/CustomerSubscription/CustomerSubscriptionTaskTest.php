<?php

namespace Tests\Portal\CustomerSubscription;

use App\Enums\TranslationEnum;
use App\Models\CustomTask;
use App\Models\Subscription;
use Session;
use Tests\TestCase;

class CustomerSubscriptionTaskTest extends TestCase
{
    public function testCanCreateCustomerSubscriptionTask(): void
    {
        $subscription = Subscription::first();
        $subscription->tasks()->delete();

        $data = [
            'nameSvSe' => 'Prenumerationsuppgift 1',
            'descriptionSvSe' => 'Beskrivning av prenumerationsuppgift 1',
            'nameEnUs' => 'Subscription task 1',
            'descriptionEnUs' => 'Subscription task 1 description',
        ];

        $response = $this->actingAs($this->admin)
            ->post("/customers/subscriptions/{$subscription->id}/tasks", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('task created successfully'),
            Session::get('success')
        );

        $task = $subscription->tasks()->first();
        $this->assertDatabaseHas('custom_tasks', [
            'taskable_type' => Subscription::class,
            'taskable_id' => $subscription->id,
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

    public function testCanUpdateCustomerSubscriptionTask(): void
    {
        $subscription = Subscription::first();
        $subscription->tasks()->delete();

        $task = $subscription->tasks()->create([]);
        $task->setName('', TranslationEnum::Swedish());
        $task->setDescription('', TranslationEnum::Swedish());
        $task->setName('', TranslationEnum::English());
        $task->setDescription('', TranslationEnum::English());

        $data = [
            'nameSvSe' => 'Prenumerationsuppgift 1',
            'descriptionSvSe' => 'Beskrivning av prenumerationsuppgift 1',
            'nameEnUs' => 'Subscription task 1',
            'descriptionEnUs' => 'Subscription task 1 description',
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/customers/subscriptions/{$subscription->id}/tasks/{$task->id}", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('task updated successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('custom_tasks', [
            'id' => $task->id,
            'taskable_type' => Subscription::class,
            'taskable_id' => $subscription->id,
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

    public function testCanNotUpdateCustomerSubscriptionTaskIfNotFound(): void
    {
        $subscription = Subscription::first();
        $data = [
            'nameSvSe' => 'Prenumerationsuppgift 1',
            'descriptionSvSe' => 'Beskrivning av prenumerationsuppgift 1',
            'nameEnUs' => 'Subscription task 1',
            'descriptionEnUs' => 'Subscription task 1 description',
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/customers/subscriptions/{$subscription->id}/tasks/1000", $data);

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('task not found'),
            Session::get('error')
        );
    }

    public function testCanDeleteCustomerSubscriptionTask(): void
    {
        $subscription = Subscription::first();
        $subscription->tasks()->delete();

        $task = $subscription->tasks()->create([]);
        $task->setName('', TranslationEnum::Swedish());
        $task->setDescription('', TranslationEnum::Swedish());
        $task->setName('', TranslationEnum::English());
        $task->setDescription('', TranslationEnum::English());

        $response = $this->actingAs($this->admin)
            ->delete("/customers/subscriptions/{$subscription->id}/tasks/{$task->id}");

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('task deleted successfully'),
            Session::get('success')
        );

        $this->assertDatabaseMissing('custom_tasks', [
            'id' => $task->id,
        ]);

        $this->assertDatabaseMissing('translations', [
            'translationable_type' => CustomTask::class,
            'translationable_id' => $task->id,
        ]);
    }

    public function testCanNotDeleteCustomerSubscriptionTaskIfNotFound(): void
    {
        $subscription = Subscription::first();

        $response = $this->actingAs($this->admin)
            ->delete("/customers/subscriptions/{$subscription->id}/tasks/1000");

        $response->assertStatus(302);
        $response->assertRedirect();

        $this->assertEquals(
            __('task not found'),
            Session::get('error')
        );
    }
}
