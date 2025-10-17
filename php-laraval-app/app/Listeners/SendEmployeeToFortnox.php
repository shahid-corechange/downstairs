<?php

namespace App\Listeners;

use App\Events\EmployeeCreated;
use App\Services\Fortnox\FortnoxEmployeeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendEmployeeToFortnox implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    public function __construct(public FortnoxEmployeeService $fortnoxService)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(EmployeeCreated $event): void
    {
        $this->fortnoxService->syncEmployee($event->user->employee);
    }
}
