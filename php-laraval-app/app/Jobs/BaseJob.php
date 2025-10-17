<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected function handleWrapper(callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());

            if ($this->connection === 'sync') {
                $constructor = new \ReflectionMethod(static::class, '__construct');
                $parameters = $constructor->getParameters();
                $args = array_map(function ($parameter) {
                    return $this->{$parameter->name};
                }, $parameters);
                $this->dispatch(...$args);

                return;
            }

            throw $e;
        }
    }
}
