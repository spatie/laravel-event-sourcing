<?php

namespace Spatie\EventProjector\EventSubscribers;

use Exception;
use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEvent;

interface EventSubscriber
{
    public function handles(): Collection;

    public function handle(StoredEvent $event);

    public function handleException(Exception $exception);
}
