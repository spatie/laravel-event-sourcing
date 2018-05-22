<?php

namespace Spatie\EventProjector\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\EventProjector\EventProjectionist;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Exceptions\InvalidEventHandler;

class ListCommand extends Command
{
    protected $signature = 'event-projector:list';

    protected $description = 'List all event projectors';

    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $eventProjectionist;

    public function __construct(EventProjectionist $eventProjectionist)
    {
        parent::__construct();

        $this->eventProjectionist = $eventProjectionist;
    }

    public function handle()
    {
    }
}
