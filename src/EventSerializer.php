<?php

namespace Spatie\EventSaucer;

use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use ReflectionClass;
use ReflectionProperty;

class EventSerializer
{
    use SerializesAndRestoresModelIdentifiers;

    /** @var mixed */
    protected $event;

    public function __construct($event)
    {
       $this->event = $event;
    }

    public function getName(): string
    {
        return get_class($this->event);
    }

    public function getSerializableProperties(): array
    {
        return dd($this->event->__sleep());
    }


}