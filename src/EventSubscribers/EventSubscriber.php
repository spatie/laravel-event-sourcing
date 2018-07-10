<?php

namespace Spatie\EventProjector\EventSubscribers;

use Exception;
use Spatie\EventProjector\Models\StoredEvent;
use Illuminate\Support\Collection;

interface EventSubscriber
{
    public function handles(): Collection;

    public function handle(StoredEvent $event);

    public function handleException(Exception $exception);
}
