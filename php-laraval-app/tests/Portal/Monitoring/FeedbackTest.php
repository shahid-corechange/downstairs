<?php

namespace Tests\Portal\Monitoring;

use App\DTOs\Feedback\FeedbackResponseDTO;
use App\Models\Feedback;
use Inertia\Testing\AssertableInertia as Assert;
use Session;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Feedback::factory(10)->create();
    }

    public function testAdminCanAccessFeedbacks(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = Feedback::count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/feedbacks')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Feedback/Overview/index')
                ->has('feedbacks', $total)
                ->has('feedbacks.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('option')
                    ->has('description')
                    ->has('createdAt')
                    ->has('deletedAt')
                    ->etc()
                    ->has('user', fn (Assert $page) => $page
                        ->has('id')
                        ->has('fullname')
                        ->etc()))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessFeedbacks(): void
    {
        $this->actingAs($this->user)
            ->get('/feedbacks')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterFeedbacks(): void
    {
        $feedback = Feedback::first();
        $pageSize = config('downstairs.pageSize');

        $this->actingAs($this->admin)
            ->get("/feedbacks?id.eq={$feedback->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Feedback/Overview/index')
                ->has('feedbacks', 1)
                ->has('feedbacks.0', fn (Assert $page) => $page
                    ->where('id', $feedback->id)
                    ->where('option', $feedback->option)
                    ->etc()
                    ->has('user', fn (Assert $page) => $page
                        ->where('id', $feedback->feedbackable->id)
                        ->where('fullname', $feedback->feedbackable->fullname)
                        ->etc()))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessFeedbacksJson(): void
    {
        $response = $this->actingAs($this->admin)->get('/feedbacks/json');
        $keys = array_keys(
            FeedbackResponseDTO::fromModel(Feedback::first())->toArray()
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => $keys,
            ],
            'meta' => [
                'etag',
            ],
        ]);
    }

    public function testCanDeleteFeedback(): void
    {
        $feedback = Feedback::first();

        $this->actingAs($this->admin)
            ->delete("/feedbacks/{$feedback->id}")
            ->assertStatus(302)
            ->assertRedirect();

        $this->assertEquals(
            __('feedback deleted successfully'),
            Session::get('success')
        );

        $this->assertSoftDeleted('feedbacks', [
            'id' => $feedback->id,
        ]);
    }

    public function testCanRestoreFeedback(): void
    {
        $feedback = Feedback::first();
        $feedback->delete();

        $this->actingAs($this->admin)
            ->post("/feedbacks/{$feedback->id}/restore")
            ->assertStatus(302)
            ->assertRedirect();

        $this->assertEquals(
            __('feedback restored successfully'),
            Session::get('success')
        );

        $this->assertDatabaseHas('feedbacks', [
            'id' => $feedback->id,
        ]);
    }
}
