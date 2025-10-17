<?php

namespace Tests\Model;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    /** @test */
    public function notificationsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('notifications', [
                'id',
                'user_id',
                'hub',
                'type',
                'title',
                'description',
                'is_read',
                'created_at',
                'updated_at',
            ]),
        );
    }

    /** @test */
    public function notificationHasUser(): void
    {
        $notification = Notification::first();

        $this->assertInstanceOf(User::class, $notification->user);
    }
}
