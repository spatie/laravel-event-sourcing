<?php

namespace Spatie\EventSourcing\Commands;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class RunCommandJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public object $command
    ) {
    }

    public function handle(): void
    {
        $handler = CommandHandler::for($this->command);

        $handler->handle();

        /*
         * For now, this functionality is disabled because we don't have a good way of handling it yet
         * https://github.com/spatie/laravel-event-sourcing/discussions/214
         */
        //        if (! $handler->forAggregateRoot()) {
        //            $handler->handle();
        //
        //            return;
        //        }
        //
        //        $lock = Cache::lock($handler->lockId());
        //
        //        if ($lock->get()) {
        //            $handler->handle();
        //
        //            $lock->release();
        //        }
    }
}
