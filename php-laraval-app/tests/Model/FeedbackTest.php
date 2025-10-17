<?php

namespace Tests\Model;

use App\Models\Feedback;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    /** @test */
    public function feedbacksDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('feedbacks', [
                'id',
                'feedbackable_type',
                'feedbackable_id',
                'option',
                'description',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function feedbackHasFeedbackable(): void
    {
        Feedback::factory(1)->create();
        $feedback = Feedback::first();

        $this->assertIsObject($feedback->feedbackable);
    }
}
