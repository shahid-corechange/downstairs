<?php

namespace App\Listeners;

use App\Events\EmployeeCreated;
use App\Notifications\CreatePasswordNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCreatePasswordEmployeeNotification implements ShouldQueue
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
        $event->user->notify(new CreatePasswordNotification($event->user));
    }
}
