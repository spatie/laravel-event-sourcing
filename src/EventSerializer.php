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
        $properties = (new ReflectionClass($this))->getProperties();

        foreach ($properties as $property) {
            $property->setValue($this, $this->getSerializedPropertyValue(
                $this->getPropertyValue($property)
            ));
        }

        return array_values(array_filter(array_map(function ($p) {
            return $p->isStatic() ? null : $p->getName();
        }, $properties)));
    }

    /**
     * Get the property value for the given property.
     *
     * @param  \ReflectionProperty  $property
     * @return mixed
     */
    protected function getPropertyValue(ReflectionProperty $property)
    {
        $property->setAccessible(true);

        return $property->getValue($this);
    }
}