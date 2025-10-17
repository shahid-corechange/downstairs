<?php

namespace Tests\Unit\Job;

use App\Contracts\NotificationService;
use App\Contracts\SMSService;
use App\Enums\User\UserNotificationMethodEnum;
use App\Helpers\Notification\AppNotificationOptions;
use App\Helpers\Notification\SendNotificationOptions;
use App\Jobs\SendNotificationJob;
use Mockery;
use Tests\TestCase;

class SendNotificationJobTest extends TestCase
{
    public function testSendNotificationJobByApp()
    {
        // Mock the NotificationService
        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('hub')->once()->andReturnSelf();
        $notificationService->shouldReceive('send')->once();

        // Mock the SMS service
        $smsService = Mockery::mock(SMSService::class);

        // Create a SendNotificationJob instance
        $job = new SendNotificationJob(
            $this->user,
            new SendNotificationOptions(
                new AppNotificationOptions(
                    'test_hub',
                    'test_type',
                    'Test Title',
                    'Test Body',
                    ['key' => 'value'],
                ),
                shouldSave: true,
            ),
        );

        // Call the handle method of the job
        $job->handle($notificationService, $smsService);

        $this->assertTrue(true);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'hub' => 'test_hub',
            'type' => 'test_type',
            'title' => 'Test Title',
            'description' => 'Test Body',
        ]);
    }

    public function testSendNotificationJobByEmail()
    {
        $this->user->info->notification_method = UserNotificationMethodEnum::Email();
        $this->user->save();

        // Mock the NotificationService
        $notificationService = Mockery::mock(NotificationService::class);

        // Mock the SMS service
        $smsService = Mockery::mock(SMSService::class);

        // Create a SendNotificationJob instance
        $job = new SendNotificationJob(
            $this->user,
            new SendNotificationOptions(
                new AppNotificationOptions(
                    'test_hub',
                    'test_type',
                    'Test Title',
                    'Test Body',
                    ['key' => 'value'],
                ),
                shouldSave: true,
                shouldInferMethod: true,
            ),
        );

        // Call the handle method of the job
        $job->handle($notificationService, $smsService);

        $this->assertTrue(true);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->user->id,
            'hub' => 'test_hub',
            'type' => 'test_type',
            'title' => 'Test Title',
            'description' => 'Test Body',
        ]);
    }

    public function testSendNotificationJobBySms()
    {
        $this->user->info->notification_method = UserNotificationMethodEnum::SMS();
        $this->user->save();

        // Mock the NotificationService
        $notificationService = Mockery::mock(NotificationService::class);

        // Mock the SMS service
        $smsService = Mockery::mock(SMSService::class);
        $smsService->shouldReceive('personalize')->once()->andReturnSelf();
        $smsService->shouldReceive('send')->once();

        // Create a SendNotificationJob instance
        $job = new SendNotificationJob(
            $this->user,
            new SendNotificationOptions(
                new AppNotificationOptions(
                    'test_hub',
                    'test_type',
                    'Test Title',
                    'Test Body',
                    ['key' => 'value'],
                ),
                shouldSave: true,
                shouldInferMethod: true,
            ),
        );

        // Call the handle method of the job
        $job->handle($notificationService, $smsService);

        $this->assertTrue(true);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->user->id,
            'hub' => 'test_hub',
            'type' => 'test_type',
            'title' => 'Test Title',
            'description' => 'Test Body',
        ]);
    }
}
