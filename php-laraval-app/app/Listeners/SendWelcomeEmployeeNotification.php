<?php

namespace App\Listeners;

use App\Events\EmployeeCreated;
use App\Notifications\WelcomeEmployeeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeEmployeeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Handle the event.
     */
    public function handle(EmployeeCreated $event): void
    {
        app()->setLocale($event->user->info->language);
        $event->user->notify(new WelcomeEmployeeNotification());
    }
}
